<div style="font-family: Arial, Helvetica, sans-serif">
    <h2>{{ $intro }}</h2>
    @foreach($groups as $group)
        <p><strong>{{ $group->name }}</strong></p>
        <ul>
            @foreach($group->flights as $flight)
                <li>
                    PNR {{ $flight->pnr ?: '-' }}, aller le {{ \Illuminate\Support\Carbon::parse($flight->date_aller)->format('d/m/Y') }}
                    @if($flight->date_retour), retour le {{ \Illuminate\Support\Carbon::parse($flight->date_retour)->format('d/m/Y') }}@endif
                </li>
            @endforeach
        </ul>
    @endforeach
</div>
