<?php

namespace App\Enums;

/**
 * Objet du SMS de confirmation envoyé aux passagers d'une réservation de vol.
 */
enum SmsFlightReservationTypes: string
{
    case NEW_RESERVATION = 'inscription';
}
