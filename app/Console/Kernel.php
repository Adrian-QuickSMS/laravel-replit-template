<?php

namespace App\Console;

use App\Jobs\Alerting\DispatchBatchedAlertsJob;
use App\Jobs\Alerting\PlatformHealthCheckJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('rcs:cleanup-drafts --hours=24')
            ->daily()
            ->at('03:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Release held messages every minute (out-of-hours restriction)
        $schedule->command('message:release-held')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        // Purge expired message data daily at 02:00
        $schedule->command('message:purge-expired')
            ->daily()
            ->at('02:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Alerting Engine — Batch dispatch (every 5 minutes)
        $schedule->job(new DispatchBatchedAlertsJob)
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        // Alerting Engine — Platform health check (every 5 minutes)
        $schedule->job(new PlatformHealthCheckJob)
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
