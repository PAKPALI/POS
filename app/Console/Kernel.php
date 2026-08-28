<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected $commands = [
        \App\Console\Commands\CleanActions::class,
        \App\Console\Commands\SendWeeklyInventoryReport::class,
        \App\Console\Commands\CleanNotificationDeliveries::class,
        \App\Console\Commands\ReconcileKprimePayPayments::class,
        \App\Console\Commands\CreatePlatformAdmin::class,
        \App\Console\Commands\RecordPlatformHeartbeat::class,
        \App\Console\Commands\CheckPlatformAlerts::class,
    ];
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('actions:clean --days=365')->weeklyOn(0, '23:59')->withoutOverlapping();
        $schedule->command('inventory:weekly-report')->weeklyOn(0, '23:59');
        $schedule->command('notifications:clean-deliveries --days=180')->weeklyOn(0, '23:30')->withoutOverlapping();
        $schedule->command('payments:reconcile-kprimepay --limit=100')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('platform:heartbeat')->everyMinute()->withoutOverlapping();
        $schedule->command('platform:check-alerts')->everyFiveMinutes()->withoutOverlapping();
        // $schedule->command('actions:clean')->everyMinute();
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
