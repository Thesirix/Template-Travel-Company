<?php

/*
|--------------------------------------------------------------------------
| Identité de l'agence
|--------------------------------------------------------------------------
| Tout ce qui est propre à une agence (nom, logo, adresses, destinataires
| des notifications) se règle ici via le .env, jamais en dur dans le code.
*/

$list = fn (string $key) => array_values(array_filter(array_map('trim', explode(',', (string) env($key, '')))));

return [

    'name' => env('AGENCY_NAME', env('APP_NAME', 'Travel Company')),

    'domain' => env('AGENCY_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),

    // Pays de départ des voyages (ses villes et aéroports sont proposés sur tous les vols)
    'origin_country' => env('AGENCY_ORIGIN_COUNTRY', 'France'),

    // Logo par défaut (le logo se règle surtout dans l'admin : Identité & couleurs)
    'logo' => env('AGENCY_LOGO'),

    'emails' => [
        // Expéditeur par défaut des mails transactionnels
        'from' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        // Boîte de l'administration (demandes d'aide, messages agents)
        'admin' => env('AGENCY_ADMIN_EMAIL', 'admin@example.com'),
        // Réception des notifications de paiement
        'payments' => env('AGENCY_PAYMENTS_EMAIL', 'payments@example.com'),
        // Alertes techniques (échecs de cron, incohérences de places)
        'alerts' => env('AGENCY_ALERTS_EMAIL', 'alerts@example.com'),
    ],

    'notifications' => [
        // Destinataires des mails de réservation / annulation / paiement
        'reservation_emails' => $list('AGENCY_RESERVATION_EMAILS'),
        // Destinataires des rappels d'émission de billets
        'emission_emails' => $list('AGENCY_EMISSION_EMAILS'),
        // Numéros prévenus par SMS pour les émissions
        'emission_phones' => $list('AGENCY_EMISSION_PHONES'),
    ],

];
