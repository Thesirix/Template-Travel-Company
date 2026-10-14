<?php

namespace App\Http\Controllers\Agent\Boat;

use App\Http\Controllers\Controller;
use App\Mail\Boat\BookingMail;
use App\Models\BoatAccommodation;
use App\Models\BoatBooking;
use App\Models\BoatDestination;
use App\Models\BoatFare;
use App\Models\BoatPassenger;
use App\Models\BoatPassengerInsurance;
use App\Models\BoatVehicle;
use App\Models\BoatVehicleInsurance;
use App\Models\BoatVehicleSize;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Traversées en bateau : demande (inscription), modification, réservation auprès de la compagnie,
 * puis vente ou bon d'échange. Un seul formulaire (agent.bateau.form) sert à toutes les étapes.
 */
class BookingController extends Controller
{
    private const RELATIONS = ['destination', 'departurePort', 'arrivalPort', 'company', 'accommodation', 'fare', 'insurance',
        'vehicle', 'vehicleSize', 'vehicleInsurance', 'agent', 'passengers', 'reservation'];

    public function create()
    {
        return $this->form('create', new BoatBooking(['trajet' => 'aller-retour']));
    }

    public function store(Request $request)
    {
        $data = $this->validateBooking($request);
        $responsible = $this->responsiblePhone($request);
        $duplicate = $responsible && BoatBooking::whereDate('date_aller', $data['date_aller'])
            ->whereHas('passengers', fn ($q) => $q->where('tele', $responsible))->exists();
        if ($duplicate) {
            return back()->withInput()->with('error', 'Une demande existe déjà pour ce responsable à cette date.');
        }

        $booking = DB::transaction(function () use ($request, $data) {
            $booking = BoatBooking::create($data + ['gent_id' => Auth::guard('gent')->id()]);
            $this->savePassengers($booking, $request);

            return $booking;
        });
        $this->notify($booking, false);

        return redirect()->route('agent.bateau.pending')->with('success', 'Demande de traversée enregistrée.');
    }

    /** Fiche : lecture seule tant que la demande n'est pas réservée, formulaire de suivi ensuite */
    public function show(BoatBooking $booking)
    {
        return $this->form($booking->status() === 'pending' ? 'show' : 'manage', $booking);
    }

    public function edit(BoatBooking $booking)
    {
        abort_unless($booking->status() === 'pending', 404);

        return $this->form('edit', $booking);
    }

    public function update(Request $request, BoatBooking $booking)
    {
        abort_unless($booking->status() === 'pending', 404);
        $this->saveBooking($request, $booking);

        return redirect()->route('agent.bateau.show', $booking)->with('success', 'Demande modifiée.');
    }

    public function reserveForm(BoatBooking $booking)
    {
        abort_unless($booking->status() === 'pending', 404);

        return $this->form('reserve', $booking);
    }

    /** Réservation auprès de la compagnie : la demande passe en « confirmée » */
    public function reserve(Request $request, BoatBooking $booking)
    {
        abort_unless($booking->status() === 'pending', 404);
        $reservation = $this->validateReservation($request);
        DB::transaction(function () use ($request, $booking, $reservation) {
            $this->saveBooking($request, $booking);
            $booking->reservation()->create($reservation + ['etat' => 'confirmer']);
        });
        $this->notify($booking, true);

        return redirect()->route('agent.bateau.confirmed')->with('success', 'Traversée réservée.');
    }

    /**
     * Suivi d'une réservation : « final » (vendue), « echange » (bon d'échange) ou « save » (enregistrer sans changer d'état).
     */
    public function finalize(Request $request, BoatBooking $booking)
    {
        abort_if($booking->status() === 'pending', 404);
        $action = $request->validate(['action' => ['required', 'in:final,echange,save']])['action'];
        $reservation = $this->validateReservation($request);
        DB::transaction(function () use ($request, $booking, $reservation, $action) {
            $this->saveBooking($request, $booking);
            $booking->reservation->update($reservation + ($action === 'save' ? [] : ['etat' => $action]));
        });
        if ($action !== 'save') {
            $this->notify($booking, true);
        }

        return redirect()->route(match ($booking->reservation->fresh()->etat) {
            'final' => 'agent.bateau.sold',
            'echange' => 'agent.bateau.vouchers',
            default => 'agent.bateau.confirmed',
        })->with('success', 'Réservation enregistrée.');
    }

