<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Passager d'une traversée en bateau ; « respo » marque le responsable de la réservation.
 * ageA / ageR : âge à l'aller et au retour (les tarifs dépendent de l'âge le jour du voyage).
 */
class BoatPassenger extends Model
{
    protected $table = 'boat_passengers';

    protected $fillable = ['respo', 'booking_id', 'nom', 'prenom', 'type', 'date_naissance', 'passport', 'mail',
        'passport_num', 'ageA', 'ageR', 'tele'];

    public const UPDATED_AT = null;

    public function booking()
    {
        return $this->belongsTo(BoatBooking::class, 'booking_id');
    }

    public function passportUrl(): ?string
    {
        return $this->passport ? Storage::disk('public')->url($this->passport) : null;
    }
}
