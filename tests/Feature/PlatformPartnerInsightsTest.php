<?php

namespace Tests\Feature;

use App\Jobs\SendPartnerPlatformAlert;
use App\Mail\PartnerPlatformAlertMail;
use App\Models\Partner;
use App\Models\PartnerPromoCode;
use App\Models\PlatformAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformPartnerInsightsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin', string $email = 'platform@example.test'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => ucfirst(str_replace('_', ' ', $role)),
            'email' => $email,
            'password' => Hash::make('SecurePassword!123'),
            'role' => $role,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    private function partner(string $username = 'partnerinsight'): Partner
    {
        $partner = Partner::factory()->create([
            'name' => 'Partenaire Insight',
            'username' => $username,
            'normalized_username' => $username,
            'email' => $username.'@example.test',
            'normalized_email' => $username.'@example.test',
            'status' => 'active',
            'email_verified_at' => now(),
            'qualified_clients_count' => 4,
        ]);

        PartnerPromoCode::create([
            'partner_id' => $partner->id,
            'code' => 'INSIGHT2026',
            'normalized_code' => 'INSIGHT2026',
            'status' => 'active',
            'is_primary' => true,
            'activated_at' => now(),
        ]);

        return $partner;
    }

    public function test_platform_partner_overview_and_detail_are_available_to_support(): void
    {
        $admin = $this->admin('support', 'support@example.test');
        $partner = $this->partner();

        $this->actingAs($admin, 'platform')
            ->get(route('platform.partners.index', ['period' => 90]))
            ->assertOk()
            ->assertSee('Programme partenaires')
            ->assertSee('Partenaire Insight')
            ->assertSee('Dynamique du programme')
            ->assertSee('Flux financiers partenaires');

        $this->actingAs($admin, 'platform')
            ->get(route('platform.partners.show', $partner))
            ->assertOk()
            ->assertSee('Partenaire Insight')
            ->assertSee('INSIGHT2026')
            ->assertSee('Identité et accès')
            ->assertSee('Activité du partenaire')
            ->assertDontSee('Commissions récentes');
    }

    public function test_finance_can_see_commissions_and_withdrawals_on_partner_detail(): void
    {
        $admin = $this->admin('finance', 'finance@example.test');
        $partner = $this->partner('financepartner');

        $this->actingAs($admin, 'platform')
            ->get(route('platform.partners.show', $partner))
            ->assertOk()
            ->assertSee('Commissions récentes')
            ->assertSee('Retraits');
    }

    public function test_partner_alert_job_uses_default_role_recipients(): void
    {
        Mail::fake();
        $this->admin('support', 'support-alert@example.test');
        $partner = $this->partner('alertpartner');

        (new SendPartnerPlatformAlert('partner_registered', $partner->id, 'partner:'.$partner->id))->handle(app(\App\Services\PlatformConfigurationService::class));

        Mail::assertSent(PartnerPlatformAlertMail::class, function (PartnerPlatformAlertMail $mail) {
            return $mail->hasTo('support-alert@example.test') && $mail->partner->name === 'Partenaire Insight';
        });
    }
}
