<?php

namespace App\Mail\Flight;

use App\Mail\AgencyMail;
use App\Models\FlightReservation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Annulation d'une réservation de vol ou d'un de ses passagers.
 */
class ReservationCanceledMail extends AgencyMail
{
    public function __construct(public FlightReservation $reservation, public int $passengers)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(from: $this->sender(), subject: 'Annulation de réservation');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.flight.reservation-canceled');
    }
}
