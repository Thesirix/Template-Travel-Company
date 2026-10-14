<?php

namespace App\Enums;

enum FlightType: string
{
    case ROUND_TRIP = 'aller_retour';
    case OUTBOUND = 'aller_simple';
    case RETURN = 'retour_simple';
}
