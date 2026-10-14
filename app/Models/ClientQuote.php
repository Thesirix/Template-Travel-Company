<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientQuote extends Model
{
    protected $table = 'client_vol_estimates';

    public $fillable=['id','first_name','last_name','comment',
        'destination','formule','installation','vehicule','bagage','transport','company','departure_city','arrived_city',
        'departure_date','return_date','nb_passagers_adultes','nb_passagers_childs',
        'nb_passagers_babies','selled','proposed_price','created_at','client_id', 'gent_id',
        'agence_id','is_vente','marge'];

    public $timestamps=false;

public function client()
{
    return $this->belongsTo(Client::class, 'client_id');
}

    public function agenceRelation(){
        return $this->belongsTo(Agency::class,'agence_id');
    }
    public function gentRelation(){
        return $this->belongsTo(Agent::class,'gent_id');
    }
}
