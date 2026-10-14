<?php

namespace App\Support;

/**
 * Images des PDF (dompdf) : lues sur le disque au lieu d'être téléchargées.
 *
 * dompdf qui télécharge une image du site sur le site lui-même attend une réponse
 * que le serveur, occupé à générer le PDF, ne peut pas donner (blocage complet avec
 * `php artisan serve`, requête doublée en production).
 */
class PdfAsset
{
    /**
     * Chemin local d'une image de `public/` (« sys/logo.png », « /img/x.png » ou une URL du site).
     * Renvoie une chaîne vide si le fichier n'existe pas, pour ne jamais lancer de téléchargement.
     */
    public static function path(?string $src): string
    {
        if (!$src) {
            return '';
        }

        $path = parse_url($src, PHP_URL_PATH) ?: $src;
        $file = public_path(ltrim(rawurldecode($path), '/'));

        return is_file($file) ? $file : '';
    }
}
