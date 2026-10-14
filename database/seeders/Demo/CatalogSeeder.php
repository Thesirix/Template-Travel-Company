<?php

namespace Database\Seeders\Demo;

use App\Models\Insurance;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Identité de l'agence, pays, destinations, villes, aéroports, compagnies, conditions de vente et assurances.
 */
class CatalogSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        foreach (['agency_name' => 'Travel Company', 'color_primary' => '#4f46e5', 'color_accent' => '#f59e0b', 'banner_style' => 'gradient', 'agency_logo' => 'images/brand/logo.png', 'agency_logo_dark' => 'images/brand/logo-blanc.png', 'agency_icon' => 'images/brand/icone-blanc.png'] as $key => $value) {
            DB::table('settings')->insert($this->stamps(['key' => $key, 'value' => $value]));
        }

        $country = fn (string $name) => DB::table('countries')->insertGetId($this->stamps(['nom' => $name, 'show' => true]));
        $france = $country('France');
        $countries = [];
        foreach (DemoSeeder::DESTINATIONS as $position => [$name, $slug, $countryName, , , , $color]) {
            $countries[$slug] = $country($countryName);
            DB::table('destinations')->insert($this->stamps([
                'name' => $name, 'slug' => $slug, 'country_id' => $countries[$slug], 'icon' => null,
                'color' => $color, 'position' => $position, 'is_active' => true,
            ]));
        }
        $country('Espagne');

        $cities = [
            [$france, 'Paris', '#1d4ed8', 'Paris-Charles de Gaulle', 'CDG'],
            [$france, 'Marseille', '#0891b2', 'Marseille Provence', 'MRS'],
            [$france, 'Lyon', '#7c3aed', 'Lyon-Saint Exupéry', 'LYS'],
        ];
        foreach (DemoSeeder::DESTINATIONS as [, $slug, , $city, $airport, $code, $color]) {
            $cities[] = [$countries[$slug], $city, $color, $airport, $code];
        }
        $cities[] = [$countries['italie'], 'Milan', '#15803d', 'Milan Malpensa', 'MXP'];
        foreach ($cities as [$countryId, $city, $color, $airport, $code]) {
            $cityId = DB::table('villes')->insertGetId($this->stamps(['country_id' => $countryId, 'nom' => $city, 'coleur' => $color]));
            DB::table('air_ports')->insert($this->stamps(['ville_id' => $cityId, 'nom' => $airport, 'abv' => $code]));
        }

        foreach ([['Sky Demo Airlines', $france], ['Blue Wings', $france], ['Aria Italia', $countries['italie']], ['Maple Air', $countries['canada']]] as [$name, $countryId]) {
            DB::table('companies')->insert($this->stamps([
                'country_id' => $countryId, 'nom' => $name, 'type' => true, 'show' => true, 'site' => true,
            ]));
        }

        DB::table('conditions')->insert($this->stamps([
            'nom' => 'Conditions standard',
            'Formalite_sante' => 'Aucun vaccin obligatoire. Vérifiez les recommandations sanitaires avant le départ.',
            'Formalite_police' => 'Passeport valide 6 mois après la date de retour.',
            'Formalite_inscription' => 'Acompte de 30 % à la réservation, solde 30 jours avant le départ.',
            'Formalite_modifiation' => 'Modification possible jusqu\'à 15 jours avant le départ, avec frais.',
            'Formalite_annulation' => 'Annulation : 30 % retenus jusqu\'à J-30, 100 % ensuite.',
        ]));
        DB::table('conditions')->insert($this->stamps([
            'nom' => 'Conditions flexibles',
            'Formalite_sante' => 'Aucun vaccin obligatoire.',
            'Formalite_police' => 'Carte d\'identité ou passeport en cours de validité.',
            'Formalite_inscription' => 'Paiement intégral à la réservation.',
            'Formalite_modifiation' => 'Modification gratuite jusqu\'à 7 jours avant le départ.',
            'Formalite_annulation' => 'Remboursement intégral jusqu\'à J-15, 50 % ensuite.',
        ]));

        foreach ([
            [Insurance::ALL_DESTINATIONS, 'Assurance annulation', 'Remboursement en cas d\'annulation pour motif couvert.', 35],
            [Insurance::ALL_DESTINATIONS, 'Assurance multirisque', 'Annulation, bagages, assistance et rapatriement.', 69],
            ['Canada', 'Assurance santé Canada', 'Frais médicaux et hospitalisation sur place, jusqu\'à 150 000 €.', 45],
        ] as [$type, $name, $description, $price]) {
            DB::table('assurences')->insert(['type' => $type, 'name' => $name, 'description' => $description, 'prix' => $price, 'created_at' => $this->now()]);
        }
    }
}
