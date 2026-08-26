<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationRecipientService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWeeklyInventoryReport extends Command
{
    protected $signature = 'inventory:weekly-report';
    protected $description = 'Envoi du rapport hebdomadaire des inventaires, compagnie par compagnie';

    public function handle(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $sentReports = 0;
        $failedDeliveries = 0;

        Company::active()->each(function (Company $company) use ($startOfWeek, $endOfWeek, &$sentReports, &$failedDeliveries) {
            app(CompanyContext::class)->setPublicCompany($company);

            if (!$company->inventory_email_enabled) {
                Log::info('Rapport inventaire ignoré : canal e-mail désactivé', ['company_id' => $company->id]);
                return;
            }

            $inventories = Inventory::with('product', 'user')
                ->whereBetween('created_at', [$startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay()])
                ->latest()
                ->get();

            if ($inventories->isEmpty()) {
                Log::info('Aucun inventaire cette semaine', ['company_id' => $company->id]);
                return;
            }

            $pdfContent = Pdf::loadView('component.inventory.pdf', [
                'inventories' => $inventories,
                'company' => $company,
                'start_date' => $startOfWeek,
                'end_date' => $endOfWeek,
            ])->output();

            $users = app(NotificationRecipientService::class)->users($company->id, 'inventory', 'email');
            $deliveryService = app(NotificationDeliveryService::class);
            $eventKey = $startOfWeek->toDateString().':'.$endOfWeek->toDateString();

            foreach ($users as $user) {
                try {
                    $sent = $deliveryService->deliver(
                        $company->id, 'weekly_inventory', $eventKey, 'inventory', 'email', $user->id,
                        function () use ($user, $pdfContent, $company, $startOfWeek, $endOfWeek): void {
                            Mail::send('emails.inventory.weeklyReport', [
                                'user' => $user,
                                'company' => $company,
                                'start_date' => $startOfWeek,
                                'end_date' => $endOfWeek,
                            ], function ($message) use ($user, $pdfContent, $company) {
                                $message->from(config('mail.from.address'), $company->name)
                                    ->to($user->email)
                                    ->subject('Rapport hebdomadaire inventaire - '.$company->name)
                                    ->attachData(
                                        $pdfContent,
                                        'rapport-inventaire-'.$company->slug.'.pdf',
                                        ['mime' => 'application/pdf']
                                    );
                            });
                        }
                    );
                    if ($sent) {
                        Log::info('Weekly inventory report sent', [
                            'company_id' => $company->id, 'user_id' => $user->id,
                        ]);
                    }
                } catch (Throwable $exception) {
                    $failedDeliveries++;
                    Log::warning('Weekly inventory report delivery failed', [
                        'company_id' => $company->id, 'user_id' => $user->id,
                        'error' => class_basename($exception),
                    ]);
                }
            }

            $sentReports++;
        });

        app(CompanyContext::class)->clear();
        $this->info("{$sentReports} rapport(s) compagnie traité(s)");

        return $failedDeliveries === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
