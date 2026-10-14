<?php

namespace App\Http\Controllers\Agent\Flight;

use App\Enums\ReservationHistoryEnum;
use App\Exports\FlightPassengersExport;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Flight;
use App\Models\FlightGroup;
use App\Models\FlightPassenger;
use App\Models\FlightReservation;
use App\Models\FlightReservationHistory;
use App\Models\Insurance;
use App\Services\FlightReservationNotifier;
use App\Traits\StoresFlightPassengers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Émission des billets (agence principale) : vols de la destination, passagers par vol,
 * vérification des fiches, exports pour la compagnie.
 */
class IssuanceController extends Controller
{
    use StoresFlightPassengers;

    public function index(FlightGroup $group)
    {
        $group->load(['country', 'flights' => fn ($q) => $q->with(['escaliers', 'ville', 'company'])->orderBy('date_aller')]);

        return view('agent.vols.emission.allvols', ['flightsGroup' => $group]);
    }

    /** Réservations d'un vol (un vol « retour simple » porte les retours des réservations sur deux vols) */
    public function flight(FlightGroup $group, int $flight)
    {
        $vol = Flight::where('vol_group_id', $group->id)->findOrFail($flight);
        $relation = $vol->type_voyage === 'retour_simple' ? 'reservations2' : 'reservations';
        $vol->load([$relation => fn ($q) => $q->with(['passagers', 'gent', 'bill'])->where('type_reservation', 'Reservation')]);

        return view('agent.vols.emission.listconsulte', ['vol' => $vol, 'flightsGroup' => $group]);
    }

    public function export(Request $request, FlightGroup $group, string $format = 'list')
    {
        abort_unless(in_array($format, FlightPassengersExport::FORMATS, true), 404);
        $vol = Flight::where('vol_group_id', $group->id)->findOrFail($request->validate(['vol_id' => ['required', 'integer']])['vol_id']);
        $reservations = FlightReservation::with(['passagers', 'gent'])->where('status', true)
            ->where(fn ($q) => $q->where('vol_id', $vol->id)->orWhere('vol_retour_id', $vol->id))
            ->get();
        $name = ($format === 'list' ? '' : $format . '-') . ($vol->pnr ?: 'vol-' . $vol->id) . ($format === 'ethiopian' ? '.ods' : '.xlsx');

        return Excel::download(new FlightPassengersExport($reservations, $format), $name);
    }

    public function reservation(FlightGroup $group, int $reservation)
    {
        $reservation = $this->find($group, $reservation, ['gent', 'passagers', 'acomptes', 'vol.escaliers', 'volRetour']);

        return view('agent.vols.emission.consulte', [
            'reservation' => $reservation,
            'flightsGroup' => $group,
            'assurences' => Insurance::forCountry($group->country->nom)->get(),
            'agents' => Agent::orderBy('name')->get(),
        ]);
    }

    public function updatePassengers(Request $request, FlightGroup $group, FlightReservationNotifier $notifier)
    {
        $reservation = $this->find($group, $request->validate(['reservation_id' => ['required', 'integer']])['reservation_id']);

        DB::transaction(function () use ($request, $reservation) {
            $reservation = FlightReservation::lockForUpdate()->with('passagers')->findOrFail($reservation->id);
            foreach ($reservation->passagers as $passenger) {
                $this->updateFlightPassengerFromForm($passenger, $request, $reservation->type_voyage);
            }
            $reservation->refreshTotals();
            $this->history($reservation);
        });
        $notifier->confirmed($reservation, 'Modification de votre réservation ' . $group->country->nom);

        return back()->with('success', 'Passagers enregistrés.');
    }

    /** N° de facture et agent vendeur */
    public function updateReservation(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'billNumber' => ['nullable', 'string', 'max:50'],
            'reservationAgent' => ['required', 'exists:gents,id'],
        ]);
        $reservation = $this->find($group, $data['reservation_id'], ['bill']);

        DB::transaction(function () use ($reservation, $data) {
            $reservation->bill?->update(['n_acompte' => $data['billNumber']]);
            $reservation->update(['gent_id' => $data['reservationAgent']]);
            $this->history($reservation);
        });

        return back()->with('success', 'Réservation enregistrée.');
    }

    public function verifyPassenger(Request $request, FlightGroup $group)
    {
        $passenger = FlightPassenger::findOrFail($request->validate(['passager_verifer_id' => ['required', 'integer']])['passager_verifer_id']);
        $this->find($group, $passenger->reservation_id);
        $passenger->update(['verifier' => 'Oui']);

        return back()->with('success', 'Passager vérifié.');
    }

    private function find(FlightGroup $group, int $id, array $with = []): FlightReservation
    {
        return FlightReservation::with($with)->where('status', true)
            ->whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))
            ->findOrFail($id);
    }

    private function history(FlightReservation $reservation): void
    {
        FlightReservationHistory::create([
            'type' => ReservationHistoryEnum::modify_pax,
            'agent_id' => Auth::guard('gent')->id(),
            'reservation_id' => $reservation->id,
        ]);
    }
}
