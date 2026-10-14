<?php

namespace App\View\Components\Agent\DashBoard;

use App\Models\Client;
use App\Models\ClientRequest;
use App\Models\Flight;
use App\Models\FlightReservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

/**
 * Tableau de bord de l'agent : chiffres du mois, ventes des 6 derniers mois, prochains départs.
 */
class Performance extends Component
{
    public array $stats;

    public array $chart;

    public $nextFlights;

    public function __construct()
    {
        $agentId = Auth::guard('gent')->id();
        $sales = FlightReservation::where('gent_id', $agentId)->where('status', true)
            ->where('created_at', '>=', today()->startOfMonth()->subMonths(5))
            ->get(['created_at', 'prix_total']);
        $thisMonth = $sales->filter(fn ($sale) => Carbon::parse($sale->created_at)->isSameMonth(today()));

        $this->stats = [
            'reservationsMonth' => $thisMonth->count(),
            'revenueMonth' => (float) $thisMonth->sum('prix_total'),
            'clients' => Client::where('gent_id', $agentId)->count(),
            'requests' => ClientRequest::where('gent_id', $agentId)->count(),
        ];

        $months = collect(range(5, 0))->map(fn ($ago) => today()->startOfMonth()->subMonths($ago));
        $byMonth = $sales->groupBy(fn ($sale) => Carbon::parse($sale->created_at)->format('Y-m'));
        $this->chart = [
            'labels' => $months->map(fn ($month) => ucfirst($month->locale('fr')->isoFormat('MMM')))->all(),
            'reservations' => $months->map(fn ($month) => ($byMonth[$month->format('Y-m')] ?? collect())->count())->all(),
            'amounts' => $months->map(fn ($month) => round((float) ($byMonth[$month->format('Y-m')] ?? collect())->sum('prix_total')))->all(),
        ];

        $this->nextFlights = Flight::with('company')->where('is_archive', false)
            ->whereDate('date_aller', '>=', today())->orderBy('date_aller')->limit(5)->get();
    }

    public function render()
    {
        return view('components.agent.DashBoard.performance');
    }
}
