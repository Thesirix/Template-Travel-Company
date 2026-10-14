<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'id','firstName','lastName', 'email', 'phoneNumber','created_at','password',
       'statut', 'city', 'country', 'observation', 'recall', 'expire_passport', 'gent_id', 'provenance'
    ];

    protected $hidden= ['password'];
    public $timestamps = false;




    public function gent()
    {
        return $this->belongsTo(Agent::class, 'gent_id');
    }

    // Relation vers les agents via client_vol_estimates
    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'client_vol_estimates', 'client_id', 'gent_id');
    }

    // Relation vers les devis de vols
    public function volEstimates()
    {
        return $this->hasMany(ClientQuote::class, 'client_id');
    }










    // Return the unique identifier for the user
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Return an array of custom claims
    public function getJWTCustomClaims()
    {
        return [];
    }

}
