<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

/**
 * Langue de l'interface : choix mémorisé en session (bouton de la barre du haut), sinon la langue par défaut.
 * Les textes sont écrits en français dans le code ; lang/en.json en donne la traduction anglaise.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale');
        if (! array_key_exists((string) $locale, config('app.locales'))) {
            $locale = config('app.locale');
        }
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
