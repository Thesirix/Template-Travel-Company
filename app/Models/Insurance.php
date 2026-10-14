<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $table = 'assurences';

    /** Valeur de « type » pour une assurance proposée sur toutes les destinations */
    public const ALL_DESTINATIONS = 'Toutes';

    public $fillable=['id','type','name','description','prix','created_at'];
    public $timestamps=false;

    /** Assurances proposées pour un pays de destination (« type » = nom du pays, ou toutes destinations) */
    public function scopeForCountry(Builder $query, ?string $country): Builder
    {
        return $query->where(fn ($q) => $q->where('type', 'like', (string) $country)->orWhere('type', self::ALL_DESTINATIONS));
    }
}
