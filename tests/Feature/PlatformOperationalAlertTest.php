<?php

namespace Tests\Feature;

use App\Mail\PlatformSecurityMail;
use App\Models\PlatformAdmin;
use App\Models\PlatformOperationalAlert;
use App\Models\PlatformAlertSetting;
use App\Models\PlatformSystemHeartbeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformOperationalAlertTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): PlatformAdmin
    {
        return PlatformAdmin::create(['name' => 'Technique', 'email' => 'technical@example.test',
            'password' => Hash::make('SecurePassword!123'), 'role' => 'super_admin', 'is_active' => true,
            'must_change_password' => false, 'two_factor_enabled' => false]);
    }

    public function test_command_detects_scheduler_problem_and_respects_notification_cooldown(): void
    {
        Mail::fake(); $admin = $this->admin();
        PlatformAlertSetting::current()->update(['enabled' => true, 'recipient_admin_ids' => [$admin->id]]);
        PlatformSystemHeartbeat::query()->delete();
        $this->artisan('platform:check-alerts')->assertSuccessful();
        $this->assertDatabaseHas('platform_operational_alerts', ['type' => 'scheduler.delayed', 'status' => 'open']);
        Mail::assertSent(PlatformSecurityMail::class, 1);
        $this->artisan('platform:check-alerts')->assertSuccessful();
        Mail::assertSent(PlatformSecurityMail::class, 1);
    }

    public function test_settings_and_alert_lifecycle_are_protected_and_audited(): void
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin, 'platform')->put(route('platform.alerts.settings'), [
            'enabled' => true, 'recipient_admin_ids' => [$admin->id], 'failed_jobs_threshold' => 2,
            'queue_age_minutes' => 20, 'blocked_payment_minutes' => 180, 'delivery_failure_percent' => 30,
            'delivery_minimum_volume' => 10, 'cooldown_minutes' => 90, 'reason' => 'Réglage initial des alertes',
            'current_password' => 'SecurePassword!123',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('platform_alert_settings', ['failed_jobs_threshold' => 2, 'cooldown_minutes' => 90]);

        $alert = PlatformOperationalAlert::create(['type'=>'test','fingerprint'=>'test','severity'=>'warning','status'=>'open',
            'title'=>'Test','message'=>'Incident de test','detected_at'=>now(),'last_detected_at'=>now()]);
        $this->post(route('platform.alerts.acknowledge', $alert))->assertSessionHas('success');
        $this->assertSame('acknowledged', $alert->fresh()->status);
        $this->post(route('platform.alerts.resolve', $alert), ['reason'=>'Incident corrigé'])->assertSessionHas('success');
        $this->assertSame('resolved', $alert->fresh()->status);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.alert.resolved', 'target_id' => (string) $alert->id]);
    }

    public function test_alert_index_supports_search_filters_and_server_pagination(): void
    {
        $admin = $this->admin();
        foreach (range(1, 11) as $number) {
            PlatformOperationalAlert::create(['type' => 'queue.delayed', 'fingerprint' => 'queue-'.$number,
                'severity' => 'warning', 'status' => 'open', 'title' => 'Queue ralentie '.$number,
                'message' => 'Le job attend depuis trop longtemps.', 'detected_at' => now(), 'last_detected_at' => now()]);
        }
        PlatformOperationalAlert::create(['type' => 'payments.blocked', 'fingerprint' => 'payment-resolved',
            'severity' => 'warning', 'status' => 'resolved', 'title' => 'Paiement résolu', 'message' => 'Incident clos.',
            'detected_at' => now(), 'last_detected_at' => now()]);

        $response = $this->actingAs($admin, 'platform')->get(route('platform.alerts.index', [
            'search' => 'Queue', 'status' => 'open', 'per_page' => 10,
        ]));

        $response->assertOk()->assertSee('Queue ralentie 1')->assertSee('Affichage de 1 à 10 sur 11')
            ->assertSee('page=2');
    }
}
