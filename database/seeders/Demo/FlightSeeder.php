<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Groupes de vols, vols (à venir et archivés), escales, réservations, passagers, acomptes, historique,
 * une annulation et une inscription en liste d'attente. Tirage aléatoire fixe : démo identique à chaque installation.
 */
class FlightSeeder extends Seeder
{
    use SeedsDemoData;

    private const FIRST_NAMES = ['Emma', 'Lucas', 'Chloé', 'Hugo', 'Léa', 'Louis', 'Manon', 'Jules', 'Camille', 'Nathan', 'Inès', 'Tom'];
    private const LAST_NAMES = ['Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Lefèvre', 'Durand', 'Leroy', 'Moreau'];

    private array $airports = [];
    private int $condition;
    /** Code de suivi des réservations (même code pour toute la démo : hacher une seule fois) */
    private ?string $code = null;

    public function run(): void
    {
        mt_srand(42);
        foreach (DB::table('air_ports')->get() as $airport) {
            $this->airports[$airport->abv] = $airport;
        }
        $this->condition = (int) DB::table('conditions')->value('id');
        $country = fn (string $name) => (int) DB::table('countries')->where('nom', $name)->value('id');
        $company = fn (string $name) => (int) DB::table('companies')->where('nom', $name)->value('id');

        // Groupes aller-retour : un vol = un aller + son retour
        foreach ([
            ['italie', 'Italie - Automne', 'Italie', 'Sky Demo Airlines', 'CDG', 'FCO', [20, 34, 48]],
            ['grece', 'Grèce - Hiver', 'Grèce', 'Blue Wings', 'MRS', 'ATH', [15, 29]],
            ['portugal', 'Portugal - Hiver', 'Portugal', 'Blue Wings', 'LYS', 'LIS', [25]],
        ] as [$slug, $groupName, $countryName, $companyName, $from, $to, $departures]) {
            $group = $this->group($groupName, $country($countryName), 'round-trip');
            foreach ($departures as $i => $days) {
                $this->flight($group, $slug, $company($companyName), 'aller_retour', $from, $to, $days, 599 + 50 * $i);
            }
        }
        // Vol passé, archivé
        $italy = (int) DB::table('vol_groups')->where('name', 'Italie - Automne')->value('id');
        $this->flight($italy, 'italie', $company('Sky Demo Airlines'), 'aller_retour', 'CDG', 'FCO', -40, 549, archived: true);

        // Groupe « aller et retour séparés » : le client choisit son aller et son retour
        $canada = $this->group('Canada - Printemps', $country('Canada'), 'multi-segment-optional');
        foreach ([60, 67] as $days) {
            $this->flight($canada, 'canada', $company('Maple Air'), 'aller_simple', 'CDG', 'YUL', $days, 489);
        }
        foreach ([74, 81] as $days) {
            $this->flight($canada, 'canada', $company('Maple Air'), 'retour_simple', 'YUL', 'CDG', $days, 459);
        }

        $this->reservations();
    }

    private function group(string $name, int $countryId, string $type): int
    {
        return DB::table('vol_groups')->insertGetId([
            'name' => $name, 'country_id' => $countryId, 'type' => $type,
            'same_itineraire' => $type === 'round-trip', 'is_active' => true, 'created_at' => $this->now(),
        ]);
    }

