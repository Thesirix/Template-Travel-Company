<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;
    protected $table="devis";

    protected $fillable=["departure_city","arrival_city","departure_date","return_date",
        "passengers_nb","first_name","last_name","phone_num","email"];


}
