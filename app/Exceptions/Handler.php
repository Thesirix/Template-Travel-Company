<?php

namespace App\Exceptions;

use App\Enums\Api\GeneralReturnEnum;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * Les erreurs sont journalisées par Laravel (storage/logs), sauf les cas normaux
 * (validation, page introuvable, accès refusé, non connecté).
 */
class Handler extends ExceptionHandler
{
    /** Champs jamais renvoyés dans le formulaire après une erreur de validation */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Non connecté : JSON 401 pour l'API, sinon la page de connexion commune.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['code' => GeneralReturnEnum::unauthenticated, 'message' => 'Unauthenticated'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
