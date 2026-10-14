<?php

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
| gent    : agents (espace /agent), session
| gestion : administrateurs (espace /admin), session
| client  : clients du site public (API /api), jeton JWT
*/

return [

    'defaults' => [
        'guard' => 'gent',
        'passwords' => 'gents',
    ],

    'guards' => [
        'gent' => [
            'driver' => 'session',
            'provider' => 'gents',
        ],
        'gestion' => [
            'driver' => 'session',
            'provider' => 'gestions',
        ],
        'client' => [
            'driver' => 'jwt',
            'provider' => 'clients',
        ],
    ],

    'providers' => [
        'gents' => [
            'driver' => 'eloquent',
            'model' => App\Models\Agent::class,
        ],
        'gestions' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
        'clients' => [
            'driver' => 'eloquent',
            'model' => App\Models\Client::class,
        ],
    ],

    // Réinitialisation de mot de passe par e-mail (non utilisée : les mots de passe sont changés par l'administration)
    'passwords' => [
        'gents' => [
            'provider' => 'gents',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
