@props(['agenciesStatistics'])
@php($max = max(1, collect($agenciesStatistics)->max('totalNumber')))
<div class="card h-100">
    <div class="card-header"><h6 class="m-0">Ventes par agence</h6></div>
    <table class="table tc-rank">
        <thead><tr><th>Agence</th><th class="text-right">Passagers</th></tr></thead>
        <tbody>
        @forelse($agenciesStatistics as $agencyStatistics)
            <tr>
                <td>
                    <div class="font-weight-bold">{{ $agencyStatistics->agencyName }}</div>
                    <div class="tc-rank__bar"><i style="width: {{ round($agencyStatistics->totalNumber / $max * 100) }}%"></i></div>
                </td>
                <td class="text-right">
                    <div class="font-weight-bold">{{ $agencyStatistics->totalNumber }}</div>
                    <div class="small text-gray-500">{{ $agencyStatistics->numAdults }} ad. · {{ $agencyStatistics->numChildren }} enf. · {{ $agencyStatistics->numBabies }} bb</div>
                </td>
            </tr>
        @empty
            <tr><td colspan="2" class="tc-empty">Aucune vente</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
