<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'black_notes';

    public $fillable=['id','text','gent_id','created_at'];
    public $timestamps=false;
}
