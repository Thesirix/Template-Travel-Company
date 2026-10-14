<?php

namespace App\Enums;

enum PassengerType: string
{
    case ADULT = 'adulte';
    case CHILD = 'enfant';
    case BABY = 'bebe';
}
