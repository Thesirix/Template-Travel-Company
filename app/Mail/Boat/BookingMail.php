<?php

namespace App\Mail\Boat;

use App\Mail\AgencyMail;
use App\Models\BoatBooking;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Récapitulatif d'une demande de traversée en bateau envoyé aux passagers :
 * demande enregistrée, puis réservation confirmée.
 */
class BookingMail extends AgencyMail
{
    public function __construct(public int $bookingId, public bool $confirmed = false)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(from: $this->sender(), subject: $this->confirmed ? 'Réservation bateau confirmée' : 'Demande de réservation bateau');
    }

    public function content(): Content
    {
        $boat = BoatBooking::with(['reservation', 'insurance', 'accommodation', 'destination', 'departurePort', 'arrivalPort',
            'company', 'vehicle', 'vehicleSize', 'vehicleInsurance', 'passengers'])->findOrFail($this->bookingId);

        return new Content(view: 'mail.boat.booking', with: ['boat' => $boat, 'confirmed' => $this->confirmed]);
    }
}
