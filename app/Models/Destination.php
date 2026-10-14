<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = ['name', 'slug', 'country_id', 'icon', 'color', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function volGroups()
    {
        return $this->hasMany(FlightGroup::class, 'country_id', 'country_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position')->orderBy('name');
    }

    /**
     * Permission Spatie (garde « gent ») qui donne accès aux vols de la destination.
     */
    public function permissionName(): ?string
    {
        return $this->country ? 'gerer ' . $this->country->nom : null;
    }

    public function iconUrl(): ?string
    {
        return $this->icon ? asset($this->icon) : null;
    }

    /**
     * Initiales affichées quand la destination n'a pas d'icône.
     */
    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 2));
    }
}
