<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Passager confirmé lors de la confirmation d'une option (nouveau PNR / nouvelle date limite).
 */
class BlockPowerConfirmation extends Model
{
    protected $table = 'block_power_confirmations';

    protected $fillable = ['agent_id', 'passenger_id'];

    public function passenger()
    {
        return $this->belongsTo(BlockPowerPassenger::class, 'passenger_id');
    }
}
