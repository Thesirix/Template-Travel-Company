<?php

namespace App\Services\Api;

use App\Enums\Api\GeneralReturnEnum;
use App\Models\Client;
use BackedEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Connexion des clients du site public : jeton JWT dans un cookie HttpOnly « access_token »,
 * plus un cookie « isLogged » lisible par le front.
 */
class AuthService
{
    public function login(string $email, string $password, BackedEnum $code, ?Client $client = null): JsonResponse
    {
        $token = Auth::guard('client')->attempt(['email' => $email, 'password' => $password]);
        if (! $token) {
            return response()->json(['code' => GeneralReturnEnum::unauthenticated, 'error' => 'Unauthorized'], 401);
        }

        $client ??= Auth::guard('client')->user();

        return response()->json(['code' => $code, 'data' => ['id' => $client->id, 'firstName' => $client->firstName, 'lastName' => $client->lastName]])
            ->withCookie(self::cookie('access_token', $token))
            ->withCookie(self::isLoggedCookie());
    }

    /**
     * Cookie d'authentification du site public.
     * Mêmes réglages que la session (SESSION_DOMAIN, SESSION_SAME_SITE, SESSION_SECURE_COOKIE) :
     * "lax" en local, "none" + secure en production si le front est sur un autre domaine.
     *
     * @param bool $httpOnly false pour les cookies que le front doit lire en JavaScript (isLogged)
     */
    public static function cookie(string $name, string $value, bool $httpOnly = true, ?int $minutes = null): Cookie
    {
        $sameSite = config('session.same_site', 'lax');

        return cookie(
            $name,
            $value,
            $minutes ?? (int) config('jwt.ttl', 60),
            '/',
            config('session.domain'),
            $sameSite === 'none' ? true : (bool) config('session.secure'),
            $httpOnly,
            false,
            $sameSite
        );
    }

    /** Indicateur lisible par le front : le client est connecté (expire juste avant le jeton) */
    public static function isLoggedCookie(): Cookie
    {
        return self::cookie('isLogged', '1', false, max(1, (int) config('jwt.ttl', 60) - 2));
    }
}
