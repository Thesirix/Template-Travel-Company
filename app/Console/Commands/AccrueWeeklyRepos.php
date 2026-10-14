<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccrueWeeklyRepos extends Command
{
    protected $signature = 'leaves:increment-repos';
    protected $description = 'Ajoute +1 jour de type "repos" à tous les agents (leave_counters) chaque lundi à 3h';

    public function handle()
    {
        $updated = DB::table('leave_counters')
            ->where('type', 'repos')
            ->update([
                'balance' => DB::raw('balance + 1')
            ]);

        $this->info("✅ $updated agents mis à jour avec +1 jour de repos.");
    }
}
