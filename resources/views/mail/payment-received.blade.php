<div style="font-family: Arial, Helvetica, sans-serif">
    <h2>Paiement reçu</h2>
    <p>Client : {{ $payment->fullname }}</p>
    <p>Adresse du titulaire de la carte : {{ trim($payment->adresse . ' ' . $payment->zip . ' ' . $payment->ville) }}</p>
    <p>E-mail : {{ $payment->email }}</p>
    <p>Téléphone : {{ $payment->telephone }}</p>
    <p>Montant : {{ number_format((float) $payment->price, 2, ',', ' ') }} €</p>
    @if($payment->text)
        <p>Libellé : {{ $payment->text }}</p>
    @endif
</div>
