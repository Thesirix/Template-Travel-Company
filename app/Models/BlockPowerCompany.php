<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compagnie aérienne proposée pour les allotements.
 */
class BlockPowerCompany extends Model
{
    protected $table = 'block_power_companies';

    protected $fillable = ['name'];
}
