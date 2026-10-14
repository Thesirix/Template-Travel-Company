<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Vols de l'administration : création (aller-retour avec escales), modification, copie et suppression.
 */
class FlightManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    private function payload(array $override = []): array
    {
        $airport = fn (string $code) => DB::table('air_ports')->where('abv', $code)->value('id');
        $go = now()->addMonths(3)->toDateString();
        $back = now()->addMonths(3)->addDays(10)->toDateString();

        return $override + [
            'destination' => 'italie',
            'group_id' => DB::table('vol_groups')->where('name', 'Italie - Automne')->value('id'),
            'company' => DB::table('companies')->where('nom', 'Sky Demo Airlines')->value('id'),
            'type_voyage' => 'aller_retour', 'type' => 0,
            'ville' => DB::table('villes')->where('nom', 'Rome')->value('id'),
            'condition' => DB::table('conditions')->value('id'),
            'place' => 30, 'pnr' => 'NEWPNR', 'bagage' => 1, 'soute' => 23, 'hand' => 8,
            'itineraire' => 'CDG - FCO', 'itineraire_color' => '#FCCF29', 'period_voyage' => '10 jours',
            'j_inscription' => 30, 'j_45' => 50, 'j_44_j_1' => 100, 'days_before_closure' => 2,
            'prix_comprend' => 'Vol et bagages', 'prix_comprend_pas' => 'Assurances', 'total_a_payer' => 200,
            'prix_achat_adult' => 300, 'prix_achat_enfant' => 250, 'prix_achat_bebe' => 60,
            'prix_vente_adult' => 420, 'prix_vente_enfant' => 360, 'prix_vente_bebe' => 90,
            'duration_aller' => '02:10', 'heur_depart_aller' => '07:00', 'heur_arriver_aller' => '09:10',
            'air_de_a_0' => $airport('CDG'), 'air_a_a_0' => $airport('MXP'), 'date_de_a_0' => $go,
            'heure_de_a_0' => '07:00', 'heure_a_a_0' => '08:30', 'modele_a_0' => 'A320',
            'air_de_a_1' => $airport('MXP'), 'air_a_a_1' => $airport('FCO'), 'date_de_a_1' => $go,
            'heure_de_a_1' => '09:30', 'heure_a_a_1' => '10:40', 'temp_a_1' => '01:00', 'modele_a_1' => 'A319',
            'duration_retour' => '02:10', 'heur_depart_retour' => '18:00', 'heur_arriver_retour' => '20:10',
            'air_de_r_0' => $airport('FCO'), 'air_a_r_0' => $airport('CDG'), 'date_de_r_0' => $back,
            'heure_de_r_0' => '18:00', 'heure_a_r_0' => '20:10', 'modele_r_0' => 'A320',
        ];
    }

    public function test_round_trip_flight_with_stops_is_created(): void
    {
        $this->post(route('admin.vols.store', 'italie'), $this->payload())
            ->assertRedirect(route('admin.vols', 'italie'))->assertSessionHasNoErrors();

        $flight = DB::table('vols')->where('pnr', 'NEWPNR')->first();
        $this->assertNotNull($flight);
        $this->assertSame(30, (int) $flight->place_disponible);
        $this->assertSame(now()->addMonths(3)->year, (int) $flight->y);
        $this->assertSame(2, DB::table('vol_escaliers')->where('vol_id', $flight->id)->where('type', 0)->count());
        $this->assertSame(1, DB::table('vol_escaliers')->where('vol_id', $flight->id)->where('type', 1)->count());
    }

    public function test_invalid_flight_is_rejected_with_errors(): void
    {
        $this->from(route('admin.vols.create', 'italie'))
            ->post(route('admin.vols.store', 'italie'), $this->payload(['prix_vente_adult' => '', 'date_de_r_0' => '']))
            ->assertRedirect(route('admin.vols.create', 'italie'))
            ->assertSessionHasErrors(['prix_vente_adult', 'date_de_r_0']);
    }

    public function test_flight_is_updated_and_keeps_its_reservations(): void
    {
        $flight = DB::table('vols')->where('destination', 'italie')->where('place_reserver', '>', 0)->first();
        $this->post(route('admin.vols.update', 'italie'), $this->payload(['vol_id' => $flight->id, 'place' => 45, 'pnr' => 'UPDPNR']))
            ->assertRedirect()->assertSessionHasNoErrors();

        $updated = DB::table('vols')->find($flight->id);
        $this->assertSame('UPDPNR', $updated->pnr);
        $this->assertSame(45 - $flight->place_reserver, (int) $updated->place_disponible);
        $this->assertSame((int) $flight->place_reserver, (int) $updated->place_reserver);
    }

    public function test_seats_cannot_go_below_sold_seats(): void
    {
        $flight = DB::table('vols')->where('place_reserver', '>', 2)->first();
        $this->post(route('admin.vols.update', 'italie'), $this->payload(['vol_id' => $flight->id, 'place' => 1]))
            ->assertSessionHasErrors('place');
    }

    public function test_copy_creates_a_new_flight(): void
    {
        $before = DB::table('vols')->count();
        $this->post(route('admin.vols.copy.store', 'italie'), $this->payload(['pnr' => 'COPPNR']))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($before + 1, DB::table('vols')->count());
    }

    public function test_flight_with_reservations_cannot_be_deleted_but_empty_one_can(): void
    {
        $sold = DB::table('vols')->where('place_reserver', '>', 0)->value('id');
        $this->post(route('admin.vols.destroy', 'italie'), ['vol_id' => $sold])->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('vols', ['id' => $sold]);

        $this->post(route('admin.vols.store', 'italie'), $this->payload(['pnr' => 'DELPNR']));
        $empty = DB::table('vols')->where('pnr', 'DELPNR')->value('id');
        $this->post(route('admin.vols.destroy', 'italie'), ['vol_id' => $empty])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('vols', ['id' => $empty]);
        $this->assertDatabaseMissing('vol_escaliers', ['vol_id' => $empty]);
    }
}
