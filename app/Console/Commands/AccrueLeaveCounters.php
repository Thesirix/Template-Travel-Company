<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccrueLeaveCounters extends Command
{
    protected $signature = 'leaves:increment-conges';
    protected $description = 'Ajoute +2.5 jours de congé à tous les agents (leave_counters, type=conge)';

    public function handle()
    {
        $updated = DB::table('leave_counters')
            ->where('type', 'conge')
            ->update([
                'balance' => DB::raw('balance + 2.5')
            ]);

        $this->info("✅ $updated agents mis à jour avec +2.5 jours de congé.");
    }
}
