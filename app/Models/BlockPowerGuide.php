<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Guide des allotements (un seul fichier, disque public, dossier « guide »).
 */
class BlockPowerGuide extends Model
{
    protected $table = 'block_power_guides';

    protected $fillable = ['name'];

    public const UPDATED_AT = null;
}
