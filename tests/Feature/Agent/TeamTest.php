<?php

namespace Tests\Feature\Agent;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Espace agent : notes, emploi du temps, congés, planning de l'équipe, profil, extractions.
 */
class TeamTest extends TestCase
{
    public function test_notes(): void
    {
        $agent = $this->actingAsAgent('agent');
        $this->postJson(route('agent.notes.store'), ['blacknotename' => 'Rappeler M. Dubois'])->assertOk()->assertJsonFragment(['text' => 'Rappeler M. Dubois']);
        $id = DB::table('black_notes')->where('text', 'Rappeler M. Dubois')->where('gent_id', $agent->id)->value('id');
        $this->assertNotNull($id);

        $this->postJson(route('agent.notes.destroy'), ['id' => $id])->assertOk();
        $this->assertDatabaseMissing('black_notes', ['id' => $id]);
    }

    public function test_schedule_events(): void
    {
        $agent = $this->actingAsAgent('agent');
        $start = now()->addDays(2)->setTime(9, 0);
        $this->postJson(route('agent.emploi.store'), ['title' => 'Salon du tourisme', 'start_date' => $start->toDateTimeString(), 'end_date' => $start->copy()->addHours(3)->toDateTimeString()])
            ->assertSuccessful();
        $id = DB::table('emplois')->where('title', 'Salon du tourisme')->where('gent_id', $agent->id)->value('id');
        $this->assertNotNull($id);

        $this->postJson(route('agent.emploi.update'), ['id' => $id, 'start_date' => $start->copy()->addDay()->toDateTimeString(), 'end_date' => $start->copy()->addDay()->addHours(2)->toDateTimeString()])->assertSuccessful();
        $this->postJson(route('agent.emploi.color'), ['id' => $id, 'color' => '#16a34a'])->assertSuccessful();
        $this->assertSame('#16a34a', DB::table('emplois')->where('id', $id)->value('color'));

        $this->getJson(route('agent.emploi.events'))->assertOk()->assertJsonFragment(['title' => 'Salon du tourisme']);

        $this->postJson(route('agent.emploi.destroy'), ['id' => $id])->assertSuccessful();
        $this->assertDatabaseMissing('emplois', ['id' => $id]);
    }

    public function test_leave_request_lifecycle(): void
    {
        $agent = $this->actingAsAgent('agent');
        $start = now()->next('Monday')->addWeeks(4);
        $this->postJson(route('agent.calendrier.store'), [
            'title' => 'Vacances', 'type' => 'conge', 'start_date' => $start->toDateString(), 'end_date' => $start->copy()->addDays(4)->toDateString(),
            'jours_par_type' => ['conge' => 5],
        ])->assertSuccessful();
        $id = DB::table('calendars')->where('gent_id', $agent->id)->where('name', 'Vacances')->value('id');
        $this->assertNotNull($id);
        $this->assertSame('pending', DB::table('calendars')->where('id', $id)->value('status'));

        $this->getJson(route('agent.calendrier.events'))->assertOk();
        $this->getJson(route('agent.calendrier.counters'))->assertOk();

        $this->postJson(route('agent.calendrier.update'), [
            'id' => $id, 'title' => 'Vacances d\'été', 'type' => 'conge', 'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addDays(3)->toDateString(), 'jours_par_type' => ['conge' => 4],
        ])->assertSuccessful();
        $this->assertSame('Vacances d\'été', DB::table('calendars')->where('id', $id)->value('name'));

        $this->postJson(route('agent.calendrier.destroy'), ['id' => $id])->assertSuccessful();
        $this->assertDatabaseMissing('calendars', ['id' => $id]);
    }

    public function test_team_planning_for_super_agent(): void
    {
        $this->actingAsAgent('super.agent');
        $agent = DB::table('gents')->where('email', 'agent@travel-company.test')->value('id');
        $pending = DB::table('calendars')->where('gent_id', $agent)->where('status', 'pending')->first();

        $this->getJson(route('agent.planning.events'))->assertOk();
        $this->getJson(route('agent.planning.resources'))->assertOk();
        $this->getJson(route('agent.planning.metrics'))->assertOk();
        $this->getJson(route('agent.planning.counters', ['gent_id' => $agent]))->assertOk();

        $this->postJson(route('agent.planning.approve', $pending->id), ['modification_token' => $pending->modification_token])->assertSuccessful();
        $this->assertSame('approved', DB::table('calendars')->where('id', $pending->id)->value('status'));

        $start = now()->addMonths(2)->startOfWeek();
        $this->postJson(route('agent.planning.store'), [
            'resourceId' => $agent, 'type' => 'repos', 'title' => 'Récupération', 'start_date' => $start->toDateString(),
            'end_date' => $start->toDateString(), 'jours_par_type' => json_encode(['repos' => 1]),
        ])->assertSuccessful();
        $created = DB::table('calendars')->where('gent_id', $agent)->where('name', 'Récupération')->first();
        $this->assertNotNull($created);

        $this->postJson(route('agent.planning.reject', $created->id), ['reason' => 'Effectif insuffisant'])->assertSuccessful();
        $this->assertSame('rejected', DB::table('calendars')->where('id', $created->id)->value('status'));

        // Modification par le responsable : passage en arrêt maladie sur deux jours
        $token = DB::table('calendars')->where('id', $created->id)->value('modification_token');
        $this->postJson(route('agent.planning.update', $created->id), [
            'start_date' => $start->toDateString(), 'end_date' => $start->copy()->addDays(2)->toDateString(),
            'status' => 'maladie', 'modification_token' => $token, 'jours_par_type' => json_encode([]),
        ])->assertSuccessful();
        $this->assertSame('maladie', DB::table('calendars')->where('id', $created->id)->value('status'));

        // Un jeton périmé est refusé, puis suppression avec le jeton à jour
        $this->deleteJson(route('agent.planning.destroy', $created->id), ['modification_token' => $token])->assertStatus(409);
        $token = DB::table('calendars')->where('id', $created->id)->value('modification_token');
        $this->deleteJson(route('agent.planning.destroy', $created->id), ['modification_token' => $token])->assertSuccessful();
        $this->assertDatabaseMissing('calendars', ['id' => $created->id]);

        $this->postJson(route('agent.planning.counters.update'), ['gent_id' => $agent, 'conge' => 20, 'repos' => 3, 'ferie' => 1, 'autre' => 0])->assertSuccessful();
        $this->assertEquals(20, DB::table('leave_counters')->where('gent_id', $agent)->where('type', 'conge')->value('balance'));
    }

    public function test_planning_is_reserved_to_allowed_agents(): void
    {
        $this->actingAsAgent('partenaire');
        $this->get(route('agent.planning'))->assertForbidden();
    }

    public function test_profile_update(): void
    {
        $agent = $this->actingAsAgent('agent');
        $this->put(route('agent.profil.update'), ['name' => 'Bernard', 'prenom' => 'Luc'])->assertRedirect();
        $this->assertSame('Luc', DB::table('gents')->where('id', $agent->id)->value('prenom'));
    }

    public function test_contacts_export(): void
    {
        $this->actingAsAgent();
        $this->get(route('agent.extractions', ['destination' => 'italie']))->assertOk()->assertSee('Contacts');
        $response = $this->post(route('agent.extractions.export'), ['destination' => 'italie']);
        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', (string) $response->headers->get('content-type'));
    }
}
