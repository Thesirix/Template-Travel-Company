<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Bateau : catalogue (destinations, ports, compagnies, installations, tarifs, véhicules, assurances)
 * et demandes de traversée dans chaque état (à traiter, confirmée, vendue, bon d'échange).
 */
class BoatSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        mt_srand(7);
        $name = fn (string $table, array $values, array $extra = []) => array_map(
            fn ($v) => DB::table($table)->insertGetId(['name' => $v, 'created_at' => $this->now()] + $extra), $values);

        [$corsica, $sardinia] = $name('boat_destinations', ['Corse', 'Sardaigne']);
        $departures = [
            $corsica => DB::table('boat_departure_ports')->insertGetId(['name' => 'Marseille', 'destination_id' => $corsica, 'created_at' => $this->now()]),
            $sardinia => DB::table('boat_departure_ports')->insertGetId(['name' => 'Toulon', 'destination_id' => $sardinia, 'created_at' => $this->now()]),
        ];
        DB::table('boat_departure_ports')->insert(['name' => 'Nice', 'destination_id' => $corsica, 'created_at' => $this->now()]);
        $arrivals = [
            $corsica => DB::table('boat_arrival_ports')->insertGetId(['name' => 'Ajaccio', 'destination_id' => $corsica, 'created_at' => $this->now()]),
            $sardinia => DB::table('boat_arrival_ports')->insertGetId(['name' => 'Porto Torres', 'destination_id' => $sardinia, 'created_at' => $this->now()]),
        ];
        DB::table('boat_arrival_ports')->insert(['name' => 'Bastia', 'destination_id' => $corsica, 'created_at' => $this->now()]);
        $companies = [
            $corsica => DB::table('boat_companies')->insertGetId(['name' => 'Mer Bleue Ferries', 'destination_id' => $corsica, 'created_at' => $this->now()]),
            $sardinia => DB::table('boat_companies')->insertGetId(['name' => 'Navi del Sud', 'destination_id' => $sardinia, 'created_at' => $this->now()]),
        ];
        $installations = $name('boat_accommodations', ['Fauteuil', 'Cabine 2 places', 'Cabine 4 places']);
        $fares = $name('boat_fares', ['Tarif standard', 'Tarif flexible']);
        $sizes = $name('boat_vehicle_sizes', ['Moins de 1,80 m de haut', 'Plus de 1,80 m de haut']);
        $vehicles = $name('boat_vehicles', ['Voiture', 'Moto', 'Camping-car']);
        $passengerInsurances = $name('boat_passenger_insurances', ['Sans assurance', 'Assurance annulation']);
        $vehicleInsurances = $name('boat_vehicle_insurances', ['Sans assurance', 'Assistance véhicule']);

        $agents = [$this->agentId('super.agent'), $this->agentId('agent')];
        $bookings = [
            // [destination, trajet, jours avant départ, véhicule ?, état de la réservation]
            [$corsica, 'aller-retour', 25, true, null],
            [$sardinia, 'aller-simple', 40, false, null],
            [$corsica, 'aller-retour', 18, true, 'confirmer'],
            [$corsica, 'aller-retour', 32, false, 'confirmer'],
            [$sardinia, 'aller-retour', 12, true, 'final'],
            [$corsica, 'aller-simple', 50, false, 'echange'],
        ];
        foreach ($bookings as $i => [$destination, $route, $days, $withVehicle, $status]) {
            $go = $this->now()->addDays($days);
            $booking = DB::table('boat_bookings')->insertGetId([
                'trajet' => $route, 'destination_id' => $destination, 'departure_port_id' => $departures[$destination],
                'arrival_port_id' => $route === 'aller-retour' ? $arrivals[$destination] : null, 'company_id' => $companies[$destination],
                'accommodation_id' => $installations[$i % 3], 'fare_id' => $fares[$i % 2],
                'date_aller' => $go->toDateString(), 'date_retour' => $route === 'aller-retour' ? $go->copy()->addDays(10)->toDateString() : null,
                'nb_adulte' => 2, 'nb_senior' => $i % 2, 'nb_jeune' => 0, 'nb_enfant' => $i % 3 === 0 ? 1 : 0, 'nb_bebe' => 0,
                'vehicle_id' => $withVehicle ? $vehicles[$i % 3] : null, 'vehicle_size_id' => $withVehicle ? $sizes[$i % 2] : null,
                'voiture_marque' => $withVehicle ? 'Renault' : null, 'voiture_modele' => $withVehicle ? 'Scénic' : null,
                'voiture_matricule' => $withVehicle ? 'AB-' . (100 + $i) . '-CD' : null,
                'voiture_anne' => $withVehicle ? $this->now()->subYears(4)->toDateString() : null,
                'vehicle_insurance_id' => $withVehicle ? $vehicleInsurances[1] : null, 'insurance_id' => $passengerInsurances[$i % 2],
                'note' => $i === 0 ? 'Le client souhaite une cabine côté mer si possible.' : null,
                'gent_id' => $agents[$i % 2], 'created_at' => $this->now()->subDays(10 - $i)->toDateString(),
            ]);

            $people = [['Adulte', 'Martin', 'Paul', 42, 1], ['Adulte', 'Martin', 'Claire', 39, 0]];
            if ($i % 2) {
                $people[] = ['Senior', 'Martin', 'Jacques', 71, 0];
            }
            if ($i % 3 === 0) {
                $people[] = ['Enfant', 'Martin', 'Lina', 9, 0];
            }
            foreach ($people as [$type, $last, $first, $age, $responsible]) {
                DB::table('boat_passengers')->insert([
                    'respo' => $responsible, 'nom' => $last, 'prenom' => $first, 'type' => $type,
                    'passport' => null, 'passport_num' => 'P' . mt_rand(1000000, 9999999),
                    'date_naissance' => $this->now()->subYears($age)->toDateString(),
                    'mail' => $responsible ? 'paul.martin@example.com' : null, 'tele' => $responsible ? $this->phone() : null,
                    'booking_id' => $booking, 'created_at' => $this->now(), 'ageA' => $age, 'ageR' => $route === 'aller-retour' ? $age : null,
                ]);
            }

            if ($status) {
                DB::table('boat_reservations')->insert($this->stamps([
                    'etat' => $status, 'booking_id' => $booking, 'num_reservation' => 'BT' . (2024000 + $booking),
                    'date_option' => $this->now()->addDays(5)->toDateString(), 'prix' => 420 + 60 * $i,
                    'details' => $status === 'echange' ? 'Bon d\'échange émis suite à un report de traversée.' : null,
                ]));
            }
        }
    }
}
