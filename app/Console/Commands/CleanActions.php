<?php

namespace App\Console\Commands;

use App\Models\Action;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanActions extends Command
{
    protected $signature = 'actions:clean';
    protected $description = 'Supprime les actions sauf celles du dimanche courant';

    public function handle()
    {
        $today = Carbon::today();

        $startSunday = $today->copy()->startOfDay();
        $endSunday = $today->copy()->endOfDay();

        Log::info('Début nettoyage des actions');

        $deleted = Action::where(function ($query) use ($startSunday, $endSunday) {
            $query->where('created_at', '<', $startSunday)
                  ->orWhere('created_at', '>', $endSunday);
        })->delete();

        Log::info("Nombre supprimé : {$deleted}");

        return Command::SUCCESS;
    }
}