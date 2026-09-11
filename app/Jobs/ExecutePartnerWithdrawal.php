<?php

namespace App\Jobs;

use App\Services\PartnerPayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecutePartnerWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 30;

    public function __construct(public readonly int $withdrawalId)
    {
        $this->onQueue('withdrawals');
    }

    public function handle(PartnerPayoutService $payouts): void
    {
        $payouts->execute($this->withdrawalId);
    }
}
