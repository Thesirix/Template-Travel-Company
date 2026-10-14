<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Alertes avant émission des billets (J-61, J-45, J-35)
        $schedule->command('flights:issuance-alerts')->dailyAt('08:00');

        // Allotements bientôt échues ou expirées
        $schedule->command('block-power:refresh')->dailyAt('06:00');

        // Congés acquis : +2,5 jours le 1er du mois ; repos : chaque lundi
        $schedule->command('leaves:increment-conges')->monthlyOn(1, '03:00');
        $schedule->command('leaves:increment-repos')->weeklyOn(1, '03:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
