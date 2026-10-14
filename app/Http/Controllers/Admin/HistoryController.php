<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationHistoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\FlightReservationHistory;
use Illuminate\Http\Request;

/**
 * Journal des actions des agents sur les réservations de vols (création, paiements, modifications, annulations).
 */
class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $entries = FlightReservationHistory::with(['gent', 'reservation.vol.volGroup'])
            ->when($request->query('agent'), fn ($q, $agent) => $q->where('agent_id', $agent))
            ->when($request->query('action'), fn ($q, $action) => $q->where('type', 'like', $action . '%'))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        $agents = Agent::orderBy('name')->get(['id', 'name', 'prenom']);
        $actions = array_map(fn ($case) => trim($case->value), ReservationHistoryEnum::cases());

        return view('admin.historique.index', compact('entries', 'agents', 'actions'));
    }
}
