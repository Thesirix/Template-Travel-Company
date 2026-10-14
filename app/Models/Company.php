<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImage;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasPublicImage;

    public function country()
	{
		return $this->belongsTo('App\Models\Country');
	}

	public function vols()
    {
        return $this->hasMany('App\Models\Flight');
    }
}
