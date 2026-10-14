<?php

namespace App\Mail;

use App\Support\Branding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

/**
 * Base des e-mails de l'application : expédiés au nom de l'agence (Identité & couleurs),
 * depuis l'adresse MAIL_FROM_ADDRESS.
 */
abstract class AgencyMail extends Mailable
{
    use Queueable, SerializesModels;

    protected function sender(): Address
    {
        return new Address(config('agency.emails.from'), app(Branding::class)->name());
    }
}
