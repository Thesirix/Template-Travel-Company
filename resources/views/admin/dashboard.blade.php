@extends('layouts.admin')
@section('title', 'Tableau de bord · ' . $brand->name())

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="tc-pagehead">
            <div>
                <h1>Tableau de bord</h1>
                <p>{{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }} · {{ $stats['vols'] }} vols en vente, {{ number_format($stats['seats'], 0, ',', ' ') }} places disponibles</p>
            </div>
            <div class="tc-pagehead__actions">
                <a href="{{ route('admin.destinations') }}" class="btn btn-secondary">Destinations</a>
                @if($destinations->first())
                    <a href="{{ route('admin.vols.create', $destinations->first()->slug) }}" class="btn btn-primary"><i class="fa-solid fa-plus mr-1"></i> Nouveau vol</a>
                @endif
            </div>
        </div>

        <div class="tc-stats mb-4">
            @foreach([
                ['Vols en vente', $stats['vols'], 'fa-plane-departure', null],
                ['Places disponibles', $stats['seats'], 'fa-chair', null],
                ['Réservations', $stats['reservations'], 'fa-ticket', null],
                ['Chiffre d\'affaires', round($stats['revenue']), 'fa-euro-sign', ' €'],
            ] as [$label, $value, $icon, $suffix])
                <div class="tc-stat">
                    <div class="tc-stat__icon"><i class="fa-solid {{ $icon }}"></i></div>
                    <div class="tc-stat__value" data-tc-count="{{ $value }}" data-tc-suffix="{{ $suffix }}">0</div>
                    <div class="tc-stat__label">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="m-0">Réservations sur 6 mois</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area"><canvas id="tcChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 mb-4">
                <div class="card h-100">
                    <div class="card-header"><h6 class="m-0">Remplissage par destination</h6></div>
                    <div class="card-body">
                        @forelse($destinations as $destination)
                            @php
                                $d = $byDestination[$destination->slug] ?? null;
                                $total = (int) ($d->total ?? 0);
                                $sold = $total - (int) ($d->libres ?? 0);
                                $pct = $total ? round($sold / $total * 100) : 0;
                            @endphp
                            <div class="tc-fill">
                                <div class="tc-fill__head">
                                    <span><span class="tc-dot" style="background: {{ $destination->color }}"></span> {{ $destination->name }}</span>
                                    <strong>{{ $pct }} %</strong>
                                </div>
                                <div class="tc-fill__bar"><i style="--w: {{ $pct }}%; background: {{ $destination->color }}"></i></div>
                                <small>{{ $sold }} / {{ $total }} places · {{ $d->vols ?? 0 }} vol{{ ($d->vols ?? 0) > 1 ? 's' : '' }}</small>
                            </div>
                        @empty
                            <div class="tc-empty"><i class="fa-solid fa-earth-africa"></i>Aucune destination</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="card">
                    <div class="card-header"><h6 class="m-0">Prochains départs</h6></div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Départ</th><th>Itinéraire</th><th>Compagnie</th><th>Remplissage</th><th class="text-right">Prix adulte</th></tr></thead>
                            <tbody>
                            @forelse($nextFlights as $flight)
                                @php($fill = $flight->place_total ? round(($flight->place_total - $flight->place_disponible) / $flight->place_total * 100) : 0)
                                <tr>
                                    <td>
                                        <div class="tc-date">
                                            <strong>{{ \Carbon\Carbon::parse($flight->date_aller)->format('d') }}</strong>
                                            <span>{{ \Carbon\Carbon::parse($flight->date_aller)->locale('fr')->isoFormat('MMM') }}</span>
                                        </div>
                                    </td>
                                    <td><strong>{{ $flight->itineraire }}</strong><br><small class="text-gray-500">{{ ucfirst($flight->destination) }} · {{ $flight->period_voyage }}</small></td>
                                    <td>{{ $flight->company->nom ?? '-' }}</td>
                                    <td style="min-width: 140px">
                                        <div class="tc-fill__bar"><i style="--w: {{ $fill }}%"></i></div>
                                        <small class="text-gray-500">{{ $flight->place_disponible }} places libres</small>
                                    </td>
                                    <td class="text-right font-weight-bold">{{ number_format($flight->prix_vente_adult, 0, ',', ' ') }} €</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="tc-empty"><i class="fa-solid fa-plane-slash"></i>Aucun départ à venir</div></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 mb-4">
                <div class="card mb-4">
                    <div class="card-header"><h6 class="m-0">Équipe</h6></div>
                    <div class="card-body tc-mini-stats">
                        <a href="{{ route('admin.agents') }}"><i class="fa-solid fa-user-tie"></i><strong>{{ $stats['agents'] }}</strong><span>agents</span></a>
                        <a href="{{ route('admin.agences') }}"><i class="fa-solid fa-building"></i><strong>{{ $stats['agencies'] }}</strong><span>agences</span></a>
                        <a href="{{ route('admin.clients') }}"><i class="fa-solid fa-users"></i><strong>{{ $stats['clients'] }}</strong><span>clients</span></a>
                    </div>
                </div>
                <div class="card tc-callout">
                    <div class="card-body">
                        <i class="fa-solid fa-umbrella-beach"></i>
                        <div>
                            <strong>{{ $stats['pendingLeaves'] }} demande{{ $stats['pendingLeaves'] > 1 ? 's' : '' }} de congé en attente</strong>
                            <p class="mb-0 text-gray-500 small">Les super agents les valident depuis le planning de l'équipe.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .tc-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .tc-fill { margin-bottom: 16px; }
        .tc-fill:last-child { margin-bottom: 0; }
        .tc-fill__head { display: flex; justify-content: space-between; align-items: center; font-weight: 500; margin-bottom: 6px; }
        .tc-fill__head span { display: inline-flex; align-items: center; gap: 8px; }
        .tc-fill__bar { height: 6px; border-radius: 99px; background: var(--tc-hover); overflow: hidden; margin-bottom: 4px; }
        .tc-fill__bar i { display: block; height: 100%; width: var(--w); border-radius: inherit; background: var(--tc-primary); }
        .tc-fill small { color: var(--tc-muted); }
        .tc-date { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .tc-date strong { font-weight: 600; }
        .tc-date span { color: var(--tc-muted); margin-left: 4px; }
        .tc-mini-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .tc-mini-stats a { padding: 12px; border-radius: var(--tc-radius); border: 1px solid var(--tc-border); color: var(--tc-text); }
        .tc-mini-stats a:hover { background: var(--tc-surface-2); }
        .tc-mini-stats i { display: none; }
        .tc-mini-stats strong { display: block; font-size: 1.25rem; font-weight: 600; }
        .tc-mini-stats span { font-size: .78rem; color: var(--tc-muted); }
        .tc-callout .card-body { display: flex; gap: 12px; align-items: flex-start; }
        .tc-callout i { color: var(--tc-warning); margin-top: 3px; }
    </style>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('tcChart');
            if (!canvas || !window.Chart) return;
            const css = getComputedStyle(document.documentElement);
            const primary = css.getPropertyValue('--tc-primary').trim();
            const rgb = css.getPropertyValue('--tc-primary-rgb').trim();
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chart['labels']),
                    datasets: [{
                        label: 'Réservations', data: @json($chart['reservations']),
                        borderColor: primary, backgroundColor: `rgba(${rgb}, .06)`, fill: true, tension: .25, borderWidth: 2,
                        pointRadius: 0, pointHoverRadius: 4, pointBackgroundColor: primary,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#18181b', padding: 8, cornerRadius: 6, displayColors: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#71717a' }, border: { color: '#e4e4e7' } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: '#71717a' }, grid: { color: '#f4f4f5' }, border: { display: false } },
                    },
                },
            });
        });
    </script>
@endpush
