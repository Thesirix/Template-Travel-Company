<?php

namespace App\Http\Controllers\Agent\Flight;

use App\Enums\FlightType;
use App\Enums\ReservationHistoryEnum;
use App\Enums\SmsFlightReservationTypes;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Flight;
use App\Models\FlightDeposit;
use App\Models\FlightGroup;
use App\Models\FlightPassenger;
use App\Models\FlightReservation;
use App\Models\FlightReservationHistory;
use App\Models\Insurance;
use App\Services\FlightReservationNotifier;
use App\Services\FlightSalesStatistics;
use App\Traits\StoresFlightPassengers;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Vente de vols d'une destination : liste des vols ouverts, saisie des passagers,
 * enregistrement de la réservation, confirmation, contrat et e-billet (PDF).
 */
class SaleController extends Controller
{
    use StoresFlightPassengers;

    private const PURCHASE_PRICES = ['prix_achat_adult', 'prix_achat_enfant', 'prix_achat_bebe'];

    public function index(FlightGroup $group, FlightSalesStatistics $statistics)
    {
        $flightGroup = FlightGroup::with(['country', 'flights' => fn ($q) => $q->where('is_archive', false)
            ->where(fn ($q) => $q->whereRaw('CURDATE() < DATE_SUB(date_aller, INTERVAL days_before_closure DAY)')
                ->orWhere('place_reserver', '>', 0))
            ->with(['escaliers', 'ville', 'company'])->orderBy('date_aller')])->findOrFail($group->id);
        $this->hidePurchasePrices($flightGroup->flights);
        $hasFlights = $flightGroup->flights->isNotEmpty();

        return view('agent.vols.vente.allvols', [
            'flightGroup' => $flightGroup,
            'type_reservation' => 'noReservation',
            'agentsStatistics' => $hasFlights ? $statistics->byAgent($group->id) : null,
            'agenciesStatistics' => $hasFlights ? $statistics->byAgency($group->id) : null,
            'destinationsStatistics' => $hasFlights ? $statistics->byCity($group->id) : null,
        ]);
    }

    /** Vols de la destination triés (appel AJAX de la liste des vols) */
    public function filter(Request $request, FlightGroup $group)
    {
        $flights = Flight::with(['escaliers', 'ville', 'company', 'volGroup'])
            ->where('vol_group_id', $group->id)->where('type_voyage', $request->order_vol_type)
            ->orderBy(match ($request->order_by) {
                'date_retour' => 'date_retour',
                'villes.nom' => City::select('nom')->whereColumn('villes.id', 'vols.ville_id'),
                default => 'date_aller',
            }, $request->order_type === 'desc' ? 'desc' : 'asc')->get();
        $this->hidePurchasePrices($flights);

        return $flights;
    }

