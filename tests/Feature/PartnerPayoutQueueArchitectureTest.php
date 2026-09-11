<?php

namespace Tests\Feature;

use App\Jobs\ReconcilePartnerWithdrawal;
use App\Services\PartnerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PartnerPayoutQueueArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_job_uses_the_withdrawals_queue_and_does_not_relaunch_a_closed_withdrawal(): void
    {
        $payouts = Mockery::mock(PartnerPayoutService::class);
        $payouts->shouldNotReceive('reconcile');

        $job = new ReconcilePartnerWithdrawal(999999);
        $job->handle($payouts);

        $this->assertSame('withdrawals', $job->queue);
        $this->assertSame('partner-withdrawal-reconcile:999999', $job->uniqueId());
        $this->assertSame([10, 30, 60], $job->backoff());
    }
}
