<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /** Accueil de chaque espace, par garde */
    private const HOMES = [
        'gent' => 'agent.home',
        'gestion' => 'admin.home',
    ];

    /**
     * Un utilisateur déjà connecté qui ouvre une page réservée aux invités retourne à l'accueil de son espace.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        foreach ($guards ?: [null] as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->route(self::HOMES[$guard] ?? 'home');
            }
        }

        return $next($request);
    }
}
