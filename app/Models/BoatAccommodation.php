<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Installation à bord (fauteuil, cabine...).
 */
class BoatAccommodation extends Model
{
    protected $table = 'boat_accommodations';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
