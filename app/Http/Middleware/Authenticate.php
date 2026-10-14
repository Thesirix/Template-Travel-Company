<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Page de connexion commune (l'API répond en JSON, sans redirection).
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        return route('login');
    }
}
