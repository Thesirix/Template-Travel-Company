<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	protected $fillable = [
        'nom',
        'country_id',
        'coleur',
    ];
	protected $table="villes";

    public function airPorts()
    {
        return $this->hasMany(Airport::class, 'ville_id');
    }


    public function country()
	{
		return $this->belongsTo('App\Models\Country');
	}
}
