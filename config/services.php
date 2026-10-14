<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, SparkPost and others. This file provides a sane default
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    // Paiement Paybox / Verifone
    'paybox' => [
        'site' => env('PAYBOX_SITE'),
        'rank' => env('PAYBOX_RANK'),
        'id' => env('PAYBOX_ID'),
        'hmac_key' => env('PAYBOX_HMAC_KEY'),
        // Page de paiement utilisée par l'API (site public)
        'url' => env('PAYBOX_URL'),
        // Serveur de la page de paiement côté agent (préproduction : preprod-tpeweb.paybox.com)
        'domain' => env('PAYBOX_DOMAIN', 'tpeweb.paybox.com'),
        // Clé publique Paybox (pubkey.pem) qui vérifie la signature des retours de paiement
        'public_key' => env('PAYBOX_PUBLIC_KEY') ?: storage_path('app/paybox/pubkey.pem'),
    ],

    // SMS iSendPro
    'isendpro' => [
        'id' => env('ISENDPROID'),
        'sender' => env('ISENDPROEMETTEUR'),
    ],

];
