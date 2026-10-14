<?php

namespace App\Http\Controllers\Agent\Flight;

use App\Enums\FlightType;
use App\Enums\ReservationHistoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Company;
use App\Models\Flight;
use App\Models\FlightCancellation;
use App\Models\FlightGroup;
use App\Models\FlightPassenger;
use App\Models\FlightReservation;
use App\Models\FlightReservationHistory;
use App\Models\Insurance;
use App\Services\FlightReservationNotifier;
use App\Services\FlightSalesStatistics;
use App\Traits\StoresFlightPassengers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Suivi des réservations de vol d'une destination : liste, fiche, ajout / modification de passagers,
 * annulation (réservation entière ou un passager), suppression, changement de vol.
 * Une agence partenaire ne voit que les réservations de ses propres agents.
 */
class ReservationController extends Controller
{
    use StoresFlightPassengers;

    private const NO_SEATS = 'Le nombre de places disponibles est inférieur au nombre de passagers.';

    public function __construct(private FlightReservationNotifier $notifier)
    {
    }

    public function index(Request $request, FlightGroup $group)
    {
        $agent = Auth::guard('gent')->user();
        $reservations = FlightReservation::selectRaw('vol_reservations.*, vol_reservation_histories.id as history_id,
                vol_reservation_histories.type as history_type, vol_reservation_histories.details as history_details,
                vol_reservation_histories.created_at as history_date')
            ->join('vol_reservation_histories', 'vol_reservation_histories.reservation_id', '=', 'vol_reservations.id')
            ->leftJoin('gents as reservation_agent', 'vol_reservations.gent_id', '=', 'reservation_agent.id')
            ->leftJoin('vols', 'vol_reservations.vol_id', '=', 'vols.id')
            ->where('vol_reservations.etat', '!=', 'annuler')
            ->where('vol_reservations.type_reservation', 'Reservation')
            ->where('vols.vol_group_id', $group->id)
            ->whereIn('vol_reservation_histories.type', [ReservationHistoryEnum::create, ReservationHistoryEnum::cancel,
                ReservationHistoryEnum::modify_date, ReservationHistoryEnum::delete_passager, ReservationHistoryEnum::add_passager])
            ->when($agent->agence->is_partner_agency, fn ($q) => $q->where('reservation_agent.agence_id', $agent->agence_id))
            ->when($request->filled('gent'), fn ($q) => $q->where('vol_reservations.gent_id', $request->gent))
            ->when($request->filled('compagnie'), fn ($q) => $q->where('vols.company_id', $request->compagnie))
            ->when($request->filled('nom'), fn ($q) => $q->whereHas('passagers', fn ($p) => $p->where('nom', 'like', "%{$request->nom}%")))
            ->when($request->filled('prenom'), fn ($q) => $q->whereHas('passagers', fn ($p) => $p->where('prenom', 'like', "%{$request->prenom}%")))
            ->when($request->filled('date_aller'), fn ($q) => $q->where('vols.date_aller', $request->date_aller))
            ->when($request->filled('date_retour'), fn ($q) => $q->where(fn ($q) => $q->where('vols.date_retour', $request->date_retour)
                ->orWhereHas('volRetour', fn ($r) => $r->where('date_aller', $request->date_retour))))
            ->with(['passagers', 'gent', 'vol.company', 'volRetour'])
            ->orderByDesc('vol_reservation_histories.id')
            ->get();

        $outboundDates = [];
        $returnDates = [];
        foreach ($group->flights as $flight) {
            if ($flight->type_voyage !== FlightType::RETURN->value) {
                $outboundDates[] = $flight->date_aller;
            }
            if ($flight->type_voyage === FlightType::ROUND_TRIP->value) {
                $returnDates[] = $flight->date_retour;
            } elseif ($flight->type_voyage === FlightType::RETURN->value) {
                $returnDates[] = $flight->date_aller;
            }
        }

        return view('agent.vols.reservations.list', [
            'gents' => Agent::orderBy('name')->get(),
            'compagnies' => Company::orderBy('nom')->get(),
            'reservations' => $reservations,
            'request' => $request,
            'flightsGroup' => $group,
            'outboundDates' => $outboundDates,
            'returnDates' => $returnDates,
        ]);
    }

    public function show(FlightGroup $group, int $reservation)
    {
        $reservation = $this->accessible($group, $reservation, ['bill', 'gent', 'passagers.assurences', 'vol.escaliers', 'volRetour']);
        $fee = $reservation->cancellationFee();
        $passengers = $reservation->nb_adult + $reservation->nb_enfant + $reservation->nb_bebe;

        return view('agent.vols.reservations.consulte', [
            'reservation' => $reservation,
            'flightsGroup' => $group,
            'assurences' => Insurance::forCountry($group->country->nom)->get(),
            // Frais d'annulation par passager (« 100% » : billet entier) et pour toute la réservation
            'annulaion_price' => $fee ?? '100%',
            'annuletout_price' => $fee === null ? $reservation->prix_total : $passengers * $fee,
        ]);
    }

    /** Fiche réservation : n° de facture, fiches passagers modifiées et nouveaux passagers */
    public function update(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'billNumber' => ['nullable', 'string', 'max:50'],
            'nb_new_adult' => ['nullable', 'integer', 'min:0'],
            'nb_new_enfant' => ['nullable', 'integer', 'min:0'],
            'nb_new_bebe' => ['nullable', 'integer', 'min:0'],
            'passagers' => ['nullable', 'array'],
            'passagers.*.type' => ['required', 'in:adult,adulte,enfant,bebe'],
            'passagers.*.nom' => ['required', 'string', 'max:200'],
            'passagers.*.prenom' => ['required', 'string', 'max:200'],
            'passagers.*.date_naissance' => ['required', 'date'],
            'passagers.*.assurance' => ['required', 'exists:assurences,id'],
        ]);
        $reservation = $this->accessible($group, $data['reservation_id']);
        $newSeats = (int) ($data['nb_new_adult'] ?? 0) + (int) ($data['nb_new_enfant'] ?? 0);
        $added = ! empty($data['passagers']);

