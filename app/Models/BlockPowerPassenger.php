<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Passager placé sur un allotement (type Mr, Mrs, CHD, INF), avec son prix de vente et son acompte.
 */
class BlockPowerPassenger extends Model
{
    public const TYPES = ['Mr' => 'Mr', 'Mrs' => 'Mrs', 'CHD' => 'Enfant (2 à 11 ans)', 'INF' => 'Bébé (moins de 2 ans)'];

    protected $table = 'block_power_passengers';

    protected $fillable = ['option_id', 'type', 'last_name', 'first_name', 'sale_price', 'deposit', 'deposit_number'];

    public function option()
    {
        return $this->belongsTo(BlockPowerOption::class, 'option_id');
    }
}
