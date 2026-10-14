@props(['agentsStatistics'])
@php($max = max(1, collect($agentsStatistics->agents)->max('totalNumber')))
<div class="card h-100">
    <div class="card-header"><h6 class="m-0">Meilleurs vendeurs</h6></div>
    <table class="table tc-rank">
        <thead><tr><th>Agent</th><th class="text-right">Passagers</th></tr></thead>
        <tbody>
        @forelse($agentsStatistics->agents as $agentStatistics)
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="tc-rank__pos">{{ $loop->iteration }}</span>
                        <span class="font-weight-bold">{{ $agentStatistics->agentName }}</span>
                    </div>
                    <div class="tc-rank__bar"><i style="width: {{ round($agentStatistics->totalNumber / $max * 100) }}%"></i></div>
                </td>
                <td class="text-right">
                    <div class="font-weight-bold">{{ $agentStatistics->totalNumber }}</div>
                    <div class="small text-gray-500">{{ $agentStatistics->numAdults }} ad. · {{ $agentStatistics->numChildren }} enf. · {{ $agentStatistics->numBabies }} bb</div>
                </td>
            </tr>
        @empty
            <tr><td colspan="2" class="tc-empty">Aucune vente</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <td>Total</td>
            <td class="text-right">
                <div>{{ $agentsStatistics->totalReservations }}</div>
                <div class="small text-gray-500 font-weight-normal">{{ $agentsStatistics->totalReservedAdults }} ad. · {{ $agentsStatistics->totalReservedChildren }} enf. · {{ $agentsStatistics->totalReservedBabies }} bb</div>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