    private function flight(int $group, string $slug, int $company, string $type, string $from, string $to, int $days, float $price, bool $archived = false): int
    {
        $outbound = $this->now()->addDays($days)->startOfDay();
        $return = $type === 'aller_retour' ? $outbound->copy()->addDays(14) : null;
        $volId = DB::table('vols')->insertGetId(array_filter($this->stamps([
            'vol_group_id' => $group, 'destination' => $slug, 'ville_id' => $this->airports[$type === 'retour_simple' ? $from : $to]->ville_id,
            'company_id' => $company, 'type_voyage' => $type, 'condition_id' => $this->condition,
            'date_aller' => $outbound->toDateString(), 'date_retour' => $return?->toDateString(),
            'y' => $outbound->year, 'm' => $outbound->month, 'd' => $outbound->day,
            'heur_depart_aller' => '10:00', 'heur_arriver_aller' => '18:30', 'duration_aller' => '08:30',
            'heur_depart_retour' => $return ? '20:00' : null, 'heur_arriver_retour' => $return ? '05:30' : null,
            'duration_retour' => $return ? '09:30' : null, 'period_voyage' => $return ? '15 jours' : ($type === 'aller_simple' ? 'Aller simple' : 'Retour simple'),
            'is_archive' => $archived, 'days_before_closure' => 3,
            'prix_achat_adult' => $price - 150, 'prix_achat_enfant' => $price - 220, 'prix_achat_bebe' => 90,
            'prix_vente_adult' => $price, 'prix_vente_enfant' => $price - 100, 'prix_vente_bebe' => 120,
            'total_a_payer' => 300, 'place_total' => 40, 'place_disponible' => 40, 'place_reserver' => 0,
            'place_attente' => 0, 'bagage' => 2, 'soute_num' => 23, 'bg_hand' => 8, 'maxbebe' => 4,
            'pnr' => strtoupper(Str::random(6)), 'itineraire' => "$from - $to", 'itineraire_color' => '#FCCF29',
            'j_inscription' => 30, 'j_45' => 50, 'j_44_j_1' => 100, 'j_0' => 100,
            'prix_comprend' => $return ? 'Vol aller-retour, 2 bagages en soute, taxes aéroport' : 'Vol simple, 2 bagages en soute, taxes aéroport',
            'prix_comprend_pas' => 'Assurances, dépenses personnelles',
        ]), fn ($value) => $value !== null));

        $legs = [[0, $from, $to, $outbound, '10:00', '18:30']];
        if ($return) {
            $legs[] = [1, $to, $from, $return, '20:00', '05:30'];
        }
        foreach ($legs as [$leg, $dep, $arr, $date, $start, $end]) {
            DB::table('vol_escaliers')->insert($this->stamps([
                'vol_id' => $volId, 'type' => $leg, 'num' => 1,
                'air_port_id' => $this->airports[$dep]->id, 're_air_port_id' => $this->airports[$arr]->id,
                'date_depart' => $date->toDateString(), 'heure_depart' => $start, 'heure_arriver' => $end, 'model' => 'Airbus A320',
            ]));
        }

        return $volId;
    }

    /** Réservations réparties sur les 6 derniers mois (tableaux de bord, graphiques, listes) */
    private function reservations(): void
    {
        $network = DB::table('agences')->where('is_partner_agency', false)->pluck('id');
        $agents = DB::table('gents')->where('state', 'enable')->whereIn('agence_id', $network)->orderBy('id')->pluck('id')->all();
        $partner = $this->agentId('partenaire');
        $roundTrips = DB::table('vols')->where('type_voyage', 'aller_retour')->where('is_archive', false)->get();
        $outbounds = DB::table('vols')->where('type_voyage', 'aller_simple')->get();
        $returns = DB::table('vols')->where('type_voyage', 'retour_simple')->get();
        $clientId = (int) DB::table('clients')->value('id');

        foreach (range(5, 0) as $monthsAgo) {
            $count = [5, 7, 6, 10, 9, 12][5 - $monthsAgo];
            for ($i = 0; $i < $count; $i++) {
                $createdAt = $this->now()->startOfMonth()->subMonths($monthsAgo)->addDays(mt_rand(0, 25))->setTime(mt_rand(9, 18), mt_rand(0, 59));
                if ($createdAt->isFuture()) {
                    $createdAt = $this->now()->subHours(mt_rand(1, 48));
                }
                $agent = $i % 7 === 6 ? $partner : $agents[mt_rand(0, count($agents) - 1)];
                if ($i % 5 === 4) {
                    $this->reservation($outbounds[mt_rand(0, $outbounds->count() - 1)], $returns[mt_rand(0, $returns->count() - 1)], $agent, $createdAt);
                } else {
                    $this->reservation($roundTrips[mt_rand(0, $roundTrips->count() - 1)], null, $agent, $createdAt, $i % 4 === 0 ? $clientId : null);
                }
            }
        }

        // Une réservation annulée (avec son motif) et une inscription en liste d'attente
        $cancelled = $this->reservation($roundTrips[0], null, $agents[0], $this->now()->subDays(20));
        DB::table('vol_reservations')->where('id', $cancelled)->update(['status' => false]);
        DB::table('vol_reservations_annules')->insert([
            'reservation_id' => $cancelled, 'montant' => 180, 'reason' => 'Annulation à la demande du client (raison personnelle)',
            'created_at' => $this->now()->subDays(18),
        ]);
        DB::table('vol_reservation_histories')->insert([
            'type' => 'Annuler la réservation', 'agent_id' => $agents[0], 'reservation_id' => $cancelled,
            'created_at' => $this->now()->subDays(18), 'details' => 'Frais d\'annulation : 180 €',
        ]);
        $waiting = $this->reservation($roundTrips[1], null, $agents[0], $this->now()->subDays(2));
        DB::table('vol_reservations')->where('id', $waiting)->update(['type_reservation' => 'liste_attente', 'prix_payed' => 0]);
    }

