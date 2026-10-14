<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port d'arrivée d'une destination bateau.
 */
class BoatArrivalPort extends Model
{
    protected $table = 'boat_arrival_ports';

    protected $fillable = ['name', 'destination_id'];

    public const UPDATED_AT = null;

    public function destination()
    {
        return $this->belongsTo(BoatDestination::class, 'destination_id');
    }
}
