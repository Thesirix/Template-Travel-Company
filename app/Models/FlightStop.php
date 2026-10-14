<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightStop extends Model
{
    protected $table = 'vol_escaliers';

    protected $guarded = ['id'];

    public function airPortDe()
	{
		return $this->belongsTo('App\Models\Airport','air_port_id','id');
	}
	public function airPortA()
	{
		return $this->belongsTo('App\Models\Airport','re_air_port_id','id');
	}
	public function vol()
	{
		return $this->belongsTo('App\Models\Flight');
	}
}
