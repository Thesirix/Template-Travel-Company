<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Relâche d'un allotement : qui l'a relâchée et quand.
 */
class BlockPowerRelease extends Model
{
    protected $table = 'block_power_releases';

    protected $fillable = ['option_id', 'agent_id'];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
