<?php

namespace App\Http\Controllers\Agent\Flight;

use App\Enums\ReservationHistoryEnum;
use App\Http\Controllers\Controller;
use App\Models\FlightDeposit;
use App\Models\FlightGroup;
use App\Models\FlightReservation;
use App\Models\FlightReservationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Acomptes d'une réservation de vol (fiches dossier et émission) : ajout, modification, suppression.
 * Chaque opération recalcule le montant payé et laisse une trace dans l'historique de la réservation.
 */
class PaymentController extends Controller
{
    public function store(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'n_acompte' => ['required', 'string', 'max:50'],
            'montant' => ['required', 'numeric', 'min:0'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);
        $reservation = $this->reservation($group, $data['reservation_id']);

        DB::transaction(function () use ($data, $reservation) {
            FlightDeposit::create($data + ['agent_id' => Auth::guard('gent')->id()]);
            $this->record($reservation, ReservationHistoryEnum::add_payment, $data['n_acompte'] . ', montant ' . $data['montant'] . ' €');
        });

        return back()->with('success', 'Paiement ajouté.');
    }

    public function update(Request $request, FlightGroup $group)
    {
        $data = $request->validate([
            'acompte_id' => ['required', 'integer'],
            'n_acompte' => ['required', 'string', 'max:50'],
            'montant' => ['required', 'numeric', 'min:0'],
            'created_at' => ['required', 'date'],
        ]);
        $deposit = FlightDeposit::findOrFail($data['acompte_id']);
        $reservation = $this->reservation($group, $deposit->reservation_id);

        DB::transaction(function () use ($data, $deposit, $reservation) {
            $details = $deposit->n_acompte . ', montant avant ' . $deposit->montant . ' €, montant après ' . $data['montant'] . ' €';
            $deposit->update(['n_acompte' => $data['n_acompte'], 'montant' => $data['montant'], 'created_at' => $data['created_at']]);
            $this->record($reservation, ReservationHistoryEnum::modify_payment, $details);
        });

        return back()->with('success', 'Paiement modifié.');
    }

    public function destroy(Request $request, FlightGroup $group)
    {
        $deposit = FlightDeposit::findOrFail($request->validate(['delete_acompte_id' => ['required', 'integer']])['delete_acompte_id']);
        $reservation = $this->reservation($group, $deposit->reservation_id);

        DB::transaction(function () use ($deposit, $reservation) {
            $deposit->delete();
            $this->record($reservation, ReservationHistoryEnum::delete_payment, $deposit->n_acompte . ', montant ' . $deposit->montant . ' €');
        });

        return back()->with('success', 'Paiement supprimé.');
    }

    private function reservation(FlightGroup $group, int $id): FlightReservation
    {
        return FlightReservation::whereHas('vol', fn ($q) => $q->where('vol_group_id', $group->id))->findOrFail($id);
    }

    private function record(FlightReservation $reservation, ReservationHistoryEnum $type, string $details): void
    {
        FlightReservationHistory::create([
            'type' => $type,
            'details' => $details,
            'agent_id' => Auth::guard('gent')->id(),
            'reservation_id' => $reservation->id,
        ]);
        $reservation->refreshTotals();
    }
}
