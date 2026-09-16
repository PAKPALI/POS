<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ReconcilePartnerWithdrawal;
use App\Jobs\ReconcilePlatformWithdrawal;
use App\Models\PartnerWithdrawal;
use App\Models\PlatformWithdrawal;

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
        \App\Console\Commands\ExpireSubscriptions::class,
        \App\Console\Commands\MaturePartnerCommissions::class,
        \App\Console\Commands\ReconcilePartnerPayouts::class,
        \App\Console\Commands\SeedPartnerWithdrawalPreview::class,
        \App\Console\Commands\ExpirePromoCodes::class,
    ];
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('actions:clean --days=365')->weeklyOn(0, '23:59')->withoutOverlapping();
        $schedule->command('inventory:weekly-report')->weeklyOn(0, '23:59');
        $schedule->command('notifications:clean-deliveries --days=180')->weeklyOn(0, '23:30')->withoutOverlapping();
        $schedule->command('payments:reconcile-kprimepay --limit=100')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('platform:heartbeat')->everyMinute()->withoutOverlapping();
        $schedule->command('platform:check-alerts')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('subscriptions:expire')->dailyAt('00:05')->withoutOverlapping();
        $schedule->command('promo-codes:expire')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('partners:mature-commissions --limit=200')->hourly()->withoutOverlapping();
        // Le scheduler ne contacte jamais KPrimePay : il ne fait que mettre les retraits
        // en attente dans la queue. Le worker `queue:work --queue=withdrawals` les traite.
        $schedule->call(function (): void {
            PartnerWithdrawal::query()
                ->whereIn('status', ['processing', 'unknown'])
                ->where(function ($query): void {
                    $query->where(function ($processing): void {
                            $processing->where('status', 'processing')
                                ->whereNotNull('processing_at')
                                ->where('processing_at', '<=', now()->subMinute());
                        })
                        ->orWhere(function ($unknown): void {
                            $unknown->where('status', 'unknown')
                                ->whereNotNull('unknown_at')
                                ->where('unknown_at', '<=', now()->subMinute());
                        });
                })
                ->oldest('id')
                ->limit(100)
                ->pluck('id')
                ->each(fn ($id) => ReconcilePartnerWithdrawal::dispatch((int) $id)->onQueue('withdrawals'));
        })->name('partners.dispatch-reconciliations')->everyMinute()->withoutOverlapping(1);
        $schedule->call(function (): void {
            PlatformWithdrawal::query()
                ->whereIn('status', ['processing', 'unknown'])
                ->where(function ($query): void {
                    // Un retrait explicitement absent chez KPrimePay attend une
                    // réautorisation opérateur ; il ne doit pas recréer un job
                    // de réconciliation à chaque minute.
                    $query->whereNull('provider_status')->orWhere('provider_status', '<>', 'transaction_not_found');
                })
                ->where(function ($query): void {
                    $query->where(function ($processing): void {
                        $processing->where('status', 'processing')->whereNotNull('processing_at')->where('processing_at', '<=', now()->subMinute());
                    })->orWhere(function ($unknown): void {
                        $unknown->where('status', 'unknown')->whereNotNull('unknown_at')->where('unknown_at', '<=', now()->subMinute());
                    });
                })->oldest('id')->limit(50)->pluck('id')
                ->each(fn ($id) => ReconcilePlatformWithdrawal::dispatch((int) $id)->onQueue('withdrawals'));
        })->name('platform.dispatch-treasury-reconciliations')->everyMinute()->withoutOverlapping(1);
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
