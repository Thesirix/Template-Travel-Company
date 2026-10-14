<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'to_do_lists';

    public $fillable = ['id', 'text', 'gent_id', 'created_at'];
    public $timestamps = false;

    public function gent()
    {
        return $this->belongsTo(Agent::class);
    }
}
