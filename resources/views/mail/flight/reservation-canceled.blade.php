@php($lead = $reservation->leadPassenger())
<div style="font-family: Arial, sans-serif">
    <h2 style="color: #dc2626">Réservation annulée</h2>
    <p>Bonjour{{ $lead ? ' ' . ($lead->genre === 'male' ? 'M.' : 'Mme') . ' ' . $lead->prenom . ' ' . $lead->nom : '' }},</p>
    <p>
        Votre inscription a été annulée pour le vol {{ $reservation->routeLabel() }}
        @if($reservation->outboundDate()), départ le {{ $reservation->outboundDate()->format('d/m/Y') }}@endif
        @if($reservation->returnDate()), retour le {{ $reservation->returnDate()->format('d/m/Y') }}@endif
        ({{ $passengers }} passager{{ $passengers > 1 ? 's' : '' }}).
    </p>
    <p>Cordialement,</p>
    <p>{{ $brand->name() }}</p>
</div>
