<?php

namespace App\Enums;

enum GroupType: string
{
    case ROUND_TRIP = 'round-trip';
    case MULTI_SEGMENT_OPTIONAL = 'multi-segment-optional';
    case MULTI_SEGMENT_REQUIRED = 'multi-segment-required';
}
