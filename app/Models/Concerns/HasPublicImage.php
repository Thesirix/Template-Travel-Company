<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Colonne « image » : chemin relatif à public/ en base (ex. « img/logo.png »), URL complète en lecture.
 * Une valeur déjà enregistrée en URL absolue est renvoyée telle quelle.
 */
trait HasPublicImage
{
    protected function image(): Attribute
    {
        return Attribute::get(fn (?string $value) => $value && ! preg_match('#^(https?:)?//#', $value) ? asset($value) : $value);
    }
}
