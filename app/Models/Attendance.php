<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
   
    public $timestamps = false;
    protected $fillable = [
        'gent_id',
        'date',
        'heure_arrivee',
        'ip_adresse',
        'mac_adresse',
    ];

    public function gent()
    {
        return $this->belongsTo(\App\Models\Agent::class, 'gent_id');
    }
}
