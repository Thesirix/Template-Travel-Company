<?php

namespace Tests;

use App\Models\Admin;
use App\Models\Agent;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base des tests : schéma recréé et démo chargée une fois par lancement, chaque test tourne dans une transaction.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DemoSeeder::class;

    /** Connecte un agent de la démo : super.agent, agent, partenaire */
    protected function actingAsAgent(string $login = 'super.agent'): Agent
    {
        $agent = Agent::where('email', $login . '@travel-company.test')->firstOrFail();
        $this->actingAs($agent, 'gent');

        return $agent;
    }

    protected function actingAsAdmin(): Admin
    {
        $admin = Admin::firstOrFail();
        $this->actingAs($admin, 'gestion');

        return $admin;
    }
}
