<?php

namespace App\Support;

use Carbon\Carbon;
use Throwable;

/**
 * Affichage des dates dans les vues : accepte une date vide ou dans n'importe quel format reconnu.
 */
class Dates
{
    public static function format(mixed $value, string $format = 'd/m/Y'): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        try {
            return ($value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse($value))->format($format);
        } catch (Throwable) {
            return (string) $value;
        }
    }
}
