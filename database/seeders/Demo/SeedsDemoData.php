<?php

namespace Database\Seeders\Demo;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Outils communs aux seeders de démonstration : date de référence, horodatage, recherche d'identifiants.
 */
trait SeedsDemoData
{
    protected function now(): Carbon
    {
        return Carbon::now();
    }

    protected function stamps(array $row): array
    {
        return $row + ['created_at' => $this->now(), 'updated_at' => $this->now()];
    }

    /** Identifiant d'un agent de démo par e-mail (ex. « super.agent ») */
    protected function agentId(string $login): int
    {
        return (int) DB::table('gents')->where('email', $login . '@travel-company.test')->value('id');
    }

    /** Agents de l'agence principale, dans l'ordre de création */
    protected function agentIds(): array
    {
        return DB::table('gents')->orderBy('id')->pluck('id')->all();
    }

    protected function phone(): string
    {
        return '06 ' . implode(' ', array_map(fn () => str_pad((string) mt_rand(10, 99), 2, '0'), range(1, 4)));
    }
}
