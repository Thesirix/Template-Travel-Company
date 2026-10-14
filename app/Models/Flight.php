<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $table = 'vols';

    protected $guarded = ['id'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function volGroup()
    {
        return $this->belongsTo(FlightGroup::class,'vol_group_id','id');
    }

    public function reservations1()
    {
        return $this->hasMany(FlightReservation::class, 'vol_id', 'id');
    }
    public function reservations2()
    {
        return $this->hasMany(FlightReservation::class, 'vol_retour_id', 'id');
    }
    public function reservations()
    {
        return $this->reservations1()->union($this->reservations2());
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function escaliers()
    {
        return $this->hasMany(FlightStop::class, 'vol_id');
    }
    public function escaliers_aller()
    {
        return $this->hasMany(FlightStop::class, 'vol_id')->where('type','0');
    }
    public function escaliers_retour()
    {
        return $this->hasMany(FlightStop::class, 'vol_id')->where('type','1');
    }



    public function conditions()
    {
        return $this->hasOne(SalesCondition::class, 'id', 'condition_id');
    }

    public function ville()
    {
        return $this->hasOne(City::class, 'id', 'ville_id');
    }

    /** Prix de vente total pour un nombre d'adultes, d'enfants et de bébés */
    public function salePrice(int $adults, int $children = 0, int $babies = 0): float
    {
        return $adults * (float) $this->prix_vente_adult + $children * (float) $this->prix_vente_enfant + $babies * (float) $this->prix_vente_bebe;
    }



}