        $saved = DB::transaction(function () use ($request, $data, $reservation, $newSeats, $added) {
            $reservation = FlightReservation::lockForUpdate()->with(['passagers', 'bill'])->findOrFail($reservation->id);
            $flights = $this->lockFlights($reservation);
            if ($newSeats > 0 && collect($flights)->contains(fn ($flight) => $flight->place_disponible < $newSeats)) {
                return false;
            }

            if ($request->has('billNumber') && $reservation->bill) {
                $reservation->bill->update(['n_acompte' => $data['billNumber']]);
            }
            foreach ($reservation->passagers as $passenger) {
                $this->updateFlightPassengerFromForm($passenger, $request, $reservation->type_voyage, withVerification: true);
            }
            foreach ($data['passagers'] ?? [] as $index => $passenger) {
                $this->createFlightPassenger($reservation->id, $request->input("passagers.$index") + $request->file("passagers.$index", []));
            }

            $reservation->nb_adult += (int) ($data['nb_new_adult'] ?? 0);
            $reservation->nb_enfant += (int) ($data['nb_new_enfant'] ?? 0);
            $reservation->nb_bebe += (int) ($data['nb_new_bebe'] ?? 0);
            $reservation->save();
            $this->moveSeats($flights, $newSeats);
            $reservation->refreshTotals();
            $this->history($reservation, $added ? ReservationHistoryEnum::add_passager : ReservationHistoryEnum::modify_pax);

            return true;
        });

        if (! $saved) {
            return back()->withInput()->with('error', self::NO_SEATS);
        }
        $this->notifier->confirmed($reservation, 'Modification de votre réservation ' . $group->country->nom);

