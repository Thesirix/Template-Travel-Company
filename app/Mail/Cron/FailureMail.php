<?php

namespace App\Mail\Cron;

use App\Mail\AgencyMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Échec d'une tâche planifiée, envoyé à la boîte « alertes ».
 */
class FailureMail extends AgencyMail
{
    public function __construct(public string $task, public string $error)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(from: $this->sender(), subject: 'Échec de tâche planifiée : ' . $this->task);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.cron.failure');
    }
}
