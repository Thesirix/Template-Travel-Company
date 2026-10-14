<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Gabarit du véhicule embarqué.
 */
class BoatVehicleSize extends Model
{
    protected $table = 'boat_vehicle_sizes';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
