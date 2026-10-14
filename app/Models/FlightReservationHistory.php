<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Action d'un agent sur une réservation de vol (voir App\Enums\ReservationHistoryEnum).
 */
class FlightReservationHistory extends Model
{
    use HasFactory;

    protected $table = 'vol_reservation_histories';
    public $timestamps = false;

    protected $fillable = ['type', 'details', 'agent_id', 'reservation_id', 'created_at'];

    public function gent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function reservation()
    {
        return $this->belongsTo(FlightReservation::class, 'reservation_id');
    }
}
