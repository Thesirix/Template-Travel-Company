<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Type de véhicule embarqué.
 */
class BoatVehicle extends Model
{
    protected $table = 'boat_vehicles';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
