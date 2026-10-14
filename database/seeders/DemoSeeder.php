<?php

namespace Database\Seeders;

use Database\Seeders\Demo\AccountSeeder;
use Database\Seeders\Demo\BlockPowerSeeder;
use Database\Seeders\Demo\BoatSeeder;
use Database\Seeders\Demo\CatalogSeeder;
use Database\Seeders\Demo\ClientSeeder;
use Database\Seeders\Demo\FlightSeeder;
use Database\Seeders\Demo\TeamSeeder;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration, entièrement fictives, qui remplissent chaque module de l'application.
 * Tous les comptes utilisent le mot de passe DemoSeeder::PASSWORD.
 */
class DemoSeeder extends Seeder
{
    public const PASSWORD = 'Demo1234!';

    /** Destinations vendues : [nom, identifiant d'URL, pays, ville, aéroport, code IATA, couleur] */
    public const DESTINATIONS = [
        ['Italie', 'italie', 'Italie', 'Rome', 'Rome Fiumicino', 'FCO', '#16a34a'],
        ['Grèce', 'grece', 'Grèce', 'Athènes', 'Athènes Elefthérios-Venizélos', 'ATH', '#2563eb'],
        ['Portugal', 'portugal', 'Portugal', 'Lisbonne', 'Lisbonne Humberto Delgado', 'LIS', '#dc2626'],
        ['Canada', 'canada', 'Canada', 'Montréal', 'Montréal-Trudeau', 'YUL', '#ea580c'],
    ];

    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            AccountSeeder::class,
            FlightSeeder::class,
            BoatSeeder::class,
            BlockPowerSeeder::class,
            ClientSeeder::class,
            TeamSeeder::class,
        ]);
    }
}
