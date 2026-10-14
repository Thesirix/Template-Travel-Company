<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LeaveRequest extends Model
{
    protected $table = 'calendars';

    protected $fillable = [
        'id',
        'gent_id',
        'name',
        'start_date',
        'type',
        'end_date',
        'freq',
        'created_at',
        'status',
        'rejection_reason',
        'jours_par_type',
        'modification_token'
    ];

    public $timestamps = true;

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'gent_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($calendar) {
            $calendar->modification_token = Str::uuid();
        });

        static::updating(function ($calendar) {
            $calendar->modification_token = Str::uuid();
        });
    }
}
