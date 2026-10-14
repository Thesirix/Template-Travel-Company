<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pays proposé à la création d'un allotement.
 */
class BlockPowerCountry extends Model
{
    protected $table = 'block_power_countries';

    protected $fillable = ['name'];
}
