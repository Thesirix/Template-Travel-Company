<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Réservation d'une demande bateau auprès de la compagnie (numéro, date d'option, prix).
 * etat : « confirmer » (réservée), « final » (vendue) ou « echange » (bon d'échange).
 */
class BoatReservation extends Model
{
    protected $table = 'boat_reservations';

    protected $fillable = ['etat', 'booking_id', 'num_reservation', 'date_option', 'prix', 'details'];

    public function booking()
    {
        return $this->belongsTo(BoatBooking::class, 'booking_id');
    }
}
