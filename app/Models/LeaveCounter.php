<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LeaveCounter extends Model
{


    public $timestamps = false;

    protected $fillable = [
        'gent_id',
        'type',
        'balance',
    ];

    protected $casts = [
        'balance' => 'float',
    ];


    public function gent()
    {
        return $this->belongsTo(Agent::class, 'gent_id');
    }
}
