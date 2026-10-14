<?php

namespace Tests\Feature\Agent;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Suivi d'une réservation de vol : fiche, ajout de passager, annulations, changement de vol,
 * acomptes, gestion des dossiers et émission.
 */
class FlightReservationTest extends TestCase
{
    private object $reservation;

    private int $group;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->actingAsAgent('super.agent');
        $this->group = DB::table('vol_groups')->where('type', 'round-trip')->orderBy('id')->value('id');
        $this->reservation = DB::table('vol_reservations')->join('vols', 'vols.id', '=', 'vol_reservations.vol_id')
            ->where('vols.vol_group_id', $this->group)->where('vol_reservations.status', 1)
            ->where('vol_reservations.type_voyage', 'aller_retour_same_vol')->where('vol_reservations.nb_adult', '>=', 2)
            ->select('vol_reservations.*')->first();
    }

    private function seatsLeft(): int
    {
        return (int) DB::table('vols')->where('id', $this->reservation->vol_id)->value('place_disponible');
    }

    public function test_reservation_page_and_list(): void
    {
        $this->get(route('agent.vols.reservations', $this->group))->assertOk();
        $this->get(route('agent.vols.reservations.show', [$this->group, $this->reservation->id]))->assertOk();
        $this->get(route('agent.vols.dossiers.show', [$this->group, $this->reservation->id]))->assertOk();
        $this->get(route('agent.vols.emission.reservation', [$this->group, $this->reservation->id]))->assertOk();
    }

    public function test_reservation_of_another_destination_is_not_found(): void
    {
        $other = DB::table('vol_groups')->where('id', '!=', $this->group)->value('id');
        $this->get(route('agent.vols.reservations.show', [$other, $this->reservation->id]))->assertNotFound();
    }

    public function test_update_keeps_insurance_and_adds_a_passenger(): void
    {
        $passenger = DB::table('vol_passagers')->where('reservation_id', $this->reservation->id)->where('type', 'adulte')->first();
        DB::table('vol_passagers')->where('id', $passenger->id)->update(['assurance_id' => DB::table('assurences')->value('id')]);
        $insurance = DB::table('vol_passagers')->where('id', $passenger->id)->value('assurance_id');
        $seats = $this->seatsLeft();
        $key = fn ($field) => "old_adulte_{$field}_{$passenger->id}";

        $this->from(route('agent.vols.reservations.show', [$this->group, $this->reservation->id]))
            ->post(route('agent.vols.reservations.update', $this->group), [
                'reservation_id' => $this->reservation->id, 'billNumber' => '880001',
                'nb_new_adult' => 1, 'nb_new_enfant' => 0, 'nb_new_bebe' => 0,
                $key('nom') => 'Moreau', $key('prenom') => $passenger->prenom, $key('date_naissance') => $passenger->date_naissance,
                $key('verifier') => 'on',
                'passagers' => [['type' => 'adult', 'genre' => 'male', 'nom' => 'Nouveau', 'prenom' => 'Paul', 'date_naissance' => '1980-05-05',
                    'tele1' => '', 'tele2' => '', 'email' => '', 'assistance' => '', 'observation' => '', 'dossier_etat' => 'Incomplet',
                    'assurance' => DB::table('assurences')->value('id')]],
            ])->assertRedirect()->assertSessionHas('success');

        $updated = DB::table('vol_passagers')->where('id', $passenger->id)->first();
        $this->assertSame('Moreau', $updated->nom);
        $this->assertSame('Oui', $updated->verifier);
        $this->assertEquals($insurance, $updated->assurance_id, "l'assurance ne doit pas être effacée");
        $this->assertDatabaseHas('vol_passagers', ['reservation_id' => $this->reservation->id, 'nom' => 'Nouveau', 'type' => 'adulte']);
        $this->assertSame($this->reservation->nb_adult + 1, (int) DB::table('vol_reservations')->where('id', $this->reservation->id)->value('nb_adult'));
        $this->assertSame($seats - 1, $this->seatsLeft());
        $this->assertSame('880001', (string) DB::table('vol_acomptes')->where('reservation_id', $this->reservation->id)->orderBy('created_at')->value('n_acompte'));
    }

    public function test_update_is_refused_without_seats(): void
    {
        DB::table('vols')->where('id', $this->reservation->vol_id)->update(['place_disponible' => 0]);
        $this->from(route('agent.vols.reservations.show', [$this->group, $this->reservation->id]))
            ->post(route('agent.vols.reservations.update', $this->group), [
                'reservation_id' => $this->reservation->id, 'nb_new_adult' => 1,
                'passagers' => [['type' => 'adult', 'nom' => 'X', 'prenom' => 'Y', 'date_naissance' => '1980-01-01', 'assurance' => DB::table('assurences')->value('id')]],
            ])->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseMissing('vol_passagers', ['reservation_id' => $this->reservation->id, 'nom' => 'X']);
    }

    public function test_cancel_one_passenger_then_whole_reservation(): void
    {
        $seats = $this->seatsLeft();
        $passenger = DB::table('vol_passagers')->where('reservation_id', $this->reservation->id)->where('type', 'adulte')->first();

        $this->from(route('agent.vols.reservations.show', [$this->group, $this->reservation->id]))
            ->post(route('agent.vols.reservations.cancel-passenger', $this->group), [
                'reservation_id' => $this->reservation->id, 'passager_id' => $passenger->id, 'all_annulation_reason' => 'Raison personnelle',
            ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('vol_passagers', ['id' => $passenger->id]);
        $this->assertDatabaseHas('vol_reservations_annules', ['reservation_id' => $this->reservation->id, 'reason' => 'Raison personnelle']);
        $this->assertSame($seats + 1, $this->seatsLeft());

        $remaining = DB::table('vol_reservations')->where('id', $this->reservation->id)->first();
        $this->post(route('agent.vols.reservations.cancel', $this->group), [
            'reservation_id' => $this->reservation->id, 'all_annulation_reason' => 'Voyage annulé',
        ])->assertRedirect(route('agent.vols.reservations', $this->group));
        $this->assertSame(0, (int) DB::table('vol_reservations')->where('id', $this->reservation->id)->value('status'));
        $this->assertSame($seats + 1 + $remaining->nb_adult + $remaining->nb_enfant, $this->seatsLeft());
        Mail::assertSent(\App\Mail\Flight\ReservationCanceledMail::class);
    }

    public function test_change_flight(): void
    {
        $other = DB::table('vols')->where('vol_group_id', $this->group)->where('id', '!=', $this->reservation->vol_id)
            ->where('type_voyage', 'aller_retour')->where('place_disponible', '>', 10)->first();
        $seats = $this->reservation->nb_adult + $this->reservation->nb_enfant;
        $oldSeats = $this->seatsLeft();

        $this->get(route('agent.vols.reservations.change-flight', [$this->group, $this->reservation->id]))->assertOk();
        $this->get(route('agent.vols.reservations.change-flight.preview', $this->group) . '?' . http_build_query(['vol_id' => $other->id, 'reservation_id' => $this->reservation->id]))
            ->assertOk();
        $this->post(route('agent.vols.reservations.change-flight.apply', $this->group), [
            'reservation_id' => $this->reservation->id, 'vol_id' => $other->id, 'type_voyage' => 'aller_retour_same_vol',
        ])->assertRedirect(route('agent.vols.reservations.show', [$this->group, $this->reservation->id]));

        $this->assertSame($other->id, (int) DB::table('vol_reservations')->where('id', $this->reservation->id)->value('vol_id'));
        $this->assertSame($oldSeats + $seats, (int) DB::table('vols')->where('id', $this->reservation->vol_id)->value('place_disponible'));
        $this->assertSame($other->place_disponible - $seats, (int) DB::table('vols')->where('id', $other->id)->value('place_disponible'));
        $this->assertDatabaseHas('vol_reservation_histories', ['reservation_id' => $this->reservation->id, 'type' => 'Modifier la date']);
    }

    public function test_passenger_documents_upload_and_delete(): void
    {
        Storage::fake('public');
        $passenger = DB::table('vol_passagers')->where('reservation_id', $this->reservation->id)->first();
        $key = fn ($field) => "old_{$passenger->type}_{$field}_{$passenger->id}";

        $this->post(route('agent.vols.dossiers.update', $this->group), [
            'reservation_id' => $this->reservation->id, $key('nom') => $passenger->nom, $key('dossier_etat') => 'Complet',
            $key('passport_file') => UploadedFile::fake()->create('passeport.pdf', 50, 'application/pdf'),
        ])->assertRedirect(route('agent.vols.dossiers.show', [$this->group, $this->reservation->id]));
        $image = DB::table('vol_passagers')->where('id', $passenger->id)->value('passport_image');
        $this->assertSame($passenger->id . '.pdf', $image);
        Storage::disk('public')->assertExists('passagers/passports/' . $image);
        $this->assertSame('Complet', DB::table('vol_passagers')->where('id', $passenger->id)->value('dossier_etat'));

        $this->post(route('agent.vols.reservations.passport.destroy', $this->group), ['passenger_id' => $passenger->id, 'file_type' => 'passport'])
            ->assertRedirect()->assertSessionHas('success');
        Storage::disk('public')->assertMissing('passagers/passports/' . $image);
        $this->assertNull(DB::table('vol_passagers')->where('id', $passenger->id)->value('passport_image'));
    }

    public function test_deposits(): void
    {
        $paid = (float) DB::table('vol_reservations')->where('id', $this->reservation->id)->value('prix_payed');

        $this->post(route('agent.vols.paiements.store', $this->group), ['reservation_id' => $this->reservation->id, 'n_acompte' => '990001', 'montant' => 150])
            ->assertRedirect()->assertSessionHas('success');
        $deposit = DB::table('vol_acomptes')->where('n_acompte', '990001')->first();
        $this->assertEquals($paid + 150, DB::table('vol_reservations')->where('id', $this->reservation->id)->value('prix_payed'));

        $this->post(route('agent.vols.paiements.update', $this->group), ['acompte_id' => $deposit->id, 'n_acompte' => '990001', 'montant' => 200, 'created_at' => now()->toDateString()])
            ->assertRedirect();
        $this->assertEquals($paid + 200, DB::table('vol_reservations')->where('id', $this->reservation->id)->value('prix_payed'));

        $this->post(route('agent.vols.paiements.destroy', $this->group), ['delete_acompte_id' => $deposit->id])->assertRedirect();
        $this->assertDatabaseMissing('vol_acomptes', ['id' => $deposit->id]);
        $this->assertEquals($paid, DB::table('vol_reservations')->where('id', $this->reservation->id)->value('prix_payed'));
        $this->assertSame(3, DB::table('vol_reservation_histories')->where('reservation_id', $this->reservation->id)->where('details', 'like', '990001%')->count());
    }

    public function test_issuance(): void
    {
        $passenger = DB::table('vol_passagers')->where('reservation_id', $this->reservation->id)->first();
        $agent = DB::table('gents')->where('email', 'agent@travel-company.test')->value('id');

        $this->get(route('agent.vols.emission', $this->group))->assertOk();
        $this->get(route('agent.vols.emission.flight', [$this->group, $this->reservation->vol_id]))->assertOk();
        $this->post(route('agent.vols.emission.verify', $this->group), ['passager_verifer_id' => $passenger->id])->assertRedirect();
        $this->assertSame('Oui', DB::table('vol_passagers')->where('id', $passenger->id)->value('verifier'));

        $this->post(route('agent.vols.emission.reservation.update', $this->group), [
            'reservation_id' => $this->reservation->id, 'billNumber' => '770001', 'reservationAgent' => $agent,
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame($agent, (int) DB::table('vol_reservations')->where('id', $this->reservation->id)->value('gent_id'));

        foreach ([null, 'amadeus', 'ethiopian', 'volotea'] as $format) {
            $route = $format ? route('agent.vols.emission.export.format', [$this->group, $format]) : route('agent.vols.emission.export', $this->group);
            $response = $this->post($route, ['vol_id' => $this->reservation->vol_id]);
            $response->assertOk();
            $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'), (string) $format);
        }
    }

    public function test_issuance_updates_passengers(): void
    {
        $passenger = DB::table('vol_passagers')->where('reservation_id', $this->reservation->id)->where('type', 'adulte')->first();
        $key = fn ($field) => "old_adulte_{$field}_{$passenger->id}";

        $this->from(route('agent.vols.emission.reservation', [$this->group, $this->reservation->id]))
            ->post(route('agent.vols.emission.passengers.update', $this->group), [
                'reservation_id' => $this->reservation->id,
                $key('nom') => 'Lefèvre', $key('prenom') => $passenger->prenom, $key('date_naissance') => $passenger->date_naissance,
                $key('ref_aller') => 'TKT-0001',
            ])->assertRedirect()->assertSessionHas('success');

        $updated = DB::table('vol_passagers')->where('id', $passenger->id)->first();
        $this->assertSame('Lefèvre', $updated->nom);
        $this->assertSame('TKT-0001', $updated->ref_aller);
    }

    public function test_only_an_admin_can_delete_a_reservation_and_seats_are_released(): void
    {
        $this->post(route('agent.vols.reservations.destroy', $this->group), ['reservation_id' => $this->reservation->id])->assertForbidden();

        $seats = $this->seatsLeft();
        $this->actingAsAdmin();
        $this->post(route('agent.vols.reservations.destroy', $this->group), ['reservation_id' => $this->reservation->id])
            ->assertRedirect(route('agent.vols.reservations', $this->group));
        $this->assertDatabaseMissing('vol_reservations', ['id' => $this->reservation->id]);
        $this->assertDatabaseMissing('vol_passagers', ['reservation_id' => $this->reservation->id]);
        $this->assertSame($seats + $this->reservation->nb_adult + $this->reservation->nb_enfant, $this->seatsLeft());
    }

    public function test_partner_agency_cannot_open_another_agency_reservation(): void
    {
        $this->actingAsAgent('partenaire');
        $this->get(route('agent.vols.reservations.show', [$this->group, $this->reservation->id]))->assertForbidden();
    }
}
