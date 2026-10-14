@props(['destinationsStatistics'])
<div class="card h-100">
    <div class="card-header"><h6 class="m-0">Remplissage par ville</h6></div>
    <div class="card-body">
        @forelse($destinationsStatistics as $stat)
            <div class="tc-fillcity">
                <div class="tc-fillcity__head">
                    <strong>{{ $stat->cityName }}</strong>
                    <span>{{ $stat->globalReservations }} / {{ $stat->globalTotal }} places vendues</span>
                </div>
                @foreach([['Aller', $stat->allerReservations, $stat->allerTotal, $stat->allerReservationsRate], ['Retour', $stat->returnReservations, $stat->returnTotal, $stat->returnReservationsRate]] as [$label, $sold, $total, $rate])
                    <div class="tc-fillcity__row">
                        <span class="tc-fillcity__label">{{ $label }}</span>
                        <div class="tc-rank__bar flex-grow-1 m-0"><i style="width: {{ min(100, (float) $rate) }}%"></i></div>
                        <span class="tc-fillcity__value">{{ $sold }} / {{ $total }}</span>
                        <span class="tc-fillcity__rate">{{ rtrim(rtrim(number_format((float) $rate, 1, ',', ''), '0'), ',') }} %</span>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="tc-empty">Aucun vol</div>
        @endforelse
    </div>
</div>
