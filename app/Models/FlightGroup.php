<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightGroup extends Model
{
    protected $table = 'vol_groups';

    protected $fillable = ['id','name','country_id','type','same_itineraire','is_active','created_at'];

    public $timestamps=false;

    public function flights()
    {
        return $this->hasMany(Flight::class, 'vol_group_id', 'id');
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

}
