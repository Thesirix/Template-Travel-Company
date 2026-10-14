<?php

namespace App\Http\Middleware;

use App\Enums\Api\GeneralReturnEnum;
use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * API du site public : client authentifié par le jeton JWT du cookie HttpOnly « access_token ».
 */
class JWTAuthenticate
{
    public function handle($request, Closure $next)
    {
        $token = $request->cookie('access_token');
        if (! $token) {
            return $this->unauthenticated('Token not provided');
        }
        $request->headers->set('Authorization', 'Bearer ' . $token);

        try {
            $guard = Auth::guard('client');
            $guard->setToken($token);
            if (! $guard->check()) {
                return $this->unauthenticated('Unauthorized');
            }
        } catch (JWTException) {
            return $this->unauthenticated('Token invalid or expired');
        }

        return $next($request);
    }

    private function unauthenticated(string $error)
    {
        return response()->json(['code' => GeneralReturnEnum::unauthenticated, 'error' => $error], 401);
    }
}
