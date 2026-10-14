<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Agents, réglages de l'agence, destinations, procédures, catalogue bateau et réglages des allotements.
 */
class SettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_agent_lifecycle(): void
    {
        $agency = DB::table('agences')->value('id');
        $this->post(route('admin.agents.store'), [
            'name' => 'Garnier', 'prenom' => 'Lisa', 'phone' => '06 11 22 33 44', 'agence' => $agency,
            'email' => 'lisa.garnier@travel-company.test', 'password' => 'Secret1234', 'password_confirmation' => 'Secret1234',
            'contract_start_date' => now()->subYear()->toDateString(),
        ])->assertRedirect(route('admin.agents'))->assertSessionHasNoErrors();
        $id = DB::table('gents')->where('email', 'lisa.garnier@travel-company.test')->value('id');
        $this->assertNotNull($id);
        $this->assertSame(5, DB::table('leave_counters')->where('gent_id', $id)->count());
        $this->assertGreaterThan(0, (float) DB::table('leave_counters')->where('gent_id', $id)->where('type', 'conge')->value('balance'));

        // Droits : une destination + un module
        $this->put(route('admin.agents.update', $id), [
            'name' => 'Garnier', 'prenom' => 'Lisa', 'phone' => '06 11 22 33 44', 'agence' => $agency,
            'email' => 'lisa.garnier@travel-company.test', 'permissions' => ['gerer Italie', 'gerer Bateau', 'pirate'],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $permissions = DB::table('model_has_permissions')->join('permissions', 'permissions.id', '=', 'permission_id')
            ->where('model_id', $id)->pluck('name')->sort()->values()->all();
        $this->assertSame(['gerer Bateau', 'gerer Italie'], $permissions);

        $this->put(route('admin.agents.password.update', $id), ['password' => 'Nouveau1234', 'password_confirmation' => 'Nouveau1234'])->assertRedirect();
        $this->assertTrue(password_verify('Nouveau1234', DB::table('gents')->where('id', $id)->value('password')));

        $this->post(route('admin.agents.toggle'), ['gent_id' => $id])->assertRedirect();
        $this->assertSame('disable', DB::table('gents')->where('id', $id)->value('state'));
        $this->post(route('admin.agents.toggle'), ['gent_id' => $id]);
        $this->assertSame('enable', DB::table('gents')->where('id', $id)->value('state'));

        $this->delete(route('admin.agents.destroy', $id))->assertRedirect();
        $this->assertDatabaseMissing('gents', ['id' => $id]);
    }

    public function test_agent_email_must_be_unique_and_agent_with_sales_is_kept(): void
    {
        $agency = DB::table('agences')->value('id');
        $this->post(route('admin.agents.store'), [
            'name' => 'X', 'prenom' => 'Y', 'phone' => '06', 'agence' => $agency, 'email' => 'agent@travel-company.test',
            'password' => 'Secret1234', 'password_confirmation' => 'Secret1234', 'contract_start_date' => '2025-01-01',
        ])->assertSessionHasErrors('email');

        $seller = DB::table('vol_reservations')->value('gent_id');
        $this->delete(route('admin.agents.destroy', $seller))->assertSessionHas('error');
        $this->assertDatabaseHas('gents', ['id' => $seller]);
    }

    public function test_disabled_agent_cannot_log_in(): void
    {
        auth('gestion')->logout();
        $this->post(route('login.submit'), ['email' => 'ancien.agent@travel-company.test', 'password' => 'Demo1234!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('gent');
    }

    public function test_single_login_page_sends_each_account_to_its_space(): void
    {
        auth('gestion')->logout();
        $this->get(route('login'))->assertOk();

        $this->post(route('login.submit'), ['email' => 'admin@travel-company.test', 'password' => 'mauvais'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('gestion');

        $this->post(route('login.submit'), ['email' => 'admin@travel-company.test', 'password' => 'Demo1234!'])
            ->assertRedirect(route('admin.home'));
        $this->assertAuthenticated('gestion');
        $this->assertGuest('gent');
        auth('gestion')->logout();

        $this->post(route('login.submit'), ['email' => 'agent@travel-company.test', 'password' => 'Demo1234!'])
            ->assertRedirect(route('agent.home'));
        $this->assertAuthenticated('gent');
        auth('gent')->logout();

    }

    public function test_page_requested_before_login_is_kept_only_in_the_right_space(): void
    {
        auth('gestion')->logout();
        $this->get(route('admin.agents'))->assertRedirect(route('login'));
        $this->post(route('login.submit'), ['email' => 'admin@travel-company.test', 'password' => 'Demo1234!'])
            ->assertRedirect(route('admin.agents'));
        auth('gestion')->logout();

        $this->get(route('admin.agents'))->assertRedirect(route('login'));
        $this->post(route('login.submit'), ['email' => 'agent@travel-company.test', 'password' => 'Demo1234!'])
            ->assertRedirect(route('agent.home'));
    }

    public function test_agency_identity(): void
    {
        $this->post(route('admin.parametres.update'), [
            'agency_name' => 'Voyages Horizon', 'color_primary' => '#0f766e', 'color_accent' => '#f97316', 'banner_style' => 'solid',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Voyages Horizon', DB::table('settings')->where('key', 'agency_name')->value('value'));
        $this->get(route('admin.home'))->assertSee('Voyages Horizon');
    }

    public function test_destinations_crud_and_permission(): void
    {
        $this->post(route('admin.destinations.store'), ['name' => 'Espagne', 'slug' => 'espagne', 'color' => '#facc15', 'is_active' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
        $destination = DB::table('destinations')->where('slug', 'espagne')->first();
        $this->assertNotNull($destination);
        $this->assertDatabaseHas('permissions', ['name' => 'gerer Espagne', 'guard_name' => 'gent']);

        $this->put(route('admin.destinations.update', $destination->id), [
            'name' => 'Espagne', 'slug' => 'espagne', 'color' => '#ef4444', 'country_id' => $destination->country_id, 'is_active' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('#ef4444', DB::table('destinations')->where('id', $destination->id)->value('color'));

        $this->delete(route('admin.destinations.destroy', $destination->id))->assertRedirect();
        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }

    public function test_procedures_upload_download_and_delete(): void
    {
        Storage::fake('public');
        $this->post(route('admin.procedures.store'), ['name' => 'Check-list départ', 'media' => UploadedFile::fake()->create('check.pdf', 20, 'application/pdf')])
            ->assertRedirect(route('admin.procedures'))->assertSessionHasNoErrors();
        $procedure = DB::table('procedures')->where('name', 'Check-list départ')->first();
        Storage::disk('public')->assertExists('procedures/' . $procedure->media);

        $this->get(route('admin.procedures.download', $procedure->id))->assertOk();

        $this->post(route('admin.procedures.destroy'), ['procedureid' => $procedure->id])->assertRedirect();
        $this->assertDatabaseMissing('procedures', ['id' => $procedure->id]);
        Storage::disk('public')->assertMissing('procedures/' . $procedure->media);
    }

    public function test_block_power_guide_upload(): void
    {
        Storage::fake('public');
        $this->post(route('admin.block-power.guide.store'), ['file' => UploadedFile::fake()->create('guide.pdf', 30, 'application/pdf')])
            ->assertRedirect()->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists('guide/guide-allotements.pdf');
        $this->assertSame(1, DB::table('block_power_guides')->count());
    }

    #[DataProvider('catalogs')]
    public function test_boat_catalog_entries_can_be_added_and_removed(string $catalog, string $table, bool $perDestination): void
    {
        $fields = ['name' => 'Élément de test'] + ($perDestination ? ['destination_id' => DB::table('boat_destinations')->value('id')] : []);
        $this->post(route('admin.bateau.store', $catalog), $fields)->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table($table)->where('name', 'Élément de test')->value('id');
        $this->assertNotNull($id, "$catalog : rien n'a été créé");

        $this->post(route('admin.bateau.destroy', $catalog), ['id' => $id])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing($table, ['id' => $id]);
    }

    public function test_boat_catalog_entry_in_use_is_kept(): void
    {
        $port = DB::table('boat_bookings')->value('departure_port_id');
        $this->post(route('admin.bateau.destroy', 'ports-depart'), ['id' => $port])->assertSessionHas('error');
        $this->assertDatabaseHas('boat_departure_ports', ['id' => $port]);
        $this->post(route('admin.bateau.store', 'ports-depart'), ['name' => 'Sans destination'])->assertSessionHasErrors('destination_id');
    }

    #[DataProvider('blockPowerCatalogs')]
    public function test_block_power_catalog_entries_can_be_added_and_removed(string $catalog, string $table): void
    {
        $this->post(route('admin.block-power.store', $catalog), ['name' => 'Élément de test'])->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table($table)->where('name', 'Élément de test')->value('id');
        $this->assertNotNull($id, "$catalog : rien n'a été créé");
        $this->post(route('admin.block-power.store', $catalog), ['name' => 'Élément de test'])->assertSessionHasErrors('name');

        $this->post(route('admin.block-power.destroy', $catalog), ['id' => $id])->assertRedirect();
        $this->assertDatabaseMissing($table, ['id' => $id]);
    }

    public static function blockPowerCatalogs(): array
    {
        return [
            'pays' => ['pays', 'block_power_countries'],
            'compagnie' => ['compagnies', 'block_power_companies'],
            'ville de départ' => ['villes-depart', 'block_power_departure_cities'],
            "ville d'arrivée" => ['villes-arrivee', 'block_power_arrival_cities'],
        ];
    }

    public static function catalogs(): array
    {
        return [
            'destination' => ['destinations', 'boat_destinations', false],
            'port de départ' => ['ports-depart', 'boat_departure_ports', true],
            "port d'arrivée" => ['ports-arrivee', 'boat_arrival_ports', true],
            'compagnie' => ['compagnies', 'boat_companies', true],
            'gabarit' => ['gabarits', 'boat_vehicle_sizes', false],
            'installation' => ['installations', 'boat_accommodations', false],
            'tarif' => ['tarifs', 'boat_fares', false],
            'véhicule' => ['vehicules', 'boat_vehicles', false],
            'assurance passager' => ['assurances-passagers', 'boat_passenger_insurances', false],
            'assurance véhicule' => ['assurances-vehicules', 'boat_vehicle_insurances', false],
        ];
    }
}
