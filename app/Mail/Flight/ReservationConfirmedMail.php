<?php

namespace App\Mail\Flight;

use App\Mail\AgencyMail;
use App\Models\FlightReservation;
use App\Models\Insurance;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Contrat d'une réservation de vol (création, modification, changement de date),
 * envoyé aux passagers et à l'agence.
 */
class ReservationConfirmedMail extends AgencyMail
{
    public function __construct(public FlightReservation $reservation, public string $title)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(from: $this->sender(), subject: $this->title);
    }

    public function content(): Content
    {
        [$durationAller, $durationRetour] = $this->reservation->durations();

        return new Content(view: 'agent.vols.contrat.contrat', with: [
            'reservation' => $this->reservation,
            'assurances' => Insurance::forCountry($this->reservation->destinationName())->get(),
            'durationAller' => $durationAller,
            'durationRetour' => $durationRetour,
        ]);
    }
}
