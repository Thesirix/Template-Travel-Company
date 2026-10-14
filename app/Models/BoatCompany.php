<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compagnie maritime d'une destination bateau.
 */
class BoatCompany extends Model
{
    protected $table = 'boat_companies';

    protected $fillable = ['name', 'destination_id'];

    public const UPDATED_AT = null;

    public function destination()
    {
        return $this->belongsTo(BoatDestination::class, 'destination_id');
    }
}
