<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\SubscriptionEvent;
use App\Models\User;
use App\Notifications\SubscriptionExpiryNotification;
use App\Services\CompanyProvisioner;
use App\Services\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionExpiryCommandTest extends TestCase
{
    use RefreshDatabase;

    private function subscription(): array
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = Company::create(['name' => 'Rappels abonnement', 'email' => 'rappels@example.test', 'number1' => '90112233', 'created_by' => $owner->id]);
        app(CompanyProvisioner::class)->provision($company, $owner);

        return [$company, app(EntitlementService::class)->current($company)];
    }

    public function test_expiry_command_emails_owner_and_admin_once_per_day(): void
    {
        [$company, $subscription] = $this->subscription();
        $admin = User::factory()->create(['status' => 1, 'email' => 'admin-rappels@example.test']);
        $adminRole = Role::where('company_id', $company->id)->where('key', 'admin')->firstOrFail();
        CompanyUser::create(['company_id' => $company->id, 'user_id' => $admin->id, 'role_id' => $adminRole->id, 'status' => 'active', 'joined_at' => now()]);
        Notification::fake();
        $subscription->update(['ends_at' => now()->addDays(3)->setTime(12, 0)]);

        $this->artisan('subscriptions:expire')->assertSuccessful();
        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(1, SubscriptionEvent::where('subscription_id', $subscription->id)->where('event_key', 'reminder:'.$subscription->id.':'.now()->toDateString())->count());
        $event = SubscriptionEvent::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertSame(3, $event->payload['days_remaining']);
        $this->assertSame('trial', $subscription->fresh()->status);
        $this->assertSame('sent', $event->payload['email'][(string) $admin->id]['status']);
        Notification::assertCount(2);
        Notification::assertSentTo($admin, SubscriptionExpiryNotification::class);
    }

    public function test_expiry_command_expires_the_subscription_and_journals_it_once(): void
    {
        [, $subscription] = $this->subscription();
        Notification::fake();
        $subscription->update(['ends_at' => now()->subMinute()]);

        $this->artisan('subscriptions:expire')->assertSuccessful();
        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame('expired', $subscription->fresh()->status);
        $this->assertSame(1, SubscriptionEvent::where('subscription_id', $subscription->id)->where('event_key', 'expired:'.$subscription->id)->count());
        Notification::assertCount(1);
    }
}
