<?php

namespace Tests\Feature\Agent;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Page « Devis clients » : demande, fiche client, devis, suivi et SMS (appels AJAX en JSON).
 */
class ClientQuoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAgent('agent');
    }

    public function test_page_and_reference_data(): void
    {
        $this->get(route('agent.demandes.create'))->assertOk()->assertDontSee('Algeria,DZ" selected', false);
        $data = $this->getJson(route('agent.demandes.reference-data'))->assertOk()
            ->assertJsonStructure(['airports', 'ports', 'airlines', 'maritimes'])->json();
        $this->assertGreaterThan(1000, count($data['airports']));
        $this->assertCount(3, $data['airports'][0]);
    }

    public function test_client_request(): void
    {
        $this->postJson(route('agent.demandes.store'), [
            'formule' => 'Aérien', 'destination' => 'Italy,IT', 'departure_city' => 'Paris', 'arrived_city' => 'Rome',
            'departure_date' => now()->addMonth()->toDateString(), 'return_date' => now()->addMonth()->addWeek()->toDateString(),
            'nb_passagers_adultes' => 2, 'nb_passagers_childs' => 1, 'demande_bagage' => 'Avec',
        ])->assertOk()->assertJson(['status' => 200]);
        $this->assertDatabaseHas('client_requests', ['arrived_city' => 'Rome', 'bagage' => 1, 'nb_passagers_childs' => 1]);

        $this->postJson(route('agent.demandes.store'), ['formule' => 'Aérien'])->assertUnprocessable()->assertJsonValidationErrors('destination');
    }

    public function test_client_lifecycle_and_quote_with_sms(): void
    {
        $agent = DB::table('gents')->where('email', 'agent@travel-company.test')->value('id');

        $this->getJson(route('agent.demandes.clients.search', ['phoneNumber' => '0700000001']))->assertOk()->assertJson(['status' => 404]);
        $client = $this->postJson(route('agent.demandes.clients.store'), ['phoneNumber' => '0700000001', 'firstName' => 'Inès', 'lastName' => 'Roux'])
            ->assertOk()->assertJson(['status' => 200])->json('client');
        $this->postJson(route('agent.demandes.clients.store'), ['phoneNumber' => '0700000001'])->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Ce client existe déjà.']);
        $this->getJson(route('agent.demandes.clients.search', ['phoneNumber' => '0700000001']))->assertJson(['status' => 200]);

        $this->putJson(route('agent.demandes.clients.update', $client['id']), ['firstName' => 'Inès', 'lastName' => 'Roux', 'email' => 'ines@example.com'])
            ->assertOk();
        $this->assertSame('ines@example.com', DB::table('clients')->where('id', $client['id'])->value('email'));

        $quote = $this->postJson(route('agent.demandes.devis.store'), [
            'client_id' => $client['id'], 'Compagnie' => 'Sky Demo Airlines', 'bagage' => 'Avec', 'depart' => 'Paris', 'arrived_city' => 'Rome',
            'devis_departure_date' => now()->addMonth()->toDateString(), 'nb_passagers_adultes' => 2, 'nb_passagers_childs' => 0,
            'nb_passagers_babies' => 0, 'proposed_price' => 640, 'destination' => 'Italie', 'formule' => 'Aérien',
        ])->assertOk()->json('client_vol_estimate_id');
        $this->assertSame($agent, (int) DB::table('client_vol_estimates')->where('id', $quote)->value('gent_id'), "le devis revient à l'agent connecté");

        $this->postJson(route('agent.demandes.devis.sms'), ['client_vol_estimate_id' => $quote])->assertOk()->assertJson(['code' => 200]);

        $this->postJson(route('agent.demandes.devis.store'), ['client_vol_estimate_id' => $quote, 'comment' => 'Rappeler jeudi', 'is_vente' => 1, 'marge' => 80])
            ->assertOk();
        $this->postJson(route('agent.demandes.clients.store'), ['client_id' => $client['id'], 'provenance' => 'Bouche à oreille'])->assertOk();
        $this->assertDatabaseHas('client_vol_estimates', ['id' => $quote, 'comment' => 'Rappeler jeudi', 'is_vente' => 1, 'marge' => 80]);
        $this->assertSame('Bouche à oreille', DB::table('clients')->where('id', $client['id'])->value('provenance'));
    }

    public function test_quote_sms_text(): void
    {
        $service = app(\App\Services\ClientQuoteService::class);
        $quote = new \App\Models\ClientQuote(['departure_city' => 'Paris', 'arrived_city' => 'Montréal', 'company' => 'Maple Air',
            'departure_date' => '2026-12-01', 'return_date' => '2026-12-15', 'nb_passagers_adultes' => 2, 'nb_passagers_childs' => 1,
            'nb_passagers_babies' => 0, 'bagage' => 1, 'proposed_price' => 1500, 'formule' => 'Aérien']);
        $agent = new \App\Models\Agent(['prenom' => 'Léa', 'phone' => '0600000000']);

        $text = $service->smsMessage($quote, $agent);
        $this->assertStringContainsString('Paris/Montreal du 01/12/2026 au 15/12/2026', $text);
        $this->assertStringContainsString('2 adultes, 1 enfants', $text);
        $this->assertStringContainsString('Lea, 0600000000', $text);
    }
}
