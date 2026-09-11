<?php

namespace App\Jobs;

use App\Models\PartnerWithdrawal;
use App\Services\PartnerPayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/** Vérifie un retrait déjà envoyé ; ce job ne lance jamais un nouveau payout. */
class ReconcilePartnerWithdrawal implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public int $uniqueFor = 55;

    public function __construct(public readonly int $withdrawalId)
    {
        $this->onQueue('withdrawals');
    }

    public function uniqueId(): string
    {
        return 'partner-withdrawal-reconcile:'.$this->withdrawalId;
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->uniqueId()))->releaseAfter(15)->expireAfter(120),
        ];
    }

    public function handle(PartnerPayoutService $payouts): void
    {
        $withdrawal = PartnerWithdrawal::query()->find($this->withdrawalId);
        if (!$withdrawal || !in_array($withdrawal->status, PartnerWithdrawal::OPEN_STATUSES, true)) {
            return;
        }

        $payouts->reconcile($withdrawal);
    }
}
