<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
	public $fillable = [
        'id',
        'nom',
        'abv',
        'ville_id',
    ];
    protected $table="air_ports";

    public function ville()
	{
		return $this->belongsTo('App\Models\City');
	}

    public function escaleDe()
    {
        return $this->hasMany(FlightStop::class,'air_port_id','id');
    }
    public function escaleA()
    {
        return $this->hasMany(FlightStop::class,'re_air_port_id','id');
    }
}
