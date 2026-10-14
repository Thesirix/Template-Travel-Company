<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tarif bateau (standard, flexible...).
 */
class BoatFare extends Model
{
    protected $table = 'boat_fares';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
