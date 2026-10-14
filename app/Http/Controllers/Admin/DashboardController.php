<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\LeaveRequest;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Flight;
use App\Models\FlightReservation;
use App\Support\Branding;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tableau de bord de l'administration.
 */
class DashboardController extends Controller
{


    public function index(Branding $brand)
    {
        $today = Carbon::today();
        $upcoming = Flight::with('company')
            ->where('is_archive', false)
            ->whereDate('date_aller', '>=', $today);

        $reservations = FlightReservation::where('etat', '!=', 'annuler');

        // Réservations des 6 derniers mois (pour le graphique)
        $months = collect(range(5, 0))->map(fn ($i) => $today->copy()->startOfMonth()->subMonths($i));
        $perMonth = FlightReservation::where('etat', '!=', 'annuler')
            ->where('created_at', '>=', $months->first())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, count(*) as total, sum(prix_total) as amount")
            ->groupBy('ym')->get()->keyBy('ym');

        $byDestination = Flight::where('is_archive', false)->whereDate('date_aller', '>=', $today)
            ->select('destination', DB::raw('count(*) as vols'), DB::raw('sum(place_total) as total'), DB::raw('sum(place_disponible) as libres'))
            ->groupBy('destination')->get()->keyBy('destination');

        return view('admin.dashboard', [
            'stats' => [
                'vols' => (clone $upcoming)->count(),
                'seats' => (int) (clone $upcoming)->sum('place_disponible'),
                'reservations' => (clone $reservations)->count(),
                'revenue' => (float) (clone $reservations)->sum('prix_total'),
                'agents' => Agent::count(),
                'agencies' => Agency::count(),
                'clients' => Client::count(),
                'pendingLeaves' => LeaveRequest::where('status', 'pending')->count(),
            ],
            'nextFlights' => (clone $upcoming)->orderBy('date_aller')->limit(6)->get(),
            'byDestination' => $byDestination,
            'destinations' => $brand->destinations(),
            'chart' => [
                'labels' => $months->map(fn ($m) => ucfirst($m->locale('fr')->isoFormat('MMM')))->values(),
                'reservations' => $months->map(fn ($m) => (int) ($perMonth[$m->format('Y-m')]->total ?? 0))->values(),
                'amounts' => $months->map(fn ($m) => (float) ($perMonth[$m->format('Y-m')]->amount ?? 0))->values(),
            ],
        ]);
    }
}
