<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Allotement : places réservées auprès d'une compagnie (PNR) jusqu'à une date limite.
 * Cycle : en vente → passagers placés (nominative) → confirmée → émise ; ou relâchée / expirée.
 */
class BlockPowerOption extends Model
{
    public const STATUSES = [
        'available' => 'En vente',
        'expiring' => 'Option bientôt échue',
        'named' => 'Nominative',
        'confirmed' => 'À émettre',
        'issued' => 'Émise',
        'released' => 'Relâchée',
        'expired' => 'Expirée',
    ];

    /** Options encore modifiables : en vente ou nominatives */
    public const ACTIVE = ['available', 'expiring', 'named'];

    /** Une option passe « bientôt échue » ce nombre de jours avant sa date limite */
    public const EXPIRING_DAYS = 2;

    protected $table = 'block_power_options';

    protected $fillable = ['agent_id', 'name', 'status', 'pnr', 'company', 'country', 'departure_city', 'arrival_city',
        'date_aller', 'date_retour', 'seats', 'cabin_class', 'price', 'option_date', 'pnr_created_on', 'note'];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function passengers()
    {
        return $this->hasMany(BlockPowerPassenger::class, 'option_id');
    }

    public function release()
    {
        return $this->hasOne(BlockPowerRelease::class, 'option_id')->latestOfMany();
    }

    public function scopeInStatus(Builder $query, array|string $statuses): Builder
    {
        return $query->whereIn('status', (array) $statuses);
    }

    public function freeSeats(): int
    {
        return max(0, (int) $this->seats - $this->passengers->count());
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE, true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
