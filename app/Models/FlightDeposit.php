<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightDeposit extends Model
{
    protected $table = 'vol_acomptes';

    protected $fillable=['id','reservation_id','montant','n_acompte','observation','agent_id','created_at'];
    public $timestamps=false;

    public function reservation(){
        return $this->belongsTo(FlightReservation::class,'reservation_id','id');
    }
}
