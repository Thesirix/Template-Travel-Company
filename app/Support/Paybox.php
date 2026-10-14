<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Paybox System (Verifone) : formulaire de paiement signé (HMAC) et vérification
 * de la signature RSA des retours (variable « Sign » de PBX_RETOUR).
 * Réglages : config/services.php → paybox (site, rang, identifiant, clé HMAC, clé publique).
 */
class Paybox
{
    /** Variables renvoyées par Paybox ; la signature doit rester la dernière */
    public const RETURN_FIELDS = 'Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K';

    public const SUCCESS = '00000';

    public const MESSAGES = [
        '00000' => 'Paiement accepté.',
        '00001' => 'Connexion au centre d’autorisation impossible. Réessayez dans quelques instants.',
        '00003' => 'Erreur Paybox. Réessayez dans quelques instants.',
        '00004' => 'Numéro de carte ou cryptogramme visuel invalide.',
        '00006' => 'Accès refusé : site, rang ou identifiant Paybox incorrect.',
        '00008' => 'Date de fin de validité de la carte incorrecte.',
        '00009' => 'Erreur de création d’un abonnement.',
        '00010' => 'Devise inconnue.',
        '00011' => 'Montant incorrect.',
        '00015' => 'Paiement déjà effectué.',
        '00016' => 'Abonné déjà existant.',
        '00021' => 'Carte non autorisée.',
        '00029' => 'Carte non conforme.',
        '00030' => 'Délai dépassé sur la page de paiement (plus de 15 minutes).',
        '00033' => 'Paiement refusé depuis ce pays.',
        '00040' => 'Opération sans authentification 3-D Secure refusée.',
        '99999' => 'Paiement en attente de validation par la banque.',
    ];

    public function url(): string
    {
        return config('services.paybox.url') ?: 'https://' . config('services.paybox.domain') . '/cgi/MYchoix_pagepaiement.cgi';
    }

    /**
     * Champs du formulaire (dans l'ordre exact de la chaîne signée) + PBX_HMAC.
     *
     * @param array<string, scalar> $extra variables PBX_* ajoutées après les variables obligatoires
     */
    public function fields(int $amountInCents, string $reference, string $email, array $extra = []): array
    {
        $fields = [
            'PBX_SITE' => config('services.paybox.site'),
            'PBX_RANG' => config('services.paybox.rank'),
            'PBX_IDENTIFIANT' => config('services.paybox.id'),
            'PBX_TOTAL' => $amountInCents,
            'PBX_DEVISE' => 978,
            'PBX_CMD' => $reference,
            'PBX_PORTEUR' => $email,
            'PBX_RETOUR' => self::RETURN_FIELDS,
            'PBX_HASH' => 'SHA512',
            'PBX_TIME' => date('c'),
        ] + $extra;
        $message = implode('&', array_map(fn ($key, $value) => "$key=$value", array_keys($fields), $fields));
        $fields['PBX_HMAC'] = strtoupper(hash_hmac('sha512', $message, pack('H*', (string) config('services.paybox.hmac_key'))));

        return $fields;
    }

    /**
     * Vérifie que le retour vient bien de Paybox : la signature porte sur la chaîne de requête brute
     * qui précède « &Sign= ».
     */
    public function verify(Request $request): bool
    {
        $query = (string) $request->server('QUERY_STRING');
        if ($request->isMethod('post') && ! str_contains($query, 'Sign=')) {
            $query = $request->getContent();
        }
        $position = strrpos($query, '&Sign=');
        $keyFile = config('services.paybox.public_key');
        if ($position === false || ! $keyFile || ! is_file($keyFile)) {
            Log::warning('Retour Paybox non vérifiable', ['signature' => $position !== false, 'cle_publique' => $keyFile]);

            return false;
        }
        $signature = base64_decode(urldecode(substr($query, $position + 6)));
        $key = openssl_pkey_get_public((string) file_get_contents($keyFile));

        return $key !== false && openssl_verify(substr($query, 0, $position), $signature, $key, OPENSSL_ALGO_SHA1) === 1;
    }

    public static function message(?string $code): string
    {
        $code = (string) $code;
        if (str_starts_with($code, '001')) {
            return 'Paiement refusé par la banque (code ' . $code . ').';
        }

        return self::MESSAGES[$code] ?? 'Paiement non abouti (code ' . ($code ?: 'inconnu') . ').';
    }
}
