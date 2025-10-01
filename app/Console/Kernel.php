<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the AI prediction command to run every minute
        $schedule->command('incidents:predict-severity')->everyMinute();

        // Schedule the Firebase incident logs sync to run every minute
        $schedule->command('sync:incident-logs')->everyMinute();

        // Schedule the full Firebase to MySQL sync to run every minute
        $schedule->command('firebase:sync-all')->everyMinute();


    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
