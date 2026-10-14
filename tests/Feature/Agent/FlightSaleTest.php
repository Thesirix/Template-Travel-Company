<?php

namespace Tests\Feature\Agent;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Vente d'un vol par un agent : réservation, passagers, acompte, historique, places, e-mails.
 */
class FlightSaleTest extends TestCase
{
    private function passenger(string $type, string $first, array $extra = []): array
    {
        return $extra + [
            'type' => $type, 'genre' => 'female', 'nom' => 'Lambert', 'prenom' => $first,
            'date_naissance' => now()->subYears($type === 'adulte' ? 35 : 8)->toDateString(),
            'tele1' => $type === 'adulte' ? '06 12 34 56 78' : '', 'tele2' => '', 'assistance' => '', 'observation' => '',
            'email' => $type === 'adulte' ? strtolower($first) . '@example.com' : '', 'assurance' => DB::table('assurences')->value('id'),
            'passport' => 'P' . random_int(1000000, 9999999), 'expiration' => now()->addYears(5)->toDateString(),
            'sejour' => '', 'sejour_expiration' => null, 'dossier_etat' => 'Complet',
        ];
    }

    public function test_agent_sells_a_round_trip_flight(): void
    {
        Mail::fake();
        $agent = $this->actingAsAgent('agent');
        $flight = DB::table('vols')->where('type_voyage', 'aller_retour')->where('is_archive', false)->first();
        $reservations = DB::table('vol_reservations')->count();

        $response = $this->post(route('agent.vols.vente.store', $flight->vol_group_id), [
            'vol_id' => $flight->id, 'type_voyage' => 'aller_retour_same_vol', 'type_reservation' => 'Reservation',
            'adultcountinput2' => 2, 'enfantcountinput2' => 1, 'bebecountinput2' => 0,
            'num_acompte' => '700001', 'montant_acompte' => 500,
            'passagers' => [$this->passenger('adulte', 'Sarah'), $this->passenger('adulte', 'Marc'), $this->passenger('enfant', 'Léo')],
        ]);

        $reservation = DB::table('vol_reservations')->orderByDesc('id')->first();
        $this->assertSame($reservations + 1, DB::table('vol_reservations')->count());
        $response->assertRedirect(route('agent.vols.vente.confirmation', [$flight->vol_group_id, $reservation->id]));
        $this->assertSame($agent->id, (int) $reservation->gent_id);
        $this->assertSame(3, DB::table('vol_passagers')->where('reservation_id', $reservation->id)->count());
        $this->assertDatabaseHas('vol_acomptes', ['reservation_id' => $reservation->id, 'n_acompte' => '700001', 'montant' => 500]);
        $this->assertDatabaseHas('vol_reservation_histories', ['reservation_id' => $reservation->id, 'type' => 'Créer une réservation']);
        $this->assertSame($flight->place_disponible - 3, (int) DB::table('vols')->where('id', $flight->id)->value('place_disponible'));

        $this->get(route('agent.vols.vente.confirmation', [$flight->vol_group_id, $reservation->id]))->assertOk()->assertSee('Lambert')->assertSee('Léo');
        $this->get(route('agent.vols.vente.e-billet', [$flight->vol_group_id, $reservation->id]))->assertOk();
        $this->get(route('agent.vols.vente.contrat', [$flight->vol_group_id, $reservation->id]))->assertOk();
    }

    public function test_sale_is_refused_when_seats_are_missing(): void
    {
        $this->actingAsAgent('agent');
        $flight = DB::table('vols')->where('type_voyage', 'aller_retour')->where('is_archive', false)->first();
        DB::table('vols')->where('id', $flight->id)->update(['place_disponible' => 1]);
        $before = DB::table('vol_reservations')->count();

        $this->from(route('agent.vols.vente', $flight->vol_group_id))->post(route('agent.vols.vente.store', $flight->vol_group_id), [
            'vol_id' => $flight->id, 'type_voyage' => 'aller_retour_same_vol', 'type_reservation' => 'Reservation',
            'adultcountinput2' => 2, 'enfantcountinput2' => 0, 'bebecountinput2' => 0, 'num_acompte' => '1', 'montant_acompte' => 0,
            'passagers' => [$this->passenger('adulte', 'Sarah'), $this->passenger('adulte', 'Marc')],
        ])->assertRedirect(route('agent.vols.vente', $flight->vol_group_id));

        $this->assertSame($before, DB::table('vol_reservations')->count());
        $this->assertSame(0, DB::transactionLevel() - 1, 'transaction restée ouverte');
    }

    public function test_one_way_outbound_and_return_flights_can_be_combined(): void
    {
        Mail::fake();
        $this->actingAsAgent('super.agent');
        $outbound = DB::table('vols')->where('type_voyage', 'aller_simple')->first();
        $return = DB::table('vols')->where('type_voyage', 'retour_simple')->first();

        $this->post(route('agent.vols.vente.store', $outbound->vol_group_id), [
            'vol_id' => $outbound->id, 'vol_retour_id' => $return->id, 'type_voyage' => 'aller_retour_diffrent_vol',
            'type_reservation' => 'Reservation', 'adultcountinput2' => 1, 'enfantcountinput2' => 0, 'bebecountinput2' => 0,
            'num_acompte' => '700002', 'montant_acompte' => 300, 'passagers' => [$this->passenger('adulte', 'Nora')],
        ])->assertRedirect();

        $reservation = DB::table('vol_reservations')->orderByDesc('id')->first();
        $this->assertSame($return->id, (int) $reservation->vol_retour_id);
        $this->assertSame($return->place_disponible - 1, (int) DB::table('vols')->where('id', $return->id)->value('place_disponible'));
    }
}
