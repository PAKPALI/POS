<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use App\Models\CompanySetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SendWeeklyInventoryReport extends Command
{
    protected $signature = 'inventory:weekly-report';
    protected $description = 'Envoi du rapport hebdomadaire des inventaires';

    public function handle()
    {
        // 📅 Semaine en cours (lundi -> dimanche)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        // 📦 Inventaires semaine
        $inventories = Inventory::with('product', 'user')
            ->whereBetween('created_at', [
                $startOfWeek->format('Y-m-d 00:00:00'),
                $endOfWeek->format('Y-m-d 23:59:59')
            ])->latest()->get();

        if ($inventories->isEmpty()) {
            Log::info("Aucun inventaire cette semaine");
            return Command::SUCCESS;
        }

        $company = CompanySetting::first();

        // 📄 Génération PDF
        $pdf = Pdf::loadView('component.inventory.pdf', [
            'inventories' => $inventories,
            'company' => $company,
            'start_date' => $startOfWeek,
            'end_date' => $endOfWeek
        ]);

        $pdfContent = $pdf->output();

        // 👥 utilisateurs ciblés
        $users = User::where('status', 1)
            ->where('user_type', '!=', 1)
            ->get();

        foreach ($users as $user) {

            Mail::send('emails.inventory.weeklyReport', [
                'user' => $user,
                'company' => $company,
                'start_date' => $startOfWeek,
                'end_date' => $endOfWeek,
            ], function ($message) use ($user, $pdfContent, $company) {

                $message->to($user->email)
                    ->subject("Rapport hebdomadaire inventaire - " . ($company->name ?? config('app.name')));

                $message->attachData(
                    $pdfContent,
                    'rapport-inventaire.pdf',
                    ['mime' => 'application/pdf']
                );
            });

            Log::info("Weekly inventory report sent to {$user->email}");
        }

        $this->info("Rapport envoyé avec succès");

        return Command::SUCCESS;
    }
}