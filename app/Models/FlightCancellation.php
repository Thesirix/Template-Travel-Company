<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightCancellation extends Model
{
    protected $table = 'vol_reservations_annules';

    protected $fillable=['id','reservation_id','montant','reason','created_at'];
    public $timestamps=false;
}
