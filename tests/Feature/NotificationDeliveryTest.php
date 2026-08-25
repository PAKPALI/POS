<?php

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use App\Models\User;
use App\Services\NotificationDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_a_sent_notification_is_not_delivered_twice(): void
    {
        $user = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($user, 'delivery-once');
        $service = app(NotificationDeliveryService::class);
        $calls = 0;

        $first = $service->deliver(
            $company->id, 'sale', '42', 'sale', 'email', $user->id,
            function () use (&$calls): void { $calls++; }
        );
        $second = $service->deliver(
            $company->id, 'sale', '42', 'sale', 'email', $user->id,
            function () use (&$calls): void { $calls++; }
        );

        $this->assertTrue($first);
        $this->assertFalse($second);
        $this->assertSame(1, $calls);
        $this->assertDatabaseHas('notification_deliveries', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'event_type' => 'sale',
            'event_key' => '42',
            'channel' => 'email',
            'status' => 'sent',
            'attempts' => 1,
        ]);
    }

    public function test_a_failed_notification_can_be_retried_without_losing_its_history(): void
    {
        $user = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($user, 'delivery-retry');
        $service = app(NotificationDeliveryService::class);

        try {
            $service->deliver(
                $company->id, 'inventory', '8', 'inventory', 'sms', $user->id,
                fn () => throw new RuntimeException('Erreur sensible non conservée')
            );
            $this->fail('La première tentative devait échouer.');
        } catch (RuntimeException) {
            // La livraison doit rester relançable.
        }

        $failed = NotificationDelivery::firstOrFail();
        $this->assertSame('failed', $failed->status);
        $this->assertSame('RuntimeException', $failed->last_error);
        $this->assertStringNotContainsString('sensible', $failed->last_error);

        $service->deliver(
            $company->id, 'inventory', '8', 'inventory', 'sms', $user->id,
            fn () => null
        );

        $failed->refresh();
        $this->assertSame('sent', $failed->status);
        $this->assertSame(2, $failed->attempts);
        $this->assertNotNull($failed->sent_at);
    }

    public function test_the_same_event_remains_isolated_between_two_companies(): void
    {
        $user = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $companyA = $this->activateCompanyFor($user, 'delivery-company-a');
        $companyB = $this->activateCompanyFor($user, 'delivery-company-b');
        $service = app(NotificationDeliveryService::class);
        $calls = 0;

        foreach ([$companyA, $companyB] as $company) {
            $service->deliver(
                $company->id, 'sale', '100', 'sale', 'email', $user->id,
                function () use (&$calls): void { $calls++; }
            );
        }

        $this->assertSame(2, $calls);
        $this->assertSame(2, NotificationDelivery::where('event_key', '100')->count());
    }

    public function test_a_revoked_member_cannot_receive_a_new_delivery(): void
    {
        $user = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($user, 'delivery-revoked');
        $user->memberships()->where('company_id', $company->id)->update(['status' => 'revoked']);

        $this->expectException(RuntimeException::class);
        app(NotificationDeliveryService::class)->deliver(
            $company->id, 'sale', '101', 'sale', 'email', $user->id,
            fn () => null
        );
    }

    public function test_old_delivery_records_have_a_safe_retention_command(): void
    {
        $user = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($user, 'delivery-retention');
        app(NotificationDeliveryService::class)->deliver(
            $company->id, 'sale', '102', 'sale', 'email', $user->id,
            fn () => null
        );
        NotificationDelivery::query()->update(['created_at' => now()->subDays(200)]);

        $this->artisan('notifications:clean-deliveries --days=180 --pretend')->assertSuccessful();
        $this->assertDatabaseCount('notification_deliveries', 1);

        $this->artisan('notifications:clean-deliveries --days=180')->assertSuccessful();
        $this->assertDatabaseCount('notification_deliveries', 0);
    }
}
