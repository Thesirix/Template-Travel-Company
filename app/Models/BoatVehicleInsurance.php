<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Assurance du véhicule embarqué.
 */
class BoatVehicleInsurance extends Model
{
    protected $table = 'boat_vehicle_insurances';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
