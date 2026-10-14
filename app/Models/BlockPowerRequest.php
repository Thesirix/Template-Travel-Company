<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Demande de groupe (allotement) : besoin d'un agent (trajet, places) avec un objectif de prix et de classe.
 */
class BlockPowerRequest extends Model
{
    protected $table = 'block_power_requests';

    protected $fillable = ['agent_id', 'name', 'status', 'pnr', 'company', 'country', 'departure_city', 'arrival_city',
        'date_aller', 'date_retour', 'seats', 'cabin_class', 'price', 'option_date', 'pnr_created_on', 'target_class', 'target_price'];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
