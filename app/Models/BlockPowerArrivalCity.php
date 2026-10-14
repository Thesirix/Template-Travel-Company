<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ville d'arrivée proposée pour les allotements.
 */
class BlockPowerArrivalCity extends Model
{
    protected $table = 'block_power_arrival_cities';

    protected $fillable = ['name'];
}
