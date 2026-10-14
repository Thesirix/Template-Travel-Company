<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment_historiques';

    public $fillable=['id','user_id','fullname','adresse','ville','zip','telephone','email','price','text','created_at'];
    public function user(){
        return $this->belongsTo(Agent::class,'user_id');
    }
}
