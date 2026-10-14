<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de départ d'une destination bateau.
 */
class BoatDeparturePort extends Model
{
    protected $table = 'boat_departure_ports';

    protected $fillable = ['name', 'destination_id'];

    public const UPDATED_AT = null;

    public function destination()
    {
        return $this->belongsTo(BoatDestination::class, 'destination_id');
    }
}
