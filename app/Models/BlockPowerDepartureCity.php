<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ville de départ proposée pour les allotements.
 */
class BlockPowerDepartureCity extends Model
{
    protected $table = 'block_power_departure_cities';

    protected $fillable = ['name'];
}
