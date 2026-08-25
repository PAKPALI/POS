<?php

namespace Tests\Unit;

use App\Jobs\SendEcommerceOrderEmailJob;
use App\Jobs\SendInventoryWhatsappJob;
use App\Jobs\SendMarginEmailJob;
use App\Jobs\SendSaleEmailJob;
use App\Jobs\SendSaleWhatsappJob;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NotificationJobReliabilityTest extends TestCase
{
    public static function jobs(): array
    {
        return [
            [new SendSaleEmailJob(1, 1)],
            [new SendSaleWhatsappJob(1, 1)],
            [new SendInventoryWhatsappJob(1, 1)],
            [new SendMarginEmailJob('Produit', 5, 4, 1)],
            [new SendEcommerceOrderEmailJob(1, 1)],
        ];
    }

    #[DataProvider('jobs')]
    public function test_notification_jobs_share_a_bounded_retry_policy(object $job): void
    {
        $this->assertSame(3, $job->tries);
        $this->assertSame(120, $job->timeout);
        $this->assertTrue($job->failOnTimeout);
        $this->assertSame([60, 300], $job->backoff());
    }
}
