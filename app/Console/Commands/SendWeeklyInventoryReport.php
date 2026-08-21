<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\NotificationRecipientService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyInventoryReport extends Command
{
    protected $signature = 'inventory:weekly-report';
    protected $description = 'Envoi du rapport hebdomadaire des inventaires, compagnie par compagnie';

    public function handle(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $sentReports = 0;

        Company::active()->each(function (Company $company) use ($startOfWeek, $endOfWeek, &$sentReports) {
            app(CompanyContext::class)->setPublicCompany($company);

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

            foreach ($users as $user) {
                Mail::send('emails.inventory.weeklyReport', [
                    'user' => $user,
                    'company' => $company,
                    'start_date' => $startOfWeek,
                    'end_date' => $endOfWeek,
                ], function ($message) use ($user, $pdfContent, $company) {
                    $message->to($user->email)
                        ->subject('Rapport hebdomadaire inventaire - '.$company->name)
                        ->attachData(
                            $pdfContent,
                            'rapport-inventaire-'.$company->slug.'.pdf',
                            ['mime' => 'application/pdf']
                        );
                });

                Log::info('Weekly inventory report sent', [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                ]);
            }

            $sentReports++;
        });

        app(CompanyContext::class)->clear();
        $this->info("{$sentReports} rapport(s) compagnie traité(s)");

        return Command::SUCCESS;
    }
}
