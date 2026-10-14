<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Envoi de SMS via iSendPro. Sans identifiant configuré (local, tests), les SMS sont seulement journalisés.
 */
class SmsService
{
    private const URL = 'https://apirest.isendpro.com/cgi-bin/smsmulti';

    /**
     * @param string|array $phones un numéro ou une liste de numéros
     * @return array{sent: bool, response: mixed}
     */
    public function sendSms(string|array $phones, string $message): array
    {
        $phones = array_values(array_filter((array) $phones));
        if (! $phones) {
            return ['sent' => false, 'response' => 'aucun numéro'];
        }

        if (! config('services.isendpro.id')) {
            Log::info('SMS non envoyé (iSendPro non configuré)', ['numeros' => $phones, 'message' => $message]);

            return ['sent' => false, 'response' => 'non configuré'];
        }

        try {
            $response = Http::asForm()->timeout(15)->post(self::URL, [
                'keyid' => config('services.isendpro.id'),
                'num' => implode(',', $phones),
                'sms' => $message,
                'emetteur' => config('services.isendpro.sender'),
                'smslong' => 2,
            ]);
            if ($response->failed()) {
                Log::warning('Échec d\'envoi SMS', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return ['sent' => $response->successful(), 'response' => $response->json() ?? $response->body()];
        } catch (Throwable $e) {
            Log::error('Erreur d\'envoi SMS : ' . $e->getMessage());

            return ['sent' => false, 'response' => $e->getMessage()];
        }
    }
}
