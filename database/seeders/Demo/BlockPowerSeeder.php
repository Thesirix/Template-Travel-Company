<?php

namespace Database\Seeders\Demo;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Allotements (options de places auprès des compagnies) : référentiels, options dans chaque statut,
 * passagers placés sur une option, demandes d'option, options relâchées et guide de l'agence.
 */
class BlockPowerSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        foreach ([
            'block_power_countries' => ['Italie', 'Grèce', 'Portugal', 'Canada'],
            'block_power_companies' => ['Sky Demo Airlines', 'Blue Wings', 'Maple Air'],
            'block_power_departure_cities' => ['Paris', 'Marseille', 'Lyon'],
            'block_power_arrival_cities' => ['Rome', 'Athènes', 'Lisbonne', 'Montréal'],
        ] as $table => $names) {
            foreach ($names as $name) {
                DB::table($table)->insert($this->stamps(['name' => $name]));
            }
        }

        $agent = $this->agentId('super.agent');
        $other = $this->agentId('agent');
        // Une option par statut ; l'option émise de date passée apparaît dans les archives
        $options = [
            ['available', 'Italie', 'Sky Demo Airlines', 'Paris', 'Rome', 30, 12, 5],
            ['expiring', 'Grèce', 'Blue Wings', 'Marseille', 'Athènes', 21, 8, 1],
            ['named', 'Portugal', 'Blue Wings', 'Lyon', 'Lisbonne', 25, 10, 6],
            ['confirmed', 'Italie', 'Sky Demo Airlines', 'Paris', 'Rome', 45, 6, 9],
            ['archived', 'Grèce', 'Blue Wings', 'Marseille', 'Athènes', -20, 4, -30],
            ['released', 'Canada', 'Maple Air', 'Paris', 'Montréal', 60, 9, 2],
            ['issued', 'Canada', 'Maple Air', 'Paris', 'Montréal', 70, 5, 3],
            ['expired', 'Portugal', 'Blue Wings', 'Lyon', 'Lisbonne', 10, 7, -2],
        ];
        $ids = [];
        foreach ($options as $i => [$status, $country, $company, $from, $to, $days, $seats, $optionDays]) {
            $ids[$status] = DB::table('block_power_options')->insertGetId($this->stamps([
                'agent_id' => $i % 2 ? $other : $agent, 'name' => "Groupe $to " . ($i + 1), 'status' => $status === 'archived' ? 'issued' : $status,
                'company' => $company, 'pnr' => 'AL' . str_pad((string) (7310 + $i), 4, '0', STR_PAD_LEFT),
                'country' => $country, 'departure_city' => $from, 'arrival_city' => $to,
                'date_aller' => $this->now()->addDays($days)->toDateString(), 'date_retour' => $this->now()->addDays($days + 10)->toDateString(),
                'seats' => $seats, 'cabin_class' => 'Économique', 'price' => 380 + 25 * $i,
                'option_date' => $this->now()->addDays($optionDays)->toDateString(),
                'pnr_created_on' => $this->now()->subDays(3)->toDateString(),
                'note' => $i === 0 ? 'Option posée pour les vacances de la Toussaint.' : null,
            ]));
        }

        // Passagers placés sur des options
        foreach (['named' => 3, 'confirmed' => 2, 'archived' => 2] as $status => $count) {
            for ($n = 1; $n <= $count; $n++) {
                DB::table('block_power_passengers')->insert($this->stamps([
                    'option_id' => $ids[$status], 'type' => ['Mrs', 'Mr', 'CHD'][$n - 1],
                    'last_name' => ['Dubois', 'Leroy', 'Robert'][$n - 1], 'first_name' => ['Marie', 'Paul', 'Léo'][$n - 1],
                    'sale_price' => 420 + 10 * $n, 'deposit' => 150, 'deposit_number' => (string) (510000 + $ids[$status] * 10 + $n),
                ]));
            }
        }
        DB::table('block_power_releases')->insert($this->stamps(['option_id' => $ids['released'], 'agent_id' => $agent]));

        foreach ([['Italie', 'Paris', 'Rome', 20, 'Économique', 450], ['Canada', 'Paris', 'Montréal', 15, 'Premium', 720]] as $i => [$country, $from, $to, $seats, $class, $target]) {
            DB::table('block_power_requests')->insert($this->stamps([
                'agent_id' => $agent, 'name' => "Demande $to", 'status' => 'available', 'company' => $i ? 'Maple Air' : 'Sky Demo Airlines',
                'pnr' => 'DM' . (880 + $i), 'country' => $country, 'departure_city' => $from, 'arrival_city' => $to,
                'date_aller' => $this->now()->addDays(90 + 10 * $i)->toDateString(), 'date_retour' => $this->now()->addDays(100 + 10 * $i)->toDateString(),
                'seats' => $seats, 'cabin_class' => $class, 'price' => $target + 50, 'option_date' => $this->now()->addDays(15)->toDateString(),
                'target_class' => $class, 'target_price' => $target,
            ]));
        }

        // Guide des allotements (PDF)
        $html = '<h1 style="font-family: sans-serif">Guide des allotements</h1>'
            . '<p style="font-family: sans-serif">1. Poser une option auprès de la compagnie (PNR, nombre de places, date limite).</p>'
            . '<p style="font-family: sans-serif">2. Placer les passagers vendus sur l\'option avant la date limite.</p>'
            . '<p style="font-family: sans-serif">3. Émettre les billets ou relâcher les places non vendues.</p>';
        Storage::disk('public')->put('guide/guide-allotements.pdf', Pdf::loadHTML($html)->output());
        DB::table('block_power_guides')->insert(['name' => 'guide-allotements.pdf', 'created_at' => $this->now()]);
    }
}
