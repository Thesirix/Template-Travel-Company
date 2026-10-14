<?php

namespace Database\Seeders\Demo;

use App\Support\AgentPermissions;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Agences, administrateur, agents (droits, compteurs de congés) et clients du site public.
 */
class AccountSeeder extends Seeder
{
    use SeedsDemoData;

    /** Plus d'un an d'ancienneté */
    private function isVeteran(string $start): bool
    {
        return $this->now()->diffInMonths($start, true) > 12;
    }

    private const LEAVE_BALANCES = ['conge' => 12.5, 'repos' => 4, 'ferie' => 2, 'maladie' => 0, 'autre' => 1];

    public function run(): void
    {
        $password = Hash::make(DemoSeeder::PASSWORD);

        $main = DB::table('agences')->insertGetId($this->stamps([
            'name' => 'Travel Company Paris', 'phone' => '01 00 00 00 00', 'rue' => '1 rue de la Démo',
            'ville' => 'Paris', 'email' => 'paris@travel-company.test',
        ]));
        $partner = DB::table('agences')->insertGetId($this->stamps([
            'name' => 'Agence Partenaire Lyon', 'phone' => '04 00 00 00 00', 'rue' => '2 place de l\'Exemple',
            'ville' => 'Lyon', 'email' => 'lyon@travel-company.test', 'is_partner_agency' => true,
        ]));

        // Autres agences du réseau (et une seconde agence partenaire)
        $others = [];
        foreach ([
            ['Travel Company Marseille', '04 91 00 00 00', '12 quai de la Démo', 'Marseille', 'marseille', false],
            ['Travel Company Bordeaux', '05 56 00 00 00', '8 cours de l\'Exemple', 'Bordeaux', 'bordeaux', false],
            ['Évasion Voyages Lille', '03 20 00 00 00', '3 rue du Test', 'Lille', 'lille', true],
        ] as [$name, $phone, $street, $city, $slug, $isPartner]) {
            $others[$city] = DB::table('agences')->insertGetId($this->stamps([
                'name' => $name, 'phone' => $phone, 'rue' => $street, 'ville' => $city,
                'email' => $slug . '@travel-company.test', 'is_partner_agency' => $isPartner,
            ]));
        }

        DB::table('gestions')->insert($this->stamps([
            'name' => 'Admin', 'prenom' => 'Démo', 'phone' => '06 00 00 00 00',
            'email' => 'admin@travel-company.test', 'password' => $password,
        ]));

        $all = array_merge(
            array_map(fn ($d) => 'gerer ' . $d[2], DemoSeeder::DESTINATIONS),
            array_values(AgentPermissions::MODULES),
        );
        foreach ($all as $permission) {
            DB::table('permissions')->insert($this->stamps(['name' => $permission, 'guard_name' => 'gent']));
        }

        $partnerRights = ['gerer Italie', 'gerer Grèce', 'gerer Clients', 'gerer devis'];
        $sales = array_values(array_diff($all, ['gerer Planning']));
        // [nom, prénom, identifiant, agence, super agent, droits, début de contrat]
        $agents = [
            ['Martin', 'Thomas', 'super.agent', $main, true, $all, '2023-03-01'],
            ['Bernard', 'Lucas', 'agent', $main, false, $all, '2024-09-01'],
            ['Petit', 'Emma', 'partenaire', $partner, false, $partnerRights, '2025-06-15'],
            ['Moreau', 'Hugo', 'ancien.agent', $main, false, [], '2022-01-10'],
            ['Laurent', 'Julien', 'julien.laurent', $main, false, $sales, '2021-05-03'],
            ['Garcia', 'Léa', 'lea.garcia', $main, false, $sales, '2024-02-12'],
            ['Roux', 'Antoine', 'antoine.roux', $main, false, $sales, '2025-01-06'],
            ['Fournier', 'Karim', 'karim.fournier', $others['Marseille'], false, $sales, '2022-09-01'],
            ['Girard', 'Chloé', 'chloe.girard', $others['Marseille'], false, $sales, '2023-11-15'],
            ['Mercier', 'Yanis', 'yanis.mercier', $others['Marseille'], false, $sales, '2025-03-03'],
            ['Lambert', 'Sarah', 'sarah.lambert', $others['Bordeaux'], false, $sales, '2024-04-02'],
            ['Bonnet', 'Mehdi', 'mehdi.bonnet', $others['Bordeaux'], false, $sales, '2023-06-19'],
            ['Fontaine', 'Nicolas', 'nicolas.fontaine', $others['Lille'], false, $partnerRights, '2025-02-10'],
            ['Chevalier', 'Manon', 'manon.chevalier', $others['Lille'], false, $partnerRights, '2024-10-01'],
        ];
        foreach ($agents as [$name, $firstName, $login, $agency, $isSuper, $permissions, $start]) {
            $agentId = DB::table('gents')->insertGetId($this->stamps([
                'name' => $name, 'prenom' => $firstName, 'phone' => '06 00 00 00 00',
                'email' => $login . '@travel-company.test', 'password' => $password, 'agence_id' => $agency,
                // Plus d'un an d'ancienneté : solde de reprise saisi par l'administration (leave_openings, plus bas)
                'contract_start_date' => $this->isVeteran($start) ? null : $start, 'is_super_agent' => $isSuper,
                'state' => $login === 'ancien.agent' ? 'disable' : 'enable',
            ]));
            foreach (DB::table('permissions')->whereIn('name', $permissions)->pluck('id') as $permissionId) {
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $permissionId, 'model_type' => 'App\\Models\\Agent', 'model_id' => $agentId,
                ]);
            }
            foreach (self::LEAVE_BALANCES as $type => $balance) {
                DB::table('leave_counters')->insert(['gent_id' => $agentId, 'type' => $type, 'balance' => $balance]);
            }
            if ($this->isVeteran($start)) {
                DB::table('leave_openings')->insert($this->stamps([
                    'gent_id' => $agentId, 'month_start' => $this->now()->startOfMonth()->subMonths(4)->toDateString(),
                    'opening' => 6 + ($agentId * 7) % 13, 'adjust' => 0,
                ]));
            }
        }

        // Clients : un compte du site public, et des clients enregistrés en agence
        DB::table('clients')->insert([
            'firstName' => 'Julie', 'lastName' => 'Durand', 'email' => 'client@travel-company.test',
            'phoneNumber' => '06 00 00 00 00', 'password' => $password, 'created_at' => $this->now()->subMonths(3),
            'statut' => true, 'city' => 'Marseille', 'country' => 'France', 'provenance' => 'Site web',
        ]);
        $sellers = DB::table('gents')->where('state', 'enable')->orderBy('id')->pluck('id')->all();
        foreach ([
            ['Camille', 'Leroy', 'Lyon', 'Souhaite partir en famille pendant les vacances d\'été.', 30],
            ['Nathan', 'Thomas', 'Paris', 'Voyage de noces, budget confortable.', 12],
            ['Inès', 'Robert', 'Marseille', null, 5],
            ['Sofiane', 'Benali', 'Marseille', 'Rend visite à sa famille deux fois par an.', 80],
            ['Claire', 'Dubois', 'Bordeaux', 'Préfère les vols directs.', 64],
            ['Mathis', 'Lefebvre', 'Paris', null, 41],
            ['Amina', 'Diallo', 'Lille', 'Groupe de 6 personnes en juillet.', 37],
            ['Paul', 'Morel', 'Paris', 'Client fidèle depuis 2019.', 120],
            ['Sarah', 'Cohen', 'Lyon', null, 22],
            ['Hugo', 'Simon', 'Bordeaux', 'Demande un devis Canada pour l\'hiver.', 9],
            ['Léna', 'Michel', 'Marseille', null, 3],
            ['Rayan', 'Mansouri', 'Lille', 'Voyage scolaire, à recontacter en janvier.', 55],
        ] as $i => [$first, $last, $city, $observation, $daysAgo]) {
            $agentId = $i < 3 ? $this->agentId('agent') : $sellers[$i % count($sellers)];
            DB::table('clients')->insert([
                'firstName' => $first, 'lastName' => $last,
                'email' => \Illuminate\Support\Str::ascii(strtolower($first . '.' . $last)) . '@example.com', 'phoneNumber' => $this->phone(),
                'created_at' => $this->now()->subDays($daysAgo), 'statut' => true, 'city' => $city, 'country' => 'France',
                'provenance' => 'Agence', 'observation' => $observation, 'gent_id' => $agentId,
                'recall' => $this->now()->addDays(7)->toDateString(), 'expire_passport' => $this->now()->addYears(4)->toDateString(),
            ]);
        }
    }
}