    public function destroy(BoatBooking $booking)
    {
        DB::transaction(function () use ($booking) {
            $this->deleteFiles($booking->passengers->pluck('passport')->push($booking->carte_grise));
            $booking->reservation()->delete();
            $booking->passengers()->delete();
            $booking->delete();
        });

        return redirect()->route('agent.bateau.pending')->with('success', 'Demande supprimée.');
    }

    public function destroyPassenger(BoatPassenger $passenger)
    {
        $booking = $passenger->booking;
        DB::transaction(function () use ($passenger, $booking) {
            $this->deleteFiles(collect([$passenger->passport]));
            $passenger->delete();
            $this->refreshCounts($booking);
        });

        return back()->with('success', 'Passager supprimé.');
    }

    /** Ports et compagnies d'une destination (listes du formulaire) */
    public function catalog(BoatDestination $boatDestination)
    {
        $list = fn ($relation) => $boatDestination->$relation()->orderBy('name')->get(['id', 'name']);

        return ['departurePorts' => $list('departurePorts'), 'arrivalPorts' => $list('arrivalPorts'), 'companies' => $list('companies')];
    }

    /**
     * Formulaire commun. $mode : create, edit, show (lecture seule), reserve, manage (réservation existante).
     */
    private function form(string $mode, BoatBooking $booking)
    {
        $booking->loadMissing(self::RELATIONS);
        $destinations = BoatDestination::with(['departurePorts', 'arrivalPorts', 'companies'])->orderBy('name')->get();
        $destination = $destinations->firstWhere('id', old('destination_id', $booking->destination_id)) ?? $destinations->first();

        return view('agent.bateau.form', [
            'mode' => $mode,
            'booking' => $booking,
            'destinations' => $destinations,
            'destination' => $destination,
            'accommodations' => BoatAccommodation::orderBy('name')->get(),
            'fares' => BoatFare::orderBy('name')->get(),
            'insurances' => BoatPassengerInsurance::orderBy('name')->get(),
            'vehicles' => BoatVehicle::orderBy('name')->get(),
            'vehicleSizes' => BoatVehicleSize::orderBy('name')->get(),
            'vehicleInsurances' => BoatVehicleInsurance::orderBy('name')->get(),
            'passengerTypes' => BoatBooking::PASSENGER_TYPES,
        ]);
    }

    private function saveBooking(Request $request, BoatBooking $booking): void
    {
        $data = $this->validateBooking($request, $booking);
        DB::transaction(function () use ($request, $booking, $data) {
            $booking->update($data);
            $this->savePassengers($booking, $request);
        });
    }

