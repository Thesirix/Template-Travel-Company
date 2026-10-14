<?php

namespace Tests\Feature\Agent;

use App\Mail\Boat\BookingMail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Traversée en bateau : demande, modification, réservation, vente ou bon d'échange, listes et export.
 */
class BoatBookingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
        $this->actingAsAgent('super.agent');
    }

    private function bookingFields(array $override = []): array
    {
        $destination = DB::table('boat_destinations')->orderBy('id')->value('id');

        return $override + [
            'trajet' => 'aller-retour', 'destination_id' => $destination,
            'departure_port_id' => DB::table('boat_departure_ports')->where('destination_id', $destination)->value('id'),
            'arrival_port_id' => DB::table('boat_arrival_ports')->where('destination_id', $destination)->value('id'),
            'company_id' => DB::table('boat_companies')->where('destination_id', $destination)->value('id'),
            'accommodation_id' => DB::table('boat_accommodations')->value('id'), 'fare_id' => DB::table('boat_fares')->value('id'),
            'insurance_id' => DB::table('boat_passenger_insurances')->value('id'),
            'date_aller' => now()->addDays(30)->toDateString(), 'date_retour' => now()->addDays(40)->toDateString(),
            'note' => 'Cabine côté mer',
        ];
    }

    private function newPassenger(string $type, string $first, int $age, array $extra = []): array
    {
        return $extra + ['type' => $type, 'nom' => 'Durand', 'prenom' => $first, 'passport_num' => 'P' . random_int(100000, 999999),
            'date_naissance' => now()->addDays(30)->subYears($age)->subMonths(2)->toDateString()];
    }

    private function createBooking(): int
    {
        $this->post(route('agent.bateau.store'), $this->bookingFields([
            'vehicle_id' => DB::table('boat_vehicles')->value('id'), 'vehicle_size_id' => DB::table('boat_vehicle_sizes')->value('id'),
            'vehicle_insurance_id' => DB::table('boat_vehicle_insurances')->value('id'), 'voiture_marque' => 'Peugeot',
            'voiture_modele' => '308', 'voiture_matricule' => 'XY-123-ZZ', 'voiture_anne' => '2020-03-01',
            'carte_grise' => UploadedFile::fake()->create('carte-grise.pdf', 40, 'application/pdf'),
            'responsable' => 'new:0',
            'nouveaux' => [
                $this->newPassenger('adulte', 'Hugo', 40, ['tele' => '0612345678', 'mail' => 'hugo@example.com',
                    'passport' => UploadedFile::fake()->create('passeport.pdf', 30, 'application/pdf')]),
                $this->newPassenger('enfant', 'Emma', 8),
            ],
        ]))->assertRedirect(route('agent.bateau.pending'))->assertSessionHasNoErrors();

        return DB::table('boat_bookings')->orderByDesc('id')->value('id');
    }

    public function test_create_booking_with_vehicle_and_documents(): void
    {
        $this->get(route('agent.bateau.create'))->assertOk();
        $id = $this->createBooking();

        $booking = DB::table('boat_bookings')->find($id);
        $this->assertSame(1, (int) $booking->nb_adulte);
        $this->assertSame(1, (int) $booking->nb_enfant);
        Storage::disk('public')->assertExists($booking->carte_grise);
        $hugo = DB::table('boat_passengers')->where('booking_id', $id)->where('prenom', 'Hugo')->first();
        $this->assertSame(1, (int) $hugo->respo);
        $this->assertSame('Adulte', $hugo->type);
        $this->assertSame(40, (int) $hugo->ageA);
        Storage::disk('public')->assertExists($hugo->passport);
        Mail::assertSent(BookingMail::class, fn ($mail) => ! $mail->confirmed && $mail->hasTo('hugo@example.com'));

        $this->get(route('agent.bateau.show', $id))->assertOk()->assertSee('Hugo')->assertSee('Carte grise enregistrée');
        $this->get(route('agent.bateau.pending'))->assertOk()->assertSee('Durand Hugo');
    }

    public function test_validation_ages_duplicates_and_missing_passengers(): void
    {
        $this->post(route('agent.bateau.store'), $this->bookingFields())->assertSessionHasErrors('nouveaux');
        $this->post(route('agent.bateau.store'), $this->bookingFields(['nouveaux' => [$this->newPassenger('senior', 'Trop', 30)]]))
            ->assertSessionHasErrors('nouveaux.0.date_naissance');
        $this->post(route('agent.bateau.store'), $this->bookingFields(['trajet' => 'aller-retour', 'date_retour' => '']))->assertSessionHasErrors('date_retour');

        $this->createBooking();
        $count = DB::table('boat_bookings')->count();
        $this->post(route('agent.bateau.store'), $this->bookingFields(['responsable' => 'new:0',
            'nouveaux' => [$this->newPassenger('adulte', 'Hugo', 40, ['tele' => '0612345678'])]]))->assertSessionHas('error');
        $this->assertSame($count, DB::table('boat_bookings')->count());
    }

    public function test_edit_adds_and_removes_passengers(): void
    {
        $id = $this->createBooking();
        $emma = DB::table('boat_passengers')->where('booking_id', $id)->where('prenom', 'Emma')->first();
        $hugo = DB::table('boat_passengers')->where('booking_id', $id)->where('prenom', 'Hugo')->first();

        $this->get(route('agent.bateau.edit', $id))->assertOk();
        $this->post(route('agent.bateau.update', $id), $this->bookingFields([
            'trajet' => 'aller-simple',
            'passengers' => [$hugo->id => ['nom' => 'Durand-Petit', 'prenom' => 'Hugo', 'date_naissance' => $hugo->date_naissance, 'tele' => '0699999999']],
            'nouveaux' => [$this->newPassenger('senior', 'Marie', 70)],
            'responsable' => "existing:$hugo->id",
        ]))->assertRedirect(route('agent.bateau.show', $id))->assertSessionHasNoErrors();

        $booking = DB::table('boat_bookings')->find($id);
        $this->assertNull($booking->date_retour);
        $this->assertNull($booking->arrival_port_id);
        $this->assertNull($booking->vehicle_id, 'sans véhicule choisi, le véhicule est retiré');
        $this->assertSame(1, (int) $booking->nb_senior);
        $this->assertSame('Durand-Petit', DB::table('boat_passengers')->where('id', $hugo->id)->value('nom'));

        $this->post(route('agent.bateau.passenger.destroy', $emma->id))->assertRedirect();
        $this->assertDatabaseMissing('boat_passengers', ['id' => $emma->id]);
        $this->assertSame(0, (int) DB::table('boat_bookings')->where('id', $id)->value('nb_enfant'));
    }

    public function test_reserve_then_sell_or_exchange(): void
    {
        $id = $this->createBooking();
        $reservation = ['num_reservation' => 'BT-7788', 'date_option' => now()->addDays(3)->toDateString(), 'reservation_prix' => 512.5, 'details' => 'Pont 6'];

        $this->get(route('agent.bateau.reserve-form', $id))->assertOk();
        $this->post(route('agent.bateau.reserve', $id), $this->bookingFields($reservation))->assertRedirect(route('agent.bateau.confirmed'));
        $this->assertDatabaseHas('boat_reservations', ['booking_id' => $id, 'etat' => 'confirmer', 'num_reservation' => 'BT-7788']);
        Mail::assertSent(BookingMail::class, fn ($mail) => $mail->confirmed);
        $this->get(route('agent.bateau.confirmed'))->assertOk()->assertSee('BT-7788');
        $this->get(route('agent.bateau.show', $id))->assertOk()->assertSee("Bon d'échange", false);
        $this->get(route('agent.bateau.edit', $id))->assertNotFound();

        $this->post(route('agent.bateau.finalize', $id), $this->bookingFields(['action' => 'save', 'details' => 'Pont 7'] + $reservation))
            ->assertRedirect(route('agent.bateau.confirmed'));
        $this->assertDatabaseHas('boat_reservations', ['booking_id' => $id, 'etat' => 'confirmer', 'details' => 'Pont 7']);

        $this->post(route('agent.bateau.finalize', $id), $this->bookingFields($reservation + ['action' => 'echange']))->assertRedirect(route('agent.bateau.vouchers'));
        $this->get(route('agent.bateau.vouchers'))->assertOk()->assertSee('BT-7788');
        $this->get(route('agent.bateau.sold'))->assertOk()->assertDontSee('BT-7788');

        $this->post(route('agent.bateau.finalize', $id), $this->bookingFields($reservation + ['action' => 'final']))->assertRedirect(route('agent.bateau.sold'));
        $this->assertDatabaseHas('boat_reservations', ['booking_id' => $id, 'etat' => 'final']);
    }

    public function test_filters_export_catalog_and_delete(): void
    {
        $id = $this->createBooking();
        $destination = DB::table('boat_bookings')->where('id', $id)->value('destination_id');
        $other = DB::table('boat_destinations')->where('id', '!=', $destination)->value('id');

        $this->get(route('agent.bateau.pending', ['nom' => 'Durand']))->assertOk()->assertSee('Durand Hugo');
        $this->get(route('agent.bateau.pending', ['destination' => $other]))->assertOk()->assertDontSee('Durand Hugo');
        // Filtres vides : tout est affiché
        $this->get(route('agent.bateau.pending', ['destination' => '', 'nom' => '']))->assertOk()->assertSee('Durand Hugo');

        foreach (['pending', 'confirmer', 'final', 'echange'] as $status) {
            $response = $this->get(route('agent.bateau.export', ['statut' => $status]));
            $response->assertOk();
            $this->assertStringContainsString('spreadsheet', (string) $response->headers->get('content-type'));
        }

        $this->getJson(route('agent.bateau.catalog', $destination))->assertOk()->assertJsonStructure(['departurePorts', 'arrivalPorts', 'companies']);

        $file = DB::table('boat_bookings')->where('id', $id)->value('carte_grise');
        $this->post(route('agent.bateau.destroy', $id))->assertRedirect(route('agent.bateau.pending'));
        $this->assertDatabaseMissing('boat_bookings', ['id' => $id]);
        $this->assertDatabaseMissing('boat_passengers', ['booking_id' => $id]);
        Storage::disk('public')->assertMissing($file);
    }

    public function test_module_is_reserved_to_allowed_agents(): void
    {
        $this->actingAsAgent('partenaire');
        $this->get(route('agent.bateau.pending'))->assertForbidden();
    }
}
