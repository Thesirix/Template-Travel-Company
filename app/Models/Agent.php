<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Agent extends Authenticatable
{
    protected $table = 'gents';

    use Notifiable, HasRoles;


    protected $guard = 'gent';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'state',
        'email',
        'password',
        'prenom',
        'phone',
        'adresse',
        'contract_start_date',
        'is_super_agent',
    ];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'contract_start_date' => 'date',
        'is_super_agent' => 'boolean',
    ];



    public function agence()
    {
        return $this->belongsTo(Agency::class);
    }

    public function flightsReservations()
    {
        return $this->hasMany(FlightReservation::class, 'gent_id', 'id');
    }


    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_vol_estimates', 'gent', 'client_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'gent_id');
    }

    public function leaveCounter()
    {
        return $this->hasMany(LeaveCounter::class, 'gent_id');
    }
}
