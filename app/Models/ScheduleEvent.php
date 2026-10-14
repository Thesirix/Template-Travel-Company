<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleEvent extends Model
{
    protected $table = 'emplois';

    use HasFactory;


    protected $fillable = [
        'gent_id',
        'title',
        'start_date',
        'end_date',
        'color',
    ];

    // Relation : Un emploi appartient à un agent (gent)
    public function gent()
    {
        return $this->belongsTo(Agent::class);
    }
}