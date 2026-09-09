<?php

namespace App\Jobs;

use App\Models\PartnerExport;
use App\Services\PartnerDashboardQueryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GeneratePartnerCommissionExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $exportId) {}

    public function handle(PartnerDashboardQueryService $dashboard): void
    {
        $export = PartnerExport::query()->with('partner')->find($this->exportId);
        if (! $export || $export->status !== 'queued' || ! $export->partner) {
            return;
        }

        $export->update(['status' => 'processing']);
        $path = 'partner-exports/partner-'.$export->partner_id.'/commissions-'.$export->id.'.csv';

        try {
            $dashboard->writeCommissionCsv($export->partner, $export->filters ?? [], $path);
            $export->update([
                'status' => 'completed',
                'path' => $path,
                'completed_at' => now(),
                'expires_at' => now()->addDays(7),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $export->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => 'La génération du fichier a échoué. Réessayez plus tard.',
            ]);
        }
    }
}
