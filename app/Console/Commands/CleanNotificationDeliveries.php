<?php

namespace App\Console\Commands;

use App\Models\NotificationDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanNotificationDeliveries extends Command
{
    protected $signature = 'notifications:clean-deliveries
        {--days=180 : Nombre de jours à conserver}
        {--pretend : Compter sans supprimer}';

    protected $description = 'Supprime les anciens états de livraison des notifications';

    public function handle(): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);
        if ($days === false || $days < 30) {
            $this->error('La durée de conservation doit être un entier d’au moins 30 jours.');

            return Command::INVALID;
        }

        $query = NotificationDelivery::query()->where('created_at', '<', now()->subDays($days));
        $count = $query->count();
        $pretend = (bool) $this->option('pretend');
        if (!$pretend && $count > 0) {
            $query->delete();
        }

        Log::info('Rétention des livraisons de notifications exécutée', [
            'retention_days' => $days,
            'pretend' => $pretend,
            'affected_deliveries' => $count,
        ]);
        $this->info($count.($pretend ? ' livraison(s) éligible(s).' : ' livraison(s) supprimée(s).'));

        return Command::SUCCESS;
    }
}
