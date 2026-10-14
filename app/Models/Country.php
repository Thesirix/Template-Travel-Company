<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImage;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasPublicImage;

    //
protected $fillable = [
        'nom',
    ];
    protected $table="countries";
    public function villes()
    {
        return $this->hasMany('App\Models\City');
    }

    public function companies()
    {
        return $this->hasMany('App\Models\Company');
    }
}
