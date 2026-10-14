<?php

namespace App\Mail\Cron;

use App\Mail\AgencyMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

/**
 * Rappel d'émission des billets : vols qui partent bientôt, regroupés par destination.
 */
class IssuanceAlertMail extends AgencyMail
{
    public function __construct(public string $title, public string $intro, public Collection $groups)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(from: $this->sender(), subject: $this->title);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.cron.issuance-alert');
    }
}