    /** Formulaire des passagers pour le(s) vol(s) choisi(s) */
    public function create(Request $request, FlightGroup $group)
    {
        if (! $request->vol_id && ! $request->vol_id_retour) {
            return back()->with('warning', 'Choisissez un vol.');
        }
        $load = fn ($id) => Flight::with(['volGroup', 'escaliers.airPortDe', 'escaliers.airPortA', 'company'])
            ->where('vol_group_id', $group->id)->findOrFail($id);
        $counts = ['adult' => (int) $request->adultcount, 'enfant' => (int) $request->enfantcount, 'bebe' => (int) $request->bebecount];

        $vol_retour = null;
        $durationRetour = null;
        if ($request->vol_id) {
            $vol = $load($request->vol_id);
            $type_voyage = $vol->type_voyage === FlightType::ROUND_TRIP->value ? 'aller_retour_same_vol' : 'aller_simple';
            $durationRetour = $type_voyage === 'aller_retour_same_vol' ? Carbon::parse($vol->duration_retour) : null;
            if ($request->vol_id_retour) {
                $vol_retour = $load($request->vol_id_retour);
                $type_voyage = 'aller_retour_diffrent_vol';
                $durationRetour = Carbon::parse($vol_retour->duration_aller);
            }
        } else {
            $vol = $load($request->vol_id_retour);
            $type_voyage = 'retour_simple';
        }

        $legs = array_filter([$vol, $vol_retour]);
        $price = fn (string $type) => array_sum(array_map(fn ($flight) => (float) $flight->{'prix_vente_' . $type}, $legs));
        $this->hidePurchasePrices(collect($legs));

        return view('agent.vols.vente.inscription', [
            'assurences' => Insurance::forCountry($group->country->nom)->get(),
            'vol' => $vol,
            'vol_retour' => $vol_retour,
            'type_voyage' => $type_voyage,
            'type_reervation' => $request->type_reservation,
            'flightsGroup' => $group,
            'durationAller' => Carbon::parse($vol->duration_aller),
            'durationRetour' => $durationRetour,
            'adultcount' => $counts['adult'],
            'enfantcount' => $counts['enfant'],
            'bebecount' => $counts['bebe'],
            'prix_adult' => $price('adult'),
            'prix_enfant' => $price('enfant'),
            'prix_bebe' => $price('bebe'),
            'devisTotalPrice' => $counts['adult'] * $price('adult') + $counts['enfant'] * $price('enfant') + $counts['bebe'] * $price('bebe'),
        ]);
    }

    /**
     * Enregistre une vente : réservation, passagers, acompte, historique et places des vols.
     * Les SMS et e-mails de confirmation partent une fois la vente enregistrée.
     */
    public function store(Request $request, FlightGroup $group, FlightReservationNotifier $notifier)
    {
        $request->validate([
            'vol_id' => ['required', 'integer'],
            'vol_retour_id' => ['nullable', 'required_if:type_voyage,aller_retour_diffrent_vol', 'integer'],
            'type_voyage' => ['required', 'in:aller_simple,retour_simple,aller_retour_same_vol,aller_retour_diffrent_vol'],
            'adultcountinput2' => ['required', 'integer', 'min:0'],
            'enfantcountinput2' => ['nullable', 'integer', 'min:0'],
            'bebecountinput2' => ['nullable', 'integer', 'min:0'],
            'passagers' => ['required', 'array', 'min:1'],
            'passagers.*.type' => ['required', 'in:adult,adulte,enfant,bebe'],
            'passagers.*.nom' => ['required', 'string', 'max:200'],
            'passagers.*.prenom' => ['required', 'string', 'max:200'],
            'passagers.*.date_naissance' => ['required', 'date'],
            'passagers.*.assurance' => ['required', 'exists:assurences,id'],
            'montant_acompte' => ['nullable', 'numeric', 'min:0'],
        ]);
        $seats = (int) $request->adultcountinput2 + (int) $request->enfantcountinput2;
        $agentId = Auth::guard('gent')->id();

        $reservation = DB::transaction(function () use ($request, $group, $seats, $agentId) {
            $ids = array_filter([$request->vol_id, $request->type_voyage === 'aller_retour_diffrent_vol' ? $request->vol_retour_id : null]);
            $flights = Flight::lockForUpdate()->where('vol_group_id', $group->id)->whereIn('id', $ids)->get();
            abort_if($flights->count() !== count($ids), 404);
            if ($flights->contains(fn ($flight) => $flight->place_disponible < $seats)) {
                return null;
            }

            $reservation = FlightReservation::create([
                'vol_id' => $request->vol_id,
                'vol_retour_id' => $request->type_voyage === 'aller_retour_diffrent_vol' ? $request->vol_retour_id : null,
                'nb_adult' => $request->adultcountinput2,
                'nb_enfant' => $request->enfantcountinput2 ?? 0,
                'nb_bebe' => $request->bebecountinput2 ?? 0,
                'gent_id' => $agentId,
                'type_reservation' => $request->type_reservation ?? 'Reservation',
                'type_voyage' => $request->type_voyage,
            ]);
            foreach ($request->passagers as $passenger) {
                $this->createFlightPassenger($reservation->id, $passenger);
            }
            foreach ($flights as $flight) {
                $flight->place_disponible -= $seats;
                $flight->place_reserver += $seats;
                $flight->save();
            }

            FlightDeposit::create([
                'reservation_id' => $reservation->id,
                'n_acompte' => $request->num_acompte,
                'montant' => $request->montant_acompte ?? 0,
                'agent_id' => $agentId,
            ]);
            FlightReservationHistory::create(['type' => ReservationHistoryEnum::create, 'agent_id' => $agentId, 'reservation_id' => $reservation->id]);
            $reservation->code = Hash::make($reservation->id . random_int(1000, 9999));
            $reservation->refreshTotals();

            return $reservation;
        });

        if (! $reservation) {
            return back()->withInput()->with('already_enregistred', 'already_enregistred')
                ->with('error', "Il ne reste plus assez de places sur ce vol : la vente n'a pas été enregistrée.");
        }
        $notifier->confirmedSms($reservation, SmsFlightReservationTypes::NEW_RESERVATION);
        $notifier->confirmed($reservation, 'Nouvelle réservation ' . $group->country->nom);

        return redirect()->route('agent.vols.vente.confirmation', [$group->id, $reservation->id])->with('success', 'Vente enregistrée.');
    }

