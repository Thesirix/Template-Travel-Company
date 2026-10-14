<?php

namespace App\Http\Controllers\Agent\Flight;

use App\Enums\ReservationHistoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Company;
use App\Models\FlightGroup;
use App\Models\FlightReservation;
use App\Models\FlightReservationHistory;
use App\Models\Insurance;
use App\Services\FlightReservationNotifier;
use App\Traits\StoresFlightPassengers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des dossiers (agence principale) : réservations actives d'une destination
 * et mise à jour des fiches passagers (pièces jointes, dossier complet / incomplet).
 */
class FileController extends Controller
{
    use StoresFlightPassengers;

    public function index(Request $request, FlightGroup $group)
    {
        $reservations = FlightReservation::with(['gent', 'bill', 'passagers', 'vol.escaliers', 'volRetour'])
            ->where('etat', '!=', 'annuler')->where('type_reservation', 'Reservation')->where('status', true)
            ->whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))
            ->when($request->filled('gent'), fn ($q) => $q->where('gent_id', $request->gent))
            ->when($request->filled('compagnie'), fn ($q) => $q->whereHas('vol', fn ($v) => $v->where('company_id', $request->compagnie)))
            ->when($request->filled('nom'), fn ($q) => $q->whereHas('passagers', fn ($p) => $p->where('nom', 'like', "%{$request->nom}%")))
            ->when($request->filled('prenom'), fn ($q) => $q->whereHas('passagers', fn ($p) => $p->where('prenom', 'like', "%{$request->prenom}%")))
            ->when($request->filled('date_aller'), fn ($q) => $q->whereHas('vol', fn ($v) => $v->where('date_aller', $request->date_aller)))
            ->when($request->filled('date_retour'), fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('vol', fn ($v) => $v->where('date_retour', $request->date_retour))
                ->orWhereHas('volRetour', fn ($v) => $v->where('date_aller', $request->date_retour))))
            ->get()
            ->sortBy('vol.date_aller');

        return view('agent.vols.dossiers.gestion', [
            'gents' => Agent::orderBy('name')->get(),
            'compagnies' => Company::orderBy('nom')->get(),
            'reservations' => $reservations,
            'flightsGroup' => $group,
            'request' => $request,
        ]);
    }

    public function show(FlightGroup $group, int $reservation)
    {
        $reservation = FlightReservation::with(['gent', 'passagers', 'acomptes', 'vol.escaliers', 'volRetour'])
            ->whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))
            ->findOrFail($reservation);

        return view('agent.vols.dossiers.consulte', [
            'reservation' => $reservation,
            'flightsGroup' => $group,
            'assurences' => Insurance::forCountry($group->country->nom)->get(),
        ]);
    }

    public function update(Request $request, FlightGroup $group, FlightReservationNotifier $notifier)
    {
        $id = $request->validate(['reservation_id' => ['required', 'integer']])['reservation_id'];
        $reservation = FlightReservation::whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))->findOrFail($id);

        DB::transaction(function () use ($request, $reservation) {
            $reservation = FlightReservation::lockForUpdate()->with('passagers')->findOrFail($reservation->id);
            foreach ($reservation->passagers as $passenger) {
                $this->updateFlightPassengerFromForm($passenger, $request, $reservation->type_voyage);
            }
            $reservation->refreshTotals();
            FlightReservationHistory::create([
                'type' => ReservationHistoryEnum::modify_pax,
                'agent_id' => Auth::guard('gent')->id(),
                'reservation_id' => $reservation->id,
            ]);
        });
        $notifier->confirmed($reservation, 'Modification de votre réservation ' . $group->country->nom);

        return redirect()->route('agent.vols.dossiers.show', [$group->id, $reservation->id])->with('success', 'Dossier enregistré.');
    }
}
