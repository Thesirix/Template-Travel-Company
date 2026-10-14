@php($reserved = $status !== 'pending')
<table>
    <thead>
    <tr>
        <th>Date de la demande</th>
        <th>Vendeur</th>
        <th>Destination</th>
        <th>Port de départ</th>
        <th>Port de retour</th>
        <th>Date aller</th>
        <th>Date retour</th>
        <th>Compagnie</th>
        <th>Installation</th>
        <th>Passagers</th>
        <th>Véhicule</th>
        <th>Responsable</th>
        @if($reserved)
            <th>N° de réservation</th>
            <th>Date d'option</th>
            <th>Prix</th>
        @endif
        <th>Remarque</th>
    </tr>
    </thead>
    <tbody>
    @foreach($bookings as $booking)
        @php($lead = $booking->passengers->firstWhere('respo', true) ?? $booking->passengers->first())
        <tr>
            <td>{{ $booking->created_at ? \Illuminate\Support\Carbon::parse($booking->created_at)->format('d/m/Y') : '' }}</td>
            <td>{{ trim(($booking->agent->prenom ?? '') . ' ' . ($booking->agent->name ?? '')) }}</td>
            <td>{{ $booking->destination->name ?? '' }}</td>
            <td>{{ $booking->departurePort->name ?? '' }}</td>
            <td>{{ $booking->arrivalPort->name ?? '-' }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($booking->date_aller)->format('d/m/Y') }}</td>
            <td>{{ $booking->date_retour ? \Illuminate\Support\Carbon::parse($booking->date_retour)->format('d/m/Y') : '-' }}</td>
            <td>{{ $booking->company->name ?? '' }}</td>
            <td>{{ $booking->accommodation->name ?? '' }}</td>
            <td>{{ $booking->passengerSummary() }}</td>
            <td>{{ $booking->hasVehicle() ? $booking->voiture_matricule : '' }}</td>
            <td>{{ $lead ? $lead->nom . ' ' . $lead->prenom : '' }}</td>
            @if($reserved)
                <td>{{ $booking->reservation->num_reservation ?? '' }}</td>
                <td>{{ $booking->reservation?->date_option ? \Illuminate\Support\Carbon::parse($booking->reservation->date_option)->format('d/m/Y') : '' }}</td>
                <td>{{ $booking->reservation->prix ?? '' }}</td>
            @endif
            <td>{{ $booking->note }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
