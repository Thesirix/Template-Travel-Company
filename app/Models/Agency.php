<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImage;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $table = 'agences';

    use HasPublicImage;

    public $fillable=['name','is_partner_agency'];
    public function agents()
    {
        return $this->hasMany(Agent::class, 'agence_id');
    }



}