    /** Champs de la demande, contrôles d'âge des passagers et carte grise */
    private function validateBooking(Request $request, ?BoatBooking $booking = null): array
    {
        $roundTrip = $request->input('trajet') === 'aller-retour';
        $vehicle = $request->filled('vehicle_id');
        $data = $request->validate([
            'trajet' => ['required', 'in:aller-simple,aller-retour'],
            'destination_id' => ['required', 'exists:boat_destinations,id'],
            'departure_port_id' => ['required', 'exists:boat_departure_ports,id'],
            'arrival_port_id' => [$roundTrip ? 'required' : 'nullable', 'exists:boat_arrival_ports,id'],
            'company_id' => ['required', 'exists:boat_companies,id'],
            'accommodation_id' => ['required', 'exists:boat_accommodations,id'],
            'fare_id' => ['required', 'exists:boat_fares,id'],
            'insurance_id' => ['required', 'exists:boat_passenger_insurances,id'],
            'date_aller' => ['required', 'date'],
            'date_retour' => [$roundTrip ? 'required' : 'nullable', 'date', 'after_or_equal:date_aller'],
            'vehicle_id' => ['nullable', 'exists:boat_vehicles,id'],
            'vehicle_size_id' => [$vehicle ? 'required' : 'nullable', 'exists:boat_vehicle_sizes,id'],
            'vehicle_insurance_id' => [$vehicle ? 'required' : 'nullable', 'exists:boat_vehicle_insurances,id'],
            'voiture_marque' => [$vehicle ? 'required' : 'nullable', 'string', 'max:50'],
            'voiture_modele' => [$vehicle ? 'required' : 'nullable', 'string', 'max:50'],
            'voiture_matricule' => [$vehicle ? 'required' : 'nullable', 'string', 'max:50'],
            'voiture_anne' => [$vehicle ? 'required' : 'nullable', 'date'],
            'carte_grise' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
            'note' => ['nullable', 'string', 'max:300'],
            'passengers' => ['nullable', 'array'],
            'passengers.*.nom' => ['required', 'string', 'max:200'],
            'passengers.*.prenom' => ['required', 'string', 'max:200'],
            'passengers.*.date_naissance' => ['required', 'date'],
            'nouveaux' => ['nullable', 'array'],
            'nouveaux.*.type' => ['required', 'in:' . implode(',', array_keys(BoatBooking::PASSENGER_TYPES))],
            'nouveaux.*.nom' => ['required', 'string', 'max:200'],
            'nouveaux.*.prenom' => ['required', 'string', 'max:200'],
            'nouveaux.*.date_naissance' => ['required', 'date', 'before_or_equal:today'],
            'nouveaux.*.passport_num' => ['required', 'string', 'max:15'],
            'nouveaux.*.tele' => ['nullable', 'string', 'max:15'],
            'nouveaux.*.mail' => ['nullable', 'email', 'max:200'],
            'nouveaux.*.passport' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ], [], ['nouveaux.*.passport_num' => 'n° de passeport', 'insurance_id' => 'assurance', 'fare_id' => 'tarif']);

        if (! $booking && empty($data['nouveaux'])) {
            throw ValidationException::withMessages(['nouveaux' => 'Ajoutez au moins un passager.']);
        }
        $this->checkAges($data['nouveaux'] ?? [], $data['date_aller'], $roundTrip ? $data['date_retour'] : null);

        $attributes = collect($data)->except(['passengers', 'nouveaux', 'carte_grise'])->all();
        if (! $roundTrip) {
            $attributes['arrival_port_id'] = null;
            $attributes['date_retour'] = null;
        }
        if (! $vehicle) {
            foreach (['vehicle_id', 'vehicle_size_id', 'vehicle_insurance_id', 'voiture_marque', 'voiture_modele', 'voiture_matricule', 'voiture_anne'] as $field) {
                $attributes[$field] = null;
            }
        }
        if ($request->hasFile('carte_grise')) {
            $this->deleteFiles(collect([$booking?->carte_grise]));
            $attributes['carte_grise'] = $request->file('carte_grise')->store('bateau/cartes-grises', 'public');
        }

        return $attributes;
    }

    /** Chaque catégorie de passager a sa tranche d'âge, vérifiée au départ (au retour pour un bébé) */
    private function checkAges(array $passengers, string $departure, ?string $return): void
    {
        foreach ($passengers as $index => $passenger) {
            [$label, $min, $max] = BoatBooking::PASSENGER_TYPES[$passenger['type']];
            $age = Carbon::parse($passenger['date_naissance'])->diffInYears(Carbon::parse($passenger['type'] === 'bebe' ? ($return ?? $departure) : $departure));
            if ($age < $min || $age > $max) {
                throw ValidationException::withMessages([
                    "nouveaux.$index.date_naissance" => "{$passenger['prenom']} {$passenger['nom']} : un passager « $label » doit avoir entre $min et $max ans le jour du voyage.",
                ]);
            }
        }
    }

