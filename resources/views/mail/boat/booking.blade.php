<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <style>
        body, td { font-family: Arial, Helvetica, sans-serif; }
        table td { padding: 6px 24px 6px 6px; }
        h2 { color: {{ $brand->primary() }}; }
    </style>
</head>
<body>
@php
    $reservation = $boat->reservation;
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '—';
    $titles = ['confirmer' => 'Traversée réservée', 'final' => 'Traversée confirmée', 'echange' => "Bon d'échange"];
@endphp
@if($brand->logoUrl())
    <img alt="{{ $brand->name() }}" src="{{ $brand->logoUrl() }}" style="display: block; width: 200px; max-width: 100%;"/>
@else
    <p style="font-size: 26px; font-weight: 800; margin: 0;">{{ $brand->name() }}</p>
@endif

<h2>{{ $confirmed && $reservation ? $titles[$reservation->etat] ?? 'Réservation' : 'Demande de traversée enregistrée' }}</h2>
<table>
    <tr><td>Destination</td><td>{{ $boat->destination->name ?? '—' }}</td></tr>
    <tr><td>Port de départ</td><td>{{ $boat->departurePort->name ?? '—' }}</td></tr>
    @if($boat->arrivalPort)
        <tr><td>Port de retour</td><td>{{ $boat->arrivalPort->name }}</td></tr>
    @endif
    <tr><td>Aller</td><td>{{ $date($boat->date_aller) }}</td></tr>
    @if($boat->date_retour)
        <tr><td>Retour</td><td>{{ $date($boat->date_retour) }}</td></tr>
    @endif
    <tr><td>Compagnie</td><td>{{ $boat->company->name ?? '—' }}</td></tr>
    <tr><td>Installation</td><td>{{ $boat->accommodation->name ?? '—' }}</td></tr>
    <tr><td>Assurance</td><td>{{ $boat->insurance->name ?? '—' }}</td></tr>
    @if($confirmed && $reservation)
        <tr><td>N° de réservation</td><td>{{ $reservation->num_reservation }}</td></tr>
        <tr><td>Date d'option</td><td>{{ $date($reservation->date_option) }}</td></tr>
        <tr><td>Prix</td><td>{{ number_format((float) $reservation->prix, 2, ',', ' ') }} €</td></tr>
    @endif
</table>

<h2>Passagers</h2>
<table>
    <tr><td>Type</td><td>Nom</td><td>Prénom</td><td>Naissance</td><td>N° passeport</td></tr>
    @foreach($boat->passengers as $passenger)
        <tr>
            <td>{{ $passenger->type }}</td>
            <td>{{ $passenger->nom }}</td>
            <td>{{ $passenger->prenom }}</td>
            <td>{{ $date($passenger->date_naissance) }}</td>
            <td>{{ $passenger->passport_num }}</td>
        </tr>
    @endforeach
</table>

@if($boat->hasVehicle())
    <h2>Véhicule</h2>
    <table>
        <tr><td>Type</td><td>{{ $boat->vehicle->name ?? '—' }}</td></tr>
        <tr><td>Marque / modèle</td><td>{{ $boat->voiture_marque }} {{ $boat->voiture_modele }}</td></tr>
        <tr><td>Immatriculation</td><td>{{ $boat->voiture_matricule }}</td></tr>
        <tr><td>Gabarit</td><td>{{ $boat->vehicleSize->name ?? '—' }}</td></tr>
        <tr><td>Assurance</td><td>{{ $boat->vehicleInsurance->name ?? '—' }}</td></tr>
    </table>
@endif

<p>Cordialement,<br>{{ $brand->name() }}</p>
</body>
</html>
