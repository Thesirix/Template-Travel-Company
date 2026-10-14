<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Change la langue de l'interface et revient sur la page en cours.
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(array_key_exists($locale, config('app.locales')), 404);
        $request->session()->put('locale', $locale);

        return back();
    }
}