    /** Passagers existants modifiés, nouveaux passagers ajoutés, responsable et compteurs mis à jour */
    private function savePassengers(BoatBooking $booking, Request $request): void
    {
        $responsible = (string) $request->input('responsable');
        foreach ($booking->passengers()->get() as $passenger) {
            $values = $request->input("passengers.$passenger->id");
            if (! $values) {
                continue;
            }
            $update = collect($values)->only(['nom', 'prenom', 'date_naissance', 'tele', 'mail', 'passport_num'])->all();
            if ($file = $request->file("passengers.$passenger->id.passport")) {
                $this->deleteFiles(collect([$passenger->passport]));
                $update['passport'] = $file->store('bateau/passeports', 'public');
            }
            if ($responsible !== '') {
                $update['respo'] = $responsible === "existing:$passenger->id";
            }
            $passenger->update($update + $this->ages($booking, $update['date_naissance'] ?? $passenger->date_naissance));
        }

        foreach ($request->input('nouveaux', []) as $index => $values) {
            $file = $request->file("nouveaux.$index.passport");
            $booking->passengers()->create([
                'type' => BoatBooking::PASSENGER_TYPES[$values['type']][0],
                'nom' => $values['nom'],
                'prenom' => $values['prenom'],
                'date_naissance' => $values['date_naissance'],
                'tele' => $values['tele'] ?? null,
                'mail' => $values['mail'] ?? null,
                'passport_num' => $values['passport_num'] ?? null,
                'passport' => $file?->store('bateau/passeports', 'public'),
                'respo' => $responsible === "new:$index",
            ] + $this->ages($booking, $values['date_naissance']));
        }

        if (! $booking->passengers()->where('respo', true)->exists()) {
            $booking->passengers()->orderBy('id')->limit(1)->update(['respo' => true]);
        }
        $this->refreshCounts($booking);
    }

    private function ages(BoatBooking $booking, string $birthDate): array
    {
        $birth = Carbon::parse($birthDate);

        return [
            'ageA' => (int) $birth->diffInYears(Carbon::parse($booking->date_aller)),
            'ageR' => $booking->date_retour ? (int) $birth->diffInYears(Carbon::parse($booking->date_retour)) : null,
        ];
    }

    private function refreshCounts(BoatBooking $booking): void
    {
        $counts = $booking->passengers()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');
        $booking->update([
            'nb_adulte' => $counts['Adulte'] ?? 0,
            'nb_senior' => $counts['Senior'] ?? 0,
            'nb_jeune' => $counts['Jeune'] ?? 0,
            'nb_enfant' => $counts['Enfant'] ?? 0,
            'nb_bebe' => $counts['Bebe'] ?? 0,
        ]);
    }

    private function validateReservation(Request $request): array
    {
        $data = $request->validate([
            'num_reservation' => ['required', 'string', 'max:100'],
            'date_option' => ['required', 'date'],
            'reservation_prix' => ['required', 'numeric', 'min:0'],
            'details' => ['nullable', 'string', 'max:500'],
        ], [], ['num_reservation' => 'n° de réservation', 'date_option' => "date d'option", 'reservation_prix' => 'prix']);

        return ['num_reservation' => $data['num_reservation'], 'date_option' => $data['date_option'],
            'prix' => $data['reservation_prix'], 'details' => $data['details'] ?? null];
    }

    private function responsiblePhone(Request $request): ?string
    {
        $responsible = (string) $request->input('responsable');

        return str_starts_with($responsible, 'new:') ? $request->input('nouveaux.' . substr($responsible, 4) . '.tele') : null;
    }

    /** Récapitulatif envoyé aux passagers qui ont un e-mail */
    private function notify(BoatBooking $booking, bool $confirmed): void
    {
        $emails = $booking->passengers()->pluck('mail')->filter()->unique()->values()->all();
        if (! $emails) {
            return;
        }
        try {
            Mail::to($emails)->send(new BookingMail($booking->id, $confirmed));
        } catch (Throwable $exception) {
            Log::error('Bateau : e-mail non envoyé', ['booking' => $booking->id, 'exception' => $exception]);
        }
    }

    private function deleteFiles($paths): void
    {
        $paths->filter()->each(fn ($path) => Storage::disk('public')->delete($path));
    }
}
