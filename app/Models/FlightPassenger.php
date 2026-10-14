<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightPassenger extends Model
{
    protected $table = 'vol_passagers';

    protected $fillable = ['id', 'reservation_id', 'type', 'genre', 'nom', 'prenom', 'date_naissance',
        'passport_en_cour', 'passport', 'passport_image', 'expiration', 'sijour_en_cour', 'sijour',
        'sijour_image', 'sijour_expiration', 'tele1', 'tele2', 'tele_destination', 'email', 'assistance',
        'Observation', 'dossier_etat', 'verifier','inscrit_aller','inscrit_retour','ref_aller','ref_retour',
        'assurance_id', 'created_at'];

    public function assurences()
    {
        return $this->hasOne(Insurance::class, 'id', 'assurance_id');
    }

    public function reservation()
    {
        return $this->belongsTo(FlightReservation::class, 'reservation_id');
    }
}