    /** « exist » si un des passagers saisis a déjà une réservation active sur cette destination (appel AJAX) */
    public function checkPassenger(Request $request, FlightGroup $group)
    {
        $counts = ['adult' => (int) $request->nb_adult, 'enfant' => (int) $request->nb_enfnat, 'bebe' => (int) $request->nb_bebe];
        foreach ($counts as $type => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $exists = FlightPassenger::where('nom', $request->input("{$type}_nom_$i"))
                    ->where('prenom', $request->input("{$type}_prenom_$i"))
                    ->where('date_naissance', $request->input("{$type}_date_$i"))
                    ->whereHas('reservation', fn ($q) => $q->where('status', 1)->whereHas('vol', fn ($v) => $v->where('vol_group_id', $group->id)))
                    ->exists();
                if ($exists) {
                    return 'exist';
                }
            }
        }

        return 'no';
    }

    public function confirmation(FlightGroup $group, int $reservation)
    {
        return view('agent.vols.vente.inscription-success', [
            'reservation' => $this->find($group, $reservation, ['passagers', 'acomptes', 'vol.company', 'volRetour']),
        ]);
    }

    public function contract(FlightGroup $group, int $reservation)
    {
        return $this->pdf('agent.vols.contrat.contrat', $group, $reservation);
    }

    public function eTicket(FlightGroup $group, int $reservation)
    {
        return $this->pdf('agent.vols.contrat.e-billiet', $group, $reservation);
    }

    private function pdf(string $view, FlightGroup $group, int $id)
    {
        $reservation = $this->find($group, $id, ['gent.agence', 'passagers', 'vol.escaliers', 'vol.company', 'volRetour.escaliers', 'volRetour.company']);
        [$durationAller, $durationRetour] = $reservation->durations();

        return Pdf::loadView($view, [
            'reservation' => $reservation,
            'assurances' => Insurance::forCountry($group->country->nom)->get()->all(),
            'durationAller' => $durationAller,
            'durationRetour' => $durationRetour,
        ])->stream();
    }

    private function find(FlightGroup $group, int $id, array $with): FlightReservation
    {
        return FlightReservation::with($with)->whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))->findOrFail($id);
    }

    /** Les agences partenaires ne voient pas les prix d'achat */
    private function hidePurchasePrices($flights): void
    {
        if (Auth::guard('gent')->user()->agence->is_partner_agency) {
            $flights->each->makeHidden(self::PURCHASE_PRICES);
        }
    }
}