    private function reservation(object $flight, ?object $returnFlight, int $agent, $createdAt, ?int $client = null): int
    {
        $adults = mt_rand(1, 3);
        $children = mt_rand(0, 2);
        $babies = mt_rand(0, 6) === 0 ? 1 : 0;
        $priced = fn (object $f) => $adults * $f->prix_vente_adult + $children * $f->prix_vente_enfant + $babies * $f->prix_vente_bebe;
        $total = $priced($flight) + ($returnFlight ? $priced($returnFlight) : 0);
        $paid = round($total * [0.3, 0.5, 1][mt_rand(0, 2)], 2);
        $insurance = DB::table('assurences')->value('id');

        $id = DB::table('vol_reservations')->insertGetId([
            'vol_id' => $flight->id, 'vol_retour_id' => $returnFlight?->id, 'type_reservation' => 'Reservation',
            'type_voyage' => $returnFlight ? 'aller_retour_diffrent_vol' : 'aller_retour_same_vol',
            'type_payemant' => 'Carte bancaire', 'prix_total' => $total, 'prix_payed' => $paid,
            'nb_adult' => $adults, 'nb_enfant' => $children, 'nb_bebe' => $babies, 'gent_id' => $agent,
            'etat' => $paid >= $total ? 'Complet' : 'Incomplet', 'status' => 1, 'client_id' => $client,
            'code' => $this->code ??= Hash::make('123456'), 'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);

        $types = array_merge(array_fill(0, $adults, 'adulte'), array_fill(0, $children, 'enfant'), array_fill(0, $babies, 'bebe'));
        foreach ($types as $type) {
            $first = self::FIRST_NAMES[mt_rand(0, count(self::FIRST_NAMES) - 1)];
            $last = self::LAST_NAMES[mt_rand(0, count(self::LAST_NAMES) - 1)];
            $age = ['adulte' => mt_rand(22, 65), 'enfant' => mt_rand(3, 11), 'bebe' => 1][$type];
            $complete = mt_rand(0, 2) > 0;
            DB::table('vol_passagers')->insert([
                'reservation_id' => $id, 'assurance_id' => $insurance, 'type' => $type,
                'genre' => mt_rand(0, 1) ? 'male' : 'female', 'nom' => $last, 'prenom' => $first,
                'date_naissance' => $this->now()->subYears($age)->subDays(mt_rand(0, 300))->toDateString(),
                'passport' => 'P' . mt_rand(1000000, 9999999), 'expiration' => $this->now()->addYears(3)->toDateString(),
                'tele1' => $type === 'adulte' ? $this->phone() : null,
                'email' => $type === 'adulte' ? Str::ascii(strtolower($first . '.' . $last)) . '@example.com' : null,
                'dossier_etat' => $complete ? 'Complet' : 'Incomplet', 'verifier' => $complete && mt_rand(0, 1) ? 'OUI' : 'NON',
                'created_at' => $createdAt, 'updated_at' => $createdAt,
            ]);
        }

        DB::table('vol_reservation_histories')->insert([
            'type' => 'Créer une réservation', 'agent_id' => $agent, 'reservation_id' => $id, 'created_at' => $createdAt, 'details' => null,
        ]);
        DB::table('vol_acomptes')->insert([
            'reservation_id' => $id, 'montant' => $paid, 'agent_id' => $agent, 'n_acompte' => (string) (400000 + $id), 'created_at' => $createdAt,
        ]);
        DB::table('vol_reservation_histories')->insert([
            'type' => 'Ajouter un paiement N Acompte ' . (400000 + $id), 'agent_id' => $agent, 'reservation_id' => $id,
            'created_at' => $createdAt, 'details' => 'Montant : ' . number_format($paid, 2, ',', ' ') . ' €',
        ]);

        $seats = $adults + $children;
        foreach (array_filter([$flight->id, $returnFlight?->id]) as $flightId) {
            DB::table('vols')->where('id', $flightId)->update([
                'place_disponible' => DB::raw('place_disponible - ' . $seats),
                'place_reserver' => DB::raw('place_reserver + ' . $seats),
            ]);
        }

        return $id;
    }
}
