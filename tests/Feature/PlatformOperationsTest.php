<?php

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\PlatformSystemHeartbeat;
use App\Models\QuotaPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class PlatformOperationsTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private function admin(): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Administrateur exploitation', 'email' => 'ops@example.test',
            'password' => Hash::make('SecurePassword!123'), 'role' => 'super_admin',
            'is_active' => true, 'must_change_password' => false,
        ]);
    }

    public function test_heartbeat_command_records_scheduler_activity(): void
    {
        $this->artisan('platform:heartbeat')->assertSuccessful();
        $heartbeat = PlatformSystemHeartbeat::where('key', 'scheduler')->firstOrFail();
        $this->assertTrue($heartbeat->last_seen_at->greaterThan(now()->subMinute()));
        $this->assertSame('testing', $heartbeat->metadata['environment']);
    }

    public function test_audit_log_is_global_filterable_and_has_a_detail_page(): void
    {
        $admin = $this->admin();
        $matching = PlatformAuditLog::create([
            'platform_admin_id' => $admin->id, 'action' => 'company.suspended',
            'target_type' => 'App\\Models\\Company', 'target_id' => '42',
            'reason' => 'Contrôle de conformité', 'ip_address' => '127.0.0.1',
            'old_values' => ['status' => 'active'], 'new_values' => ['status' => 'suspended'],
        ]);
        PlatformAuditLog::create(['platform_admin_id' => $admin->id, 'action' => 'platform.login', 'result' => 'success']);

        $this->actingAs($admin, 'platform')->get(route('platform.audit.index', ['q' => 'conformité']))
            ->assertOk()->assertSee('company.suspended')->assertDontSee('platform.login');
        $this->actingAs($admin, 'platform')->get(route('platform.audit.show', $matching))
            ->assertOk()->assertSee('Contrôle de conformité')->assertSee('suspended');
    }

    public function test_health_page_summarizes_scheduler_deliveries_and_blocked_payments(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create();
        $company = $this->activateCompanyFor($owner, 'health-company');
        PlatformSystemHeartbeat::create(['key' => 'scheduler', 'last_seen_at' => now(), 'metadata' => ['environment' => 'testing']]);
        NotificationDelivery::create([
            'company_id' => $company->id, 'user_id' => $owner->id,
            'event_type' => 'sale', 'event_key' => 'sale-1', 'category' => 'sale',
            'channel' => 'sms', 'status' => 'failed', 'attempts' => 3, 'last_error' => 'provider_error',
        ]);
        $blockedPayment = QuotaPayment::create([
            'company_id' => $company->id, 'user_id' => $owner->id,
            'transaction_id' => 'QUOTA-BLOCKED', 'idempotency_key' => 'quota-blocked',
            'sms_quantity' => 1, 'sms_unit_price' => 35, 'sms_unit_cost' => 15,
            'whatsapp_quantity' => 0, 'whatsapp_unit_price' => 30, 'whatsapp_unit_cost' => 15,
            'amount' => 35, 'currency' => 'XOF', 'status' => 'pending',
        ]);
        $blockedPayment->forceFill(['created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)])->saveQuietly();

        $this->actingAs($admin, 'platform')->get(route('platform.health.index'))
            ->assertOk()
            ->assertSee('Opérationnel')
            ->assertSee('Délivrabilité sur les 7 derniers jours')
            ->assertSee('SMS')
            ->assertSee('Paiements en attente depuis plus de 2 h')
            ->assertSee('>1</div>', false);
    }

    public function test_retry_requires_a_reason_and_an_existing_failed_job(): void
    {
        $admin = $this->admin();
        $uuid = '11111111-1111-4111-8111-111111111111';
        $this->actingAs($admin, 'platform')->postJson(route('platform.health.jobs.retry', $uuid), ['reason' => 'non'])
            ->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->actingAs($admin, 'platform')->postJson(route('platform.health.jobs.retry', $uuid), ['reason' => 'Nouvelle tentative contrôlée'])
            ->assertNotFound();
    }
}
