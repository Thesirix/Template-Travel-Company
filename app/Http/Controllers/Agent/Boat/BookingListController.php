<?php

namespace App\Http\Controllers\Agent\Boat;

use App\Exports\BoatBookingsExport;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\BoatBooking;
use App\Models\BoatCompany;
use App\Models\BoatDeparturePort;
use App\Models\BoatDestination;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Listes des traversées par statut (à traiter, confirmées, vendues, bons d'échange), filtres et export Excel.
 */
class BookingListController extends Controller
{
    public function index(Request $request, string $status)
    {
        return view('agent.bateau.index', [
            'status' => $status,
            'title' => BoatBooking::STATUSES[$status],
            'bookings' => $this->bookings($request, $status),
            'destinations' => BoatDestination::orderBy('name')->get(),
            'companies' => BoatCompany::orderBy('name')->get(),
            'departurePorts' => BoatDeparturePort::orderBy('name')->get(),
            'agents' => Agent::orderBy('name')->get(),
            'filters' => $request->only(self::FILTERS),
        ]);
    }

    public function export(Request $request)
    {
        $status = $request->validate(['statut' => ['nullable', 'in:' . implode(',', array_keys(BoatBooking::STATUSES))]])['statut'] ?? 'pending';

        return Excel::download(new BoatBookingsExport($this->bookings($request, $status), $status), 'bateau-' . $status . '-' . now()->format('Y-m-d') . '.xlsx');
    }

    private const FILTERS = ['destination', 'compagnie', 'port', 'nom', 'date_aller', 'date_retour', 'agent'];

    private function bookings(Request $request, string $status)
    {
        return BoatBooking::inStatus($status)
            ->with(['reservation', 'accommodation', 'destination', 'departurePort', 'arrivalPort', 'agent', 'company', 'passengers'])
            ->when($request->filled('destination'), fn ($q) => $q->where('destination_id', $request->destination))
            ->when($request->filled('compagnie'), fn ($q) => $q->where('company_id', $request->compagnie))
            ->when($request->filled('port'), fn ($q) => $q->where('departure_port_id', $request->port))
            ->when($request->filled('nom'), fn ($q) => $q->whereHas('passengers', fn ($p) => $p->where('nom', 'like', "%{$request->nom}%")))
            ->when($request->filled('date_aller'), fn ($q) => $q->whereDate('date_aller', $request->date_aller))
            ->when($request->filled('date_retour'), fn ($q) => $q->whereDate('date_retour', $request->date_retour))
            ->when($request->filled('agent'), fn ($q) => $q->where('gent_id', $request->agent))
            ->orderByDesc('created_at')
            ->get();
    }
}
