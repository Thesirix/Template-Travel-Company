<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\ClientQuote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Texte du devis envoyé au client par SMS (sans accents : certains opérateurs les remplacent mal).
 */
class ClientQuoteService
{
    public function smsMessage(ClientQuote $quote, Agent $agent): string
    {
        $dates = $quote->return_date
            ? 'du ' . Carbon::parse($quote->departure_date)->format('d/m/Y') . ' au ' . Carbon::parse($quote->return_date)->format('d/m/Y')
            : 'le ' . Carbon::parse($quote->departure_date)->format('d/m/Y');
        $passengers = $quote->nb_passagers_adultes . ' adultes'
            . ($quote->nb_passagers_childs > 0 ? ", {$quote->nb_passagers_childs} enfants" : '')
            . ($quote->nb_passagers_babies > 0 ? ", {$quote->nb_passagers_babies} bebes" : '');
        $details = $quote->formule === 'Maritime'
            ? ($quote->installation ?: 'aucune installation') . "\nvehicule : " . ($quote->vehicule ?: 'sans vehicule')
            : ($quote->bagage ? 'avec bagages' : 'sans bagages');

        $message = "Bonjour, nous vous remercions pour votre confiance. Voici votre devis.\n"
            . "{$quote->departure_city}/{$quote->arrived_city} $dates\n"
            . "{$quote->company}\n$passengers\n$details\n"
            . 'Prix ' . $quote->proposed_price . " EUR\n"
            . "{$agent->prenom}, {$agent->phone}";

        return Str::ascii($message);
    }
}
