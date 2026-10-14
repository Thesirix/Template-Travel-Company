<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Chaque page (route GET nommée) de l'espace agent et de l'administration s'ouvre sans erreur,
 * avec de vrais enregistrements de la démo comme paramètres.
 */
class PagesTest extends TestCase
{
    /** Routes GET qui ne sont pas des pages (fichiers générés, déconnexion) : testées ailleurs */
    private const NOT_PAGES = '#\.(export|download|e-billet|contrat|logout)(\.|$)|^agent\.vols\.(vente\.create|reservations\.change-flight\.preview)$|^agent\.demandes\.clients\.search$#';

    public function test_every_agent_page_opens(): void
    {
        $this->actingAsAgent('super.agent');
        $this->assertPagesOpen('agent.');
    }

    public function test_every_admin_page_opens(): void
    {
        $this->actingAsAdmin();
        $this->assertPagesOpen('admin.');
    }

    public function test_sale_form_opens_for_the_chosen_flight(): void
    {
        $this->actingAsAgent();
        $params = $this->resolve(['group', 'flight', 'reservation']);
        $this->get(route('agent.vols.vente.create', $params['group']) . '?' . http_build_query(['vol_id' => $params['flight'], 'adultcount' => 2, 'enfantcount' => 1, 'bebecount' => 0]))
            ->assertOk();
        $this->get(route('agent.vols.reservations.change-flight.preview', $params['group']) . '?' . http_build_query(['vol_id' => $params['flight'], 'reservation_id' => $params['reservation']]))
            ->assertOk();
        // Sans vol choisi : retour à la page précédente
        $this->get(route('agent.vols.vente.create', $params['group']))->assertRedirect();
    }

    public function test_partner_agent_only_sees_allowed_pages(): void
    {
        $this->actingAsAgent('partenaire');
        $group = DB::table('vol_groups')->where('type', 'round-trip')->value('id');

        $this->get(route('agent.home'))->assertOk();
        $this->get(route('agent.vols.vente', $group))->assertOk();
        $this->get(route('agent.demandes.create'))->assertOk();
        // Gestion des dossiers et émission : réservées à l'agence principale
        $this->get(route('agent.vols.dossiers', $group))->assertRedirect();
        // Module non attribué : allotements
        $this->get(route('agent.block-power'))->assertForbidden();
    }

    public function test_guests_are_sent_to_the_right_login_page(): void
    {
        $this->get('/agent')->assertRedirect(route('login'));
        $this->get('/admin/agents')->assertRedirect(route('login'));
        $this->getJson('/api/me')->assertUnauthorized();
        $this->get('/')->assertRedirect('/connexion');
    }

    #[DataProvider('files')]
    public function test_generated_files_download(string $space, string $route, array $params): void
    {
        $space === 'agent' ? $this->actingAsAgent() : $this->actingAsAdmin();
        $response = $this->get(route($route, $this->resolve($params)));

        $response->assertOk();
        $this->assertMatchesRegularExpression('#pdf|spreadsheet|excel|octet|image|csv#', (string) $response->headers->get('content-type'));
    }

    public static function files(): array
    {
        return [
            'contrat de vente' => ['agent', 'agent.vols.vente.contrat', ['group', 'reservation']],
            'e-billet' => ['agent', 'agent.vols.vente.e-billet', ['group', 'reservation']],
            'procédure (agent)' => ['agent', 'agent.procedures.download', ['procedure']],
            'procédure (admin)' => ['admin', 'admin.procedures.download', ['procedure']],
            'export bateau' => ['agent', 'agent.bateau.export', []],
            'guide des allotements' => ['agent', 'agent.block-power.guide', []],
        ];
    }

    private function assertPagesOpen(string $prefix): void
    {
        $failures = [];
        $count = 0;
        /** @var Route $route */
        foreach (app('router')->getRoutes() as $route) {
            $name = (string) $route->getName();
            if (! str_starts_with($name, $prefix) || ! in_array('GET', $route->methods())
                || preg_match(self::NOT_PAGES, $name) || str_ends_with($name, '.login') || $name === 'agent.block-power.guide') {
                continue;
            }
            $url = route($name, $this->resolve($route->parameterNames(), $name));
            $status = $this->get($url)->getStatusCode();
            $count++;
            if ($status !== 200) {
                $failures[] = "$status $name ($url)";
            }
        }
        $this->assertGreaterThan(20, $count);
        $this->assertSame([], $failures, implode("\n", $failures));
    }

    /** Valeurs réelles des paramètres de route, cohérentes entre elles */
    private function resolve(array $params, string $route = ''): array
    {
        $group = DB::table('vol_groups')->where('type', 'round-trip')->orderBy('id')->value('id');
        $booking = fn (?string $etat) => $etat
            ? DB::table('boat_reservations')->where('etat', $etat)->value('booking_id')
            : DB::table('boat_bookings')->whereNotIn('id', DB::table('boat_reservations')->pluck('booking_id'))->value('id');
        $option = fn (string $status) => DB::table('block_power_options')->where('status', $status)->value('id');
        $destination = DB::table('destinations')->orderBy('position')->value('slug');
        $values = [
            'group' => $group,
            'reservation' => DB::table('vol_reservations')->join('vols', 'vols.id', '=', 'vol_reservations.vol_id')
                ->where('vols.vol_group_id', $group)->where('vol_reservations.status', 1)->value('vol_reservations.id'),
            'flight' => $route === 'admin.places.edit' || str_starts_with($route, 'admin.vols')
                ? DB::table('vols')->where('destination', $destination)->value('id')
                : DB::table('vols')->where('vol_group_id', $group)->value('id'),
            'booking' => $booking(null),
            'passenger' => DB::table('boat_passengers')->value('id'),
            'boatDestination' => DB::table('boat_destinations')->value('id'),
            'option' => $option($route === 'agent.block-power.confirm-form' ? 'named' : 'available'),
            'blockPowerRequest' => DB::table('block_power_requests')->value('id'),
            'destination' => $destination,
            'procedure' => DB::table('procedures')->value('id'),
            'client' => DB::table('clients')->value('id'),
            'agent' => DB::table('gents')->value('id'),
            'agency' => DB::table('agences')->value('id'),
            'country' => DB::table('countries')->value('id'),
            'city' => DB::table('villes')->value('id'),
            'airport' => DB::table('air_ports')->value('id'),
            'company' => DB::table('companies')->value('id'),
            'condition' => DB::table('conditions')->value('id'),
            'insurance' => DB::table('assurences')->value('id'),
        ];

        return array_combine($params, array_map(fn ($p) => $values[$p], $params));
    }
}
