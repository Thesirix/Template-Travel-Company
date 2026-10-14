<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Catalogue de l'administration : création, modification et suppression des référentiels.
 */
class CatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_countries_crud(): void
    {
        $this->post(route('admin.pays.store'), ['name' => 'Islande', 'show' => 1, 'chart' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('countries')->where('nom', 'Islande')->value('id');
        $this->assertNotNull($id);

        $this->put(route('admin.pays.update', $id), ['name' => 'Islande (Reykjavik)', 'show' => 1, 'chart' => 1])->assertRedirect();
        $this->assertDatabaseHas('countries', ['id' => $id, 'nom' => 'Islande (Reykjavik)']);

        $this->delete(route('admin.pays.destroy', $id))->assertRedirect();
        $this->assertDatabaseMissing('countries', ['id' => $id]);
    }

    public function test_country_logo_is_stored_as_relative_path(): void
    {
        Storage::fake('logos');
        $this->post(route('admin.pays.store'), ['name' => 'Malte', 'show' => 1, 'image' => UploadedFile::fake()->image('malte.png')])
            ->assertRedirect()->assertSessionHasNoErrors();
        $raw = DB::table('countries')->where('nom', 'Malte')->value('image');
        $this->assertStringStartsWith('uploads/logos/', $raw);
        @unlink(public_path($raw));
    }

    public function test_cities_crud(): void
    {
        $country = DB::table('countries')->where('nom', 'Italie')->value('id');
        $this->post(route('admin.villes.store'), ['name' => 'Naples', 'country' => $country, 'coleur' => '#ff0000'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('villes')->where('nom', 'Naples')->value('id');
        $this->assertNotNull($id);

        $this->put(route('admin.villes.update', $id), ['name' => 'Napoli', 'country' => $country, 'coleur' => '#00ff00'])->assertRedirect();
        $this->assertDatabaseHas('villes', ['id' => $id, 'nom' => 'Napoli', 'coleur' => '#00ff00']);

        $this->delete(route('admin.villes.destroy', $id))->assertRedirect();
        $this->assertDatabaseMissing('villes', ['id' => $id]);
    }

    public function test_airports_crud(): void
    {
        $city = DB::table('villes')->where('nom', 'Milan')->value('id');
        $this->post(route('admin.aeroports.store'), ['name' => 'Milan Linate', 'abv' => 'LIN', 'ville' => $city])
            ->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('air_ports')->where('abv', 'LIN')->value('id');
        $this->assertNotNull($id);

        $this->put(route('admin.aeroports.update', $id), ['name' => 'Milano Linate', 'abv' => 'LIN', 'ville' => $city])->assertRedirect();
        $this->assertDatabaseHas('air_ports', ['id' => $id, 'nom' => 'Milano Linate']);

        $this->delete(route('admin.aeroports.destroy', $id))->assertRedirect();
        $this->assertDatabaseMissing('air_ports', ['id' => $id]);
    }

    public function test_companies_crud(): void
    {
        $country = DB::table('countries')->where('nom', 'France')->value('id');
        $this->post(route('admin.compagnies.store'), ['name' => 'Air Démo', 'country' => $country, 'type' => 1, 'show' => 1, 'chart' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('companies')->where('nom', 'Air Démo')->value('id');
        $this->assertNotNull($id);

        $this->put(route('admin.compagnies.update', $id), ['name' => 'Air Démo 2', 'country' => $country, 'type' => 1, 'show' => 1, 'chart' => 1])->assertRedirect();
        $this->assertDatabaseHas('companies', ['id' => $id, 'nom' => 'Air Démo 2']);

        $this->delete(route('admin.compagnies.destroy', $id))->assertRedirect();
        $this->assertDatabaseMissing('companies', ['id' => $id]);
    }

    public function test_agencies_create_and_update(): void
    {
        $data = ['name' => 'Agence Nice', 'phone' => '04 93 00 00 00', 'rue' => '3 avenue Jean Médecin', 'ville' => 'Nice', 'email' => 'nice@travel-company.test'];
        $this->post(route('admin.agences.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('agences')->where('email', 'nice@travel-company.test')->value('id');
        $this->assertNotNull($id);

        $this->put(route('admin.agences.update', $id), ['name' => 'Agence Nice Centre', 'is_partner_agency' => 1] + $data)->assertRedirect();
        $this->assertDatabaseHas('agences', ['id' => $id, 'name' => 'Agence Nice Centre', 'is_partner_agency' => 1]);
    }

    public function test_sales_conditions_crud(): void
    {
        $data = ['name' => 'Conditions groupe', 'Formalite_sante' => 'Aucune', 'Formalite_police' => 'Passeport',
            'Formalite_inscription' => 'Acompte 30 %', 'Formalite_modifiation' => 'Sans frais', 'Formalite_annulation' => 'J-30'];
        $this->post(route('admin.conditions.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('conditions')->where('nom', 'Conditions groupe')->value('id');
        $this->assertNotNull($id);

        $this->post(route('admin.conditions.update'), ['condition_id' => $id, 'name' => 'Conditions groupes'] + $data)->assertRedirect();
        $this->assertDatabaseHas('conditions', ['id' => $id, 'nom' => 'Conditions groupes']);

        $this->post(route('admin.conditions.destroy'), ['condition_id' => $id])->assertRedirect();
        $this->assertDatabaseMissing('conditions', ['id' => $id]);
    }

    public function test_insurances_crud(): void
    {
        $this->post(route('admin.assurances.store'), ['type_assu' => 'Toutes', 'name' => 'Assurance bagages', 'Description' => 'Bagages perdus ou abîmés', 'Prix' => 19])
            ->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('assurences')->where('name', 'Assurance bagages')->value('id');
        $this->assertNotNull($id);

        $this->post(route('admin.assurances.update', $id), ['type_assu' => 'Italie', 'name' => 'Assurance bagages Italie', 'Description' => 'Bagages', 'Prix' => 15])
            ->assertRedirect();
        $this->assertDatabaseHas('assurences', ['id' => $id, 'type' => 'Italie', 'prix' => 15]);

        $this->post(route('admin.assurances.destroy'), ['condition_id' => $id])->assertRedirect();
        $this->assertDatabaseMissing('assurences', ['id' => $id]);
    }

    public function test_flight_groups_crud(): void
    {
        $country = DB::table('countries')->where('nom', 'Grèce')->value('id');
        $data = ['name' => 'Grèce - Été', 'country_id' => $country, 'type' => 'round-trip', 'same_itineraire' => 1, 'is_active' => 1];
        $this->post(route('admin.groupes-vols.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('vol_groups')->where('name', 'Grèce - Été')->value('id');
        $this->assertNotNull($id);

        $this->post(route('admin.groupes-vols.update'), ['id' => $id, 'name' => 'Grèce - Été 2027'] + $data)->assertRedirect();
        $this->assertDatabaseHas('vol_groups', ['id' => $id, 'name' => 'Grèce - Été 2027']);

        $this->post(route('admin.groupes-vols.destroy'), ['id' => $id])->assertRedirect();
        $this->assertDatabaseMissing('vol_groups', ['id' => $id]);
    }

    public function test_a_group_with_flights_cannot_be_deleted(): void
    {
        $group = DB::table('vol_groups')->value('id');
        $this->post(route('admin.groupes-vols.destroy'), ['id' => $group])->assertRedirect();
        $this->assertDatabaseHas('vol_groups', ['id' => $group]);
    }

    public function test_tasks_for_agents(): void
    {
        $agent = DB::table('gents')->value('id');
        $this->post(route('admin.taches.store'), ['gent' => $agent, 'Note' => 'Appeler la compagnie'])->assertRedirect();
        $id = DB::table('to_do_lists')->where('text', 'Appeler la compagnie')->value('id');
        $this->assertNotNull($id);

        $this->post(route('admin.taches.destroy'), ['id' => $id])->assertRedirect();
        $this->assertDatabaseMissing('to_do_lists', ['id' => $id]);
    }

    public function test_seats_of_a_flight_can_be_corrected(): void
    {
        $flight = DB::table('vols')->first();
        $this->post(route('admin.places.update'), [
            'volid' => $flight->id, 'placetotal' => 50, 'placereserver' => $flight->place_reserver, 'placedisponsible' => 50 - $flight->place_reserver,
        ])->assertRedirect();
        $this->assertDatabaseHas('vols', ['id' => $flight->id, 'place_total' => 50]);
    }

    public function test_admin_profile_and_password(): void
    {
        $this->put(route('admin.profil.update'), ['name' => 'Admin', 'prenom' => 'Principal'])->assertRedirect();
        $this->assertDatabaseHas('gestions', ['email' => 'admin@travel-company.test', 'prenom' => 'Principal']);

        $this->post(route('admin.profil.password'), ['password' => 'Nouveau1234!', 'password_confirmation' => 'Nouveau1234!'])->assertRedirect();
        $this->assertTrue(password_verify('Nouveau1234!', DB::table('gestions')->value('password')));
    }
}
