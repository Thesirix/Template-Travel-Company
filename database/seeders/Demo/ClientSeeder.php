<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demandes des clients : devis préparés en agence (vendus ou en cours), demandes saisies par les agents,
 * demandes de devis envoyées depuis le site public.
 */
class ClientSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $agency = (int) DB::table('agences')->min('id');
        $agents = [$this->agentId('super.agent'), $this->agentId('agent')];
        $clients = DB::table('clients')->orderBy('id')->pluck('id')->all();

        $quotes = [
            ['Italie', 'Vol + hôtel', 'Avion', 'Sky Demo Airlines', 'Paris', 'Rome', 40, 2, 1, 0, 1890, true],
            ['Grèce', 'Vol seul', 'Avion', 'Blue Wings', 'Marseille', 'Athènes', 22, 2, 0, 0, 1140, false],
            ['Portugal', 'Vol + voiture', 'Avion', 'Blue Wings', 'Lyon', 'Lisbonne', 55, 4, 2, 0, 2980, false],
            ['Corse', 'Traversée', 'Bateau', 'Mer Bleue Ferries', 'Marseille', 'Ajaccio', 30, 2, 1, 1, 640, true],
            ['Canada', 'Vol seul', 'Avion', 'Maple Air', 'Paris', 'Montréal', 80, 1, 0, 0, 760, false],
        ];
        foreach ($quotes as $i => [$destination, $formula, $transport, $company, $from, $to, $days, $adults, $children, $babies, $price, $sold]) {
            DB::table('client_vol_estimates')->insert([
                'client_id' => $clients[$i % count($clients)], 'agence_id' => $agency, 'gent_id' => $agents[$i % 2],
                'created_at' => $this->now()->subDays(20 - 3 * $i), 'destination' => $destination, 'formule' => $formula,
                'transport' => $transport, 'company' => $company, 'departure_city' => $from, 'arrived_city' => $to,
                'departure_date' => $this->now()->addDays($days)->toDateString(), 'return_date' => $this->now()->addDays($days + 10)->toDateString(),
                'nb_passagers_adultes' => $adults, 'nb_passagers_childs' => $children, 'nb_passagers_babies' => $babies,
                'selled' => $sold ? 1 : 0, 'is_vente' => $sold, 'proposed_price' => $price, 'marge' => round($price * 0.12),
                'bagage' => 'Avec', 'comment' => $i === 2 ? 'Rappeler le client la semaine prochaine pour valider les dates.' : null,
                'installation' => $transport === 'Bateau' ? 'Cabine 4 places' : null, 'vehicule' => $transport === 'Bateau' ? 'Voiture' : null,
            ]);
        }

        foreach ([['Italie', 'Vol seul', 'Paris', 'Milan', 35, 2, 0], ['Grèce', 'Vol + hôtel', 'Lyon', 'Athènes', 60, 2, 2]] as $i => [$destination, $formula, $from, $to, $days, $adults, $children]) {
            DB::table('client_requests')->insert([
                'agence_id' => $agency, 'gent_id' => $agents[$i], 'created_at' => $this->now()->subDays(4 - $i),
                'destination' => $destination, 'formule' => $formula, 'departure_city' => $from, 'arrived_city' => $to,
                'departure_date' => $this->now()->addDays($days)->toDateString(), 'return_date' => $this->now()->addDays($days + 7)->toDateString(),
                'nb_passagers_adultes' => $adults, 'nb_passagers_childs' => $children, 'nb_passagers_babies' => 0, 'bagage' => 1,
            ]);
        }

        foreach ([
            ['Paris', 'Rome', 25, 2, 'Sarah', 'Lambert'], ['Lyon', 'Lisbonne', 45, 4, 'Karim', 'Garnier'], ['Marseille', 'Athènes', 70, 1, 'Julien', 'Faure'],
        ] as $i => [$from, $to, $days, $passengers, $first, $last]) {
            DB::table('devis')->insert($this->stamps([
                'departure_city' => $from, 'arrival_city' => $to, 'departure_date' => $this->now()->addDays($days)->toDateString(),
                'return_date' => $this->now()->addDays($days + 7)->toDateString(), 'passengers_nb' => $passengers,
                'first_name' => $first, 'last_name' => $last, 'phone_num' => $this->phone(),
                'email' => strtolower($first) . '.' . strtolower($last) . '@example.com',
            ]));
        }
    }
}
