<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Destination bateau (pays ou île desservis).
 */
class BoatDestination extends Model
{
    protected $table = 'boat_destinations';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;

    public function departurePorts()
    {
        return $this->hasMany(BoatDeparturePort::class, 'destination_id');
    }

    public function arrivalPorts()
    {
        return $this->hasMany(BoatArrivalPort::class, 'destination_id');
    }

    public function companies()
    {
        return $this->hasMany(BoatCompany::class, 'destination_id');
    }
}
