<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Assurance voyage des passagers d'une traversée.
 */
class BoatPassengerInsurance extends Model
{
    protected $table = 'boat_passenger_insurances';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