        return back()->with('success', 'Réservation enregistrée.');
    }

    /** Annule toute la réservation : places libérées, pièces jointes supprimées, réservation désactivée */
    public function cancel(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'all_annulation_reason' => ['required', 'string', 'max:1000'],
        ]);
        $reservation = $this->accessible($group, $data['reservation_id']);
        if (! $reservation->status) {
            return back()->with('warning', 'Cette réservation est déjà annulée.');
        }

        DB::transaction(function () use ($reservation, $data) {
            $reservation = FlightReservation::lockForUpdate()->with('passagers')->findOrFail($reservation->id);
            $this->moveSeats($this->lockFlights($reservation), -$reservation->seats());
            $reservation->passagers->each(fn ($passenger) => $this->deletePassengerDocuments($passenger));
            $reservation->update(['status' => false]);
            $this->history($reservation, ReservationHistoryEnum::cancel, $data['all_annulation_reason']);
        });
        $this->notifier->canceled($reservation, $reservation->nb_adult + $reservation->nb_enfant + $reservation->nb_bebe);

        return redirect()->route('agent.vols.reservations', $group->id)->with('success', 'Réservation annulée.');
    }

    /**
     * Annule un passager : frais d'annulation selon le barème du vol (billet entier le jour du départ),
     * place libérée (sauf bébé). Si c'était le dernier passager avec un siège, toute la réservation est annulée.
     */
    public function cancelPassenger(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'passager_id' => ['required', 'integer'],
            'all_annulation_reason' => ['required', 'string', 'max:1000'],
        ]);
        $reservation = $this->accessible($group, $data['reservation_id']);
        $passenger = FlightPassenger::where('reservation_id', $reservation->id)->findOrFail($data['passager_id']);

        $wholeReservation = DB::transaction(function () use ($reservation, $passenger, $data) {
            $reservation = FlightReservation::lockForUpdate()->with(['vol', 'volRetour', 'passagers'])->findOrFail($reservation->id);
            $flights = $this->lockFlights($reservation);
            $fee = $reservation->cancellationFee();
            FlightCancellation::create([
                'reservation_id' => $reservation->id,
                'reason' => $data['all_annulation_reason'],
                'montant' => $fee ?? $reservation->farePrice($passenger->type),
            ]);

            $lastSeat = $passenger->type !== 'bebe' && $reservation->seats() === 1;
            if ($lastSeat) {
                $this->moveSeats($flights, -1);
                $reservation->passagers->each(fn ($other) => $this->deletePassengerDocuments($other));
                $reservation->update(['status' => false]);
                $this->history($reservation, ReservationHistoryEnum::cancel);

                return true;
            }

            $counter = ['adulte' => 'nb_adult', 'enfant' => 'nb_enfant'][$passenger->type] ?? 'nb_bebe';
            $reservation->decrement($counter);
            if ($passenger->type !== 'bebe') {
                $this->moveSeats($flights, -1);
            }
            $this->deletePassengerDocuments($passenger);
            $passenger->delete();
            $reservation->refreshTotals();
            $this->history($reservation, ReservationHistoryEnum::delete_passager);

            return false;
        });
        $this->notifier->canceled($reservation, 1);

        return $wholeReservation
            ? redirect()->route('agent.vols.reservations', $group->id)->with('success', 'Réservation annulée.')
            : back()->with('success', 'Passager annulé.');
    }

    /**
     * Suppression définitive, réservée à un administrateur connecté dans le même navigateur
     * (le bouton n'apparaît qu'à lui). Libère les places si la réservation était active.
     */
    public function destroy(Request $request, FlightGroup $group)
    {
        abort_unless(Auth::guard('gestion')->check(), 403);
        $reservation = $this->accessible($group, $request->validate(['reservation_id' => ['required', 'integer']])['reservation_id']);

        DB::transaction(function () use ($reservation) {
            $reservation = FlightReservation::lockForUpdate()->with('passagers')->findOrFail($reservation->id);
            if ($reservation->status) {
                $this->moveSeats($this->lockFlights($reservation), -$reservation->seats());
            }
            $reservation->passagers->each(fn ($passenger) => $this->deletePassengerDocuments($passenger));
            FlightReservationHistory::where('reservation_id', $reservation->id)->delete();
            $reservation->passagers()->delete();
            $reservation->acomptes()->delete();
            $reservation->annulations()->delete();
            $reservation->delete();
        });

        return redirect()->route('agent.vols.reservations', $group->id)->with('success', 'Réservation supprimée.');
    }

    /** Étape 1 du changement de vol : liste des vols ouverts de la destination */
    public function changeFlight(FlightGroup $group, int $reservation, FlightSalesStatistics $statistics)
    {
        $reservation = $this->accessible($group, $reservation, ['vol']);
        $flightGroup = FlightGroup::with(['country', 'flights' => fn ($q) => $q->where('is_archive', false)
            ->where(fn ($q) => $q->whereRaw('CURDATE() < DATE_SUB(date_aller, INTERVAL days_before_closure DAY)')
                ->orWhere('place_reserver', '>', 0))
            ->with(['escaliers', 'ville', 'company'])->orderBy('date_aller')])->findOrFail($group->id);

        if (Auth::guard('gent')->user()->agence->is_partner_agency) {
            $flightGroup->flights->each->makeHidden(['prix_achat_adult', 'prix_achat_enfant', 'prix_achat_bebe']);
        }
        $hasFlights = $flightGroup->flights->isNotEmpty();

        return view('agent.vols.vente.allvols', [
            'flightGroup' => $flightGroup,
            'reservation' => $reservation,
            'reservation_id' => $reservation->id,
            'type_reservation' => 'Reservation_noListeAttente',
            'agentsStatistics' => $hasFlights ? $statistics->byAgent($group->id) : null,
            'agenciesStatistics' => $hasFlights ? $statistics->byAgency($group->id) : null,
            'destinationsStatistics' => $hasFlights ? $statistics->byCity($group->id) : null,
        ]);
    }

    /** Étape 2 : récapitulatif avec le nouveau vol choisi (aller, retour ou les deux) */
    public function previewFlightChange(Request $request, FlightGroup $group)
    {
        if (! $request->vol_id && ! $request->vol_id_retour) {
            return back()->with('warning', 'Choisissez un vol.');
        }
        $reservation = $this->accessible($group, (int) $request->reservation_id, ['gent', 'passagers.assurences', 'vol.escaliers', 'volRetour']);
        $load = fn ($id) => Flight::with(['escaliers.airPortDe', 'escaliers.airPortA', 'company'])->where('vol_group_id', $group->id)->findOrFail($id);

        $vol = null;
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

        $price = fn (Flight $flight) => $reservation->nb_adult * $flight->prix_vente_adult
            + $reservation->nb_enfant * $flight->prix_vente_enfant + $reservation->nb_bebe * $flight->prix_vente_bebe;
        $devisTotalPrice = $price($vol) + ($vol_retour ? $price($vol_retour) : 0)
            + $reservation->passagers->sum(fn ($passenger) => (float) ($passenger->assurences->prix ?? 0));

        return view('agent.vols.reservations.consulte-date', [
            'assurences' => Insurance::forCountry($group->country->nom)->get(),
            'type_reservation' => 'Reservation_noListeAttente',
            'type_voyage' => $type_voyage,
            'flightsGroup' => $group,
            'vol' => $vol,
            'vol_retour' => $vol_retour,
            'reservation' => $reservation,
            'durationAller' => Carbon::parse($vol->duration_aller),
            'durationRetour' => $durationRetour,
            'adultcount' => $reservation->nb_adult,
            'enfantcount' => $reservation->nb_enfant,
            'bebecount' => $reservation->nb_bebe,
            'devisTotalPrice' => $devisTotalPrice,
        ]);
    }

    /** Étape 3 : déplace la réservation sur le(s) nouveau(x) vol(s) et libère les places des anciens */
    public function applyFlightChange(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'vol_id' => ['required', 'integer'],
            'vol_retour_id' => ['nullable', 'required_if:type_voyage,aller_retour_diffrent_vol', 'integer'],
            'type_voyage' => ['required', 'in:aller_simple,retour_simple,aller_retour_same_vol,aller_retour_diffrent_vol'],
        ]);
        $reservation = $this->accessible($group, $data['reservation_id']);

        $details = DB::transaction(function () use ($data, $reservation, $group) {
            $reservation = FlightReservation::lockForUpdate()->with(['vol', 'volRetour'])->findOrFail($reservation->id);
            $oldDates = $this->dateLabel($reservation);
            $oldFlights = $this->lockFlights($reservation);

            $reservation->vol_id = $data['vol_id'];
            $reservation->vol_retour_id = $data['type_voyage'] === 'aller_retour_diffrent_vol' ? $data['vol_retour_id'] : null;
            $reservation->type_voyage = $data['type_voyage'];
            $reservation->unsetRelation('vol')->unsetRelation('volRetour');
            $newFlights = $this->lockFlights($reservation);
            if (collect($newFlights)->contains(fn ($flight) => $flight->vol_group_id != $group->id)) {
                abort(404);
            }
            if (collect($newFlights)->contains(fn ($flight) => $flight->place_disponible < $reservation->seats())) {
                return null;
            }

            $this->moveSeats($oldFlights, -$reservation->seats());
            $this->moveSeats($newFlights, $reservation->seats());
            $reservation->save();
            $reservation->load(['vol', 'volRetour']);
            $details = 'Ancienne date : ' . $oldDates . ', nouvelle date : ' . $this->dateLabel($reservation);
            $this->history($reservation, ReservationHistoryEnum::modify_date, $details);
            $reservation->refreshTotals();

            return $details;
        });

        if ($details === null) {
            return back()->withInput()->with('error', self::NO_SEATS);
        }
        $this->notifier->confirmed($reservation, 'Changement de date de votre réservation ' . $group->country->nom);

        return redirect()->route('agent.vols.reservations.show', [$group->id, $reservation->id])->with('success', 'Vol modifié. ' . $details);
    }

    /** Supprime la copie du passeport ou du titre de séjour d'un passager */
    public function destroyPassportImage(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'passenger_id' => ['required', 'integer'],
            'file_type' => ['required', 'in:passport,sejour'],
        ]);
        $passenger = FlightPassenger::findOrFail($data['passenger_id']);
        $this->accessible($group, $passenger->reservation_id);

        $column = $data['file_type'] === 'passport' ? 'passport_image' : 'sijour_image';
        $this->deletePassengerDocuments(new FlightPassenger([$column => $passenger->$column]));
        $passenger->update([$column => null]);

        return back()->with('success', 'Pièce jointe supprimée.');
    }

    /** Réservation de la destination, visible par l'agent (une agence partenaire ne voit que les siennes) */
    private function accessible(FlightGroup $group, int $id, array $with = []): FlightReservation
    {
        $reservation = FlightReservation::with(array_merge(['gent'], $with))
            ->whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))
            ->findOrFail($id);
        $agent = Auth::guard('gent')->user();
        abort_if($agent->agence->is_partner_agency && $reservation->gent?->agence_id !== $agent->agence_id, 403);

        return $reservation;
    }

    /** Vols de la réservation verrouillés pour mettre à jour leurs places */
    private function lockFlights(FlightReservation $reservation): array
    {
        $ids = [$reservation->vol_id];
        if ($reservation->type_voyage === 'aller_retour_diffrent_vol' && $reservation->vol_retour_id) {
            $ids[] = $reservation->vol_retour_id;
        }

        return Flight::lockForUpdate()->whereIn('id', $ids)->get()->all();
    }

    /** Réserve ($seats > 0) ou libère ($seats < 0) des sièges sur chaque vol */
    private function moveSeats(array $flights, int $seats): void
    {
        foreach ($flights as $flight) {
            $flight->place_disponible -= $seats;
            $flight->place_reserver += $seats;
            $flight->save();
        }
    }

    private function history(FlightReservation $reservation, ReservationHistoryEnum $type, ?string $details = null): void
    {
        FlightReservationHistory::create([
            'type' => $type,
            'agent_id' => Auth::guard('gent')->id(),
            'reservation_id' => $reservation->id,
            'details' => $details,
        ]);
    }

    private function dateLabel(FlightReservation $reservation): string
    {
        return trim(($reservation->outboundDate()?->format('d/m/Y') ?? '') . ' → ' . ($reservation->returnDate()?->format('d/m/Y') ?? ''), ' →');
    }
}
