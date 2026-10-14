<div class="tc-perf">
    <div class="tc-stats mb-4">
        @foreach([
            ['Mes réservations du mois', $stats['reservationsMonth'], 'fa-ticket', ''],
            ['Mon chiffre du mois', round($stats['revenueMonth']), 'fa-euro-sign', ' €'],
            ['Mes clients', $stats['clients'], 'fa-users', ''],
            ['Mes demandes clients', $stats['requests'], 'fa-inbox', ''],
        ] as [$label, $value, $icon, $suffix])
            <div class="tc-stat">
                <div class="tc-stat__icon"><i class="fa-solid {{ $icon }}"></i></div>
                <div class="tc-stat__value" data-tc-count="{{ $value }}" data-tc-suffix="{{ $suffix }}">0</div>
                <div class="tc-stat__label">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-xl-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0">Mes ventes sur 6 mois</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-light active" data-perf="reservations">Réservations</button>
                        <button type="button" class="btn btn-light" data-perf="amounts">Montant</button>
                    </div>
                </div>
                <div class="card-body"><div class="chart-area" style="height: 260px"><canvas id="perfChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-5 mb-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="m-0">Prochains départs</h6></div>
                <div class="card-body p-0">
                    @forelse($nextFlights as $flight)
                        @php($fill = $flight->place_total ? round(($flight->place_total - $flight->place_disponible) / $flight->place_total * 100) : 0)
                        <a class="tc-flight" href="{{ route('agent.vols.vente', $flight->vol_group_id) }}">
                            <span class="tc-flight__date">
                                <strong>{{ \Carbon\Carbon::parse($flight->date_aller)->format('d') }}</strong>
                                {{ \Carbon\Carbon::parse($flight->date_aller)->locale('fr')->isoFormat('MMM') }}
                            </span>
                            <span class="tc-flight__info">
                                <strong>{{ $flight->itineraire }}</strong>
                                <small>{{ $flight->company->nom ?? '' }} · {{ $flight->place_disponible }} places libres</small>
                                <span class="tc-flight__bar"><i style="width: {{ $fill }}%"></i></span>
                            </span>
                            <span class="tc-flight__price">{{ number_format($flight->prix_vente_adult, 0, ',', ' ') }} €</span>
                        </a>
                    @empty
                        <div class="tc-empty"><i class="fa-solid fa-plane-slash"></i>Aucun départ à venir</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tc-perf .tc-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
    .tc-flight { display: flex; align-items: center; gap: 14px; padding: 12px 16px; border-bottom: 1px solid var(--tc-border); color: var(--tc-text) !important; }
    .tc-flight:last-child { border-bottom: 0; }
    .tc-flight:hover { background: var(--tc-surface-2); }
    .tc-flight__date { width: 44px; flex-shrink: 0; text-align: center; font-size: .7rem; color: var(--tc-muted); text-transform: uppercase; line-height: 1.2; }
    .tc-flight__date strong { display: block; font-size: 1.05rem; font-weight: 600; color: var(--tc-text); }
    .tc-flight__info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
    .tc-flight__info small { color: var(--tc-muted); }
    .tc-flight__bar { height: 4px; border-radius: 99px; background: var(--tc-hover); overflow: hidden; }
    .tc-flight__bar i { display: block; height: 100%; border-radius: inherit; background: var(--tc-primary); }
    .tc-flight__price { font-weight: 600; white-space: nowrap; }
</style>

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('perfChart');
            if (!canvas || !window.Chart) return;
            const css = getComputedStyle(document.documentElement);
            const primary = css.getPropertyValue('--tc-primary').trim();
            const accent = css.getPropertyValue('--tc-accent').trim();
            const series = { reservations: @json($chart['reservations']), amounts: @json($chart['amounts']) };
            const chart = new Chart(canvas, {
                type: 'bar',
                data: { labels: @json($chart['labels']), datasets: [{ data: series.reservations, backgroundColor: primary, hoverBackgroundColor: primary, borderRadius: 3, maxBarThickness: 36 }] },
                options: {
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#18181b', padding: 8, cornerRadius: 6, displayColors: false } },
                    scales: { x: { grid: { display: false }, ticks: { color: '#71717a' } }, y: { beginAtZero: true, ticks: { precision: 0, color: '#71717a' }, grid: { color: '#f4f4f5' }, border: { display: false } } },
                },
            });
            document.querySelectorAll('[data-perf]').forEach((btn) => btn.addEventListener('click', () => {
                document.querySelectorAll('[data-perf]').forEach((b) => b.classList.toggle('active', b === btn));
                chart.data.datasets[0].data = series[btn.dataset.perf];
                chart.update();
            }));
        });
    </script>
@endpush
