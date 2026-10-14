<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    public $fillable=['email','otp','expiration_date','nb_attempts','type','created_at'];

    public $timestamps = false;
}
