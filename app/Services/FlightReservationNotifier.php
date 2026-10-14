<?php

namespace App\Services;

use App\Enums\SmsFlightReservationTypes;
use App\Mail\Flight\ReservationCanceledMail;
use App\Mail\Flight\ReservationConfirmedMail;
use App\Models\FlightReservation;
use App\Support\Branding;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * E-mails et SMS envoyés autour d'une réservation de vol.
 * Une notification qui échoue est journalisée sans jamais annuler l'opération qui l'a déclenchée.
 */
class FlightReservationNotifier
{
    private const RELATIONS = ['gent', 'passagers', 'vol.company', 'vol.volGroup.country', 'vol.escaliers.airPortDe.ville',
        'vol.escaliers.airPortA.ville', 'volRetour.escaliers'];

    public function __construct(private SmsService $sms, private Branding $brand)
    {
    }

    /** Contrat envoyé à l'agence et aux passagers qui ont un e-mail */
    public function confirmed(FlightReservation|int $reservation, string $subject): void
    {
        $this->attempt('e-mail de confirmation', function () use ($reservation, $subject) {
            $reservation = $this->fresh($reservation);
            $this->mail(new ReservationConfirmedMail($reservation, $subject), $reservation);
        });
    }

    /** SMS au premier adulte joignable de chaque réservation */
    public function confirmedSms(FlightReservation|int $reservation, SmsFlightReservationTypes $type): void
    {
        $this->attempt('SMS de confirmation', function () use ($reservation, $type) {
            $reservation = $this->fresh($reservation);
            foreach ($reservation->passagers->where('type', 'adulte') as $passenger) {
                $phones = array_filter([$passenger->tele1, $passenger->tele2]);
                if (! $phones) {
                    continue;
                }
                $message = $this->greeting($passenger) . ' Votre ' . $type->value . ' est bien confirmée pour le vol '
                    . $reservation->routeLabel() . ' avec ' . ($reservation->vol->company->nom ?? 'la compagnie')
                    . ($reservation->outboundDate() ? ', départ le ' . $reservation->outboundDate()->format('d/m/Y') : '')
                    . ($reservation->returnDate() ? ', retour le ' . $reservation->returnDate()->format('d/m/Y') : '')
                    . '. Merci de respecter les conditions du contrat et de régler 45 jours avant le départ. Cordialement, ' . $this->brand->name();
                $this->sms->sendSms($phones, $message);
            }
        });
    }

    /** Annulation de toute la réservation ou d'un passager ($passengers = nombre de passagers annulés) */
    public function canceled(FlightReservation|int $reservation, int $passengers): void
    {
        $this->attempt("e-mail d'annulation", function () use ($reservation, $passengers) {
            $reservation = $this->fresh($reservation);
            $this->mail(new ReservationCanceledMail($reservation, $passengers), $reservation);
        });
    }

    private function mail($mailable, FlightReservation $reservation): void
    {
        if ($agency = config('agency.notifications.reservation_emails')) {
            Mail::to($agency)->send(clone $mailable);
        }
        $clients = $reservation->passagers->pluck('email')->filter()->unique()->values()->all();
        if ($clients) {
            Mail::to($clients)->send($mailable);
        }
    }

    private function greeting($passenger): string
    {
        return 'Bonjour ' . ($passenger->genre === 'male' ? 'M. ' : 'Mme ') . $passenger->prenom . ' ' . $passenger->nom . '.';
    }

    private function fresh(FlightReservation|int $reservation): FlightReservation
    {
        return FlightReservation::with(self::RELATIONS)->findOrFail($reservation instanceof FlightReservation ? $reservation->id : $reservation);
    }

    private function attempt(string $what, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            Log::error("Réservation de vol : $what non envoyé", ['exception' => $exception]);
        }
    }
}
