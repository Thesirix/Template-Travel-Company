<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Paiement en ligne accepté par Paybox, envoyé à la boîte « paiements » de l'agence.
 */
class PaymentReceivedMail extends AgencyMail
{
    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(from: $this->sender(), subject: 'Paiement reçu');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payment-received');
    }
}
