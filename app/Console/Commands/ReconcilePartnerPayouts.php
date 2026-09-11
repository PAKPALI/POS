<?php

namespace App\Console\Commands;

use App\Models\PartnerWithdrawal;
use App\Services\PartnerPayoutService;
use Illuminate\Console\Command;
use Throwable;

class ReconcilePartnerPayouts extends Command
{
    protected $signature = 'partners:reconcile-payouts {--limit=100 : Nombre maximal de retraits} {--pretend : Vérifier sans modifier}';
    protected $description = 'Réconcilie les retraits partenaires en attente auprès de KPrimePay';

    public function handle(PartnerPayoutService $payouts): int
    {
        $limit = max(1, min(1000, (int) $this->option('limit')));
        $pretend = (bool) $this->option('pretend');
        $withdrawals = PartnerWithdrawal::query()
            ->whereIn('status', ['processing', 'unknown'])
            ->oldest('processing_at')
            ->limit($limit)
            ->get();

        $counts = ['success' => 0, 'failed' => 0, 'pending' => 0, 'errors' => 0];
        foreach ($withdrawals as $withdrawal) {
            try {
                if ($pretend) {
                    $counts['pending']++;
                    continue;
                }
                $status = $payouts->reconcile($withdrawal);
                if ($status === 'success') $counts['success']++;
                elseif (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled', 'rejected'], true)) $counts['failed']++;
                else $counts['pending']++;
            } catch (Throwable $exception) {
                report($exception);
                $counts['errors']++;
            }
        }

        $prefix = $pretend ? 'Simulation — ' : '';
        $this->info($prefix."analysés: {$withdrawals->count()}, réussis: {$counts['success']}, échoués: {$counts['failed']}, en attente: {$counts['pending']}, erreurs: {$counts['errors']}. ");
        return $counts['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
