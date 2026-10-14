<?php

namespace Tests\Feature\Agent;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Allotements : option → passagers → confirmation → émission ; relâche et reprise ; demandes ; statuts quotidiens.
 */
class BlockPowerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAgent('super.agent');
    }

    private function optionFields(array $override = []): array
    {
        return $override + [
            'name' => 'Groupe test', 'pnr' => 'TST123', 'pnr_created_on' => now()->toDateString(), 'country' => 'Italie',
            'company' => 'Sky Demo Airlines', 'departure_city' => 'Paris', 'arrival_city' => 'Rome',
            'date_aller' => now()->addDays(40)->toDateString(), 'date_retour' => now()->addDays(47)->toDateString(),
            'seats' => 3, 'cabin_class' => 'Économique', 'price' => 410, 'option_date' => now()->addDays(10)->toDateString(),
        ];
    }

    private function createOption(array $override = []): int
    {
        $this->post(route('agent.block-power.store'), $this->optionFields($override))
            ->assertRedirect(route('agent.block-power'))->assertSessionHasNoErrors();

        return DB::table('block_power_options')->where('pnr', $override['pnr'] ?? 'TST123')->value('id');
    }

    public function test_full_cycle_from_option_to_issue(): void
    {
        $this->get(route('agent.block-power.create'))->assertOk();
        $id = $this->createOption();
        $this->get(route('agent.block-power'))->assertOk()->assertSee('TST123');
        $this->post(route('agent.block-power.store'), $this->optionFields())->assertSessionHasErrors('pnr');

        $this->get(route('agent.block-power.passengers', $id))->assertOk();
        $this->post(route('agent.block-power.passengers.store', $id), ['passagers' => [
            ['type' => 'Mr', 'last_name' => 'Garcia', 'first_name' => 'Hugo', 'birth_date' => '1980-04-02', 'sale_price' => 450, 'deposit' => 100, 'deposit_number' => 'A1'],
            ['type' => 'CHD', 'last_name' => 'Garcia', 'first_name' => 'Léa', 'birth_date' => now()->subYears(6)->toDateString()],
        ]])->assertRedirect(route('agent.block-power.show', $id))->assertSessionHasNoErrors();
        $this->assertSame('named', DB::table('block_power_options')->where('id', $id)->value('status'));
        $this->get(route('agent.block-power', ['statut' => 'nominatives']))->assertSee('TST123');

        // Une seule place restante
        $this->post(route('agent.block-power.passengers.store', $id), ['passagers' => [
            ['type' => 'Mr', 'last_name' => 'A', 'first_name' => 'B'], ['type' => 'Mr', 'last_name' => 'C', 'first_name' => 'D'],
        ]])->assertSessionHasErrors('passagers');

        $this->get(route('agent.block-power.confirm-form', $id))->assertOk();
        $this->post(route('agent.block-power.confirm', $id), ['pnr' => 'TST999', 'option_date' => now()->addDays(20)->toDateString(), 'reprendre' => 1])
            ->assertRedirect(route('agent.block-power.create', ['depuis' => $id, 'places' => 1]));
        $this->assertDatabaseHas('block_power_options', ['id' => $id, 'status' => 'confirmed', 'pnr' => 'TST999']);
        $this->assertSame(2, DB::table('block_power_confirmations')->whereIn('passenger_id', DB::table('block_power_passengers')->where('option_id', $id)->pluck('id'))->count());
        $this->get(route('agent.block-power.create', ['depuis' => $id, 'places' => 1]))->assertOk()->assertSee('Groupe test');

        $this->post(route('agent.block-power.issue', $id))->assertRedirect(route('agent.block-power', ['statut' => 'emises']));
        $this->assertSame('issued', DB::table('block_power_options')->where('id', $id)->value('status'));
        $this->get(route('agent.block-power', ['statut' => 'emises']))->assertSee('TST999');
        $this->get(route('agent.block-power.edit', $id))->assertNotFound();
    }

    public function test_passenger_type_must_match_age(): void
    {
        $id = $this->createOption();
        $this->post(route('agent.block-power.passengers.store', $id), ['passagers' => [
            ['type' => 'INF', 'last_name' => 'Petit', 'first_name' => 'Tom', 'birth_date' => now()->subYears(5)->toDateString()],
        ]])->assertSessionHasErrors('passagers.0.birth_date');
        $this->assertSame(0, DB::table('block_power_passengers')->where('option_id', $id)->count());
    }

    public function test_edit_remove_passenger_and_release(): void
    {
        $id = $this->createOption();
        $this->post(route('agent.block-power.passengers.store', $id), ['passagers' => [['type' => 'Mrs', 'last_name' => 'Roux', 'first_name' => 'Anne']]]);

        $this->get(route('agent.block-power.edit', $id))->assertOk();
        $this->post(route('agent.block-power.update', $id), $this->optionFields(['seats' => 5, 'note' => 'Prix négocié']))
            ->assertRedirect(route('agent.block-power.show', $id));
        $this->assertDatabaseHas('block_power_options', ['id' => $id, 'seats' => 5, 'note' => 'Prix négocié']);

        $passenger = DB::table('block_power_passengers')->where('option_id', $id)->value('id');
        $this->post(route('agent.block-power.passengers.destroy', $passenger))->assertRedirect();
        $this->assertSame('available', DB::table('block_power_options')->where('id', $id)->value('status'));

        $this->post(route('agent.block-power.release', $id), ['reprendre' => 1])->assertRedirect(route('agent.block-power.create', ['depuis' => $id]));
        $this->assertSame('released', DB::table('block_power_options')->where('id', $id)->value('status'));
        $this->assertDatabaseHas('block_power_releases', ['option_id' => $id]);
        $this->get(route('agent.block-power', ['statut' => 'relachees']))->assertSee('TST123');
        $this->post(route('agent.block-power.release', $id))->assertNotFound();
    }

    public function test_every_tab_and_sort_opens(): void
    {
        foreach (array_keys(\App\Http\Controllers\Agent\BlockPowerController::TABS) as $tab) {
            $this->get(route('agent.block-power', ['statut' => $tab, 'tri' => 'price', 'sens' => 'desc']))->assertOk();
        }
        // Tri inconnu : ignoré (pas d'injection de colonne)
        $this->get(route('agent.block-power', ['tri' => 'id; drop table users', 'sens' => 'n\'importe']))->assertOk();
        $this->get(route('agent.block-power', ['statut' => 'archives']))->assertSee('BP7314');
    }

    public function test_requests(): void
    {
        $this->get(route('agent.block-power.requests.create'))->assertOk();
        $this->post(route('agent.block-power.requests.store'), $this->optionFields(['pnr' => '', 'target_class' => 'Premium', 'target_price' => 380]))
            ->assertRedirect(route('agent.block-power.requests'))->assertSessionHasNoErrors();
        $request = DB::table('block_power_requests')->where('name', 'Groupe test')->first();
        $this->assertEquals(380, $request->target_price);
        $this->get(route('agent.block-power.requests'))->assertOk()->assertSee('Groupe test');

        $this->post(route('agent.block-power.requests.destroy', $request->id))->assertRedirect();
        $this->assertDatabaseMissing('block_power_requests', ['id' => $request->id]);
    }

    public function test_daily_status_refresh(): void
    {
        $soon = $this->createOption(['pnr' => 'SOON1', 'option_date' => now()->addDay()->toDateString()]);
        $late = $this->createOption(['pnr' => 'LATE1', 'option_date' => now()->subDay()->toDateString()]);
        $confirmed = DB::table('block_power_options')->where('status', 'confirmed')->value('id');
        DB::table('block_power_options')->where('id', $confirmed)->update(['option_date' => now()->subDays(3)]);

        $this->artisan('block-power:refresh')->assertSuccessful();

        $this->assertSame('expiring', DB::table('block_power_options')->where('id', $soon)->value('status'));
        $this->assertSame('expired', DB::table('block_power_options')->where('id', $late)->value('status'));
        $this->assertSame('confirmed', DB::table('block_power_options')->where('id', $confirmed)->value('status'), 'une option confirmée ne périme pas');
    }

    public function test_guide_and_access(): void
    {
        $this->get(route('agent.block-power.guide'))->assertOk();
        $this->actingAsAgent('partenaire');
        $this->get(route('agent.block-power'))->assertForbidden();
    }
}
