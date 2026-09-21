<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Partner;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use App\Services\PlatformConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InAppGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_manager_can_read_the_company_guide(): void
    {
        [$owner, $company] = $this->ownerWithCompany();

        $this->withSession(['active_company_id' => $company->id])
            ->actingAs($owner)
            ->get(route('subscriptions.guide'))
            ->assertOk()
            ->assertSee('Bien utiliser')
            ->assertSee('Point de vente')
            ->assertSee('l’application reste consultable');
    }

    public function test_subscription_guide_pdf_downloads_for_the_company_manager(): void
    {
        [$owner, $company] = $this->ownerWithCompany();

        $this->withSession(['active_company_id' => $company->id])
            ->actingAs($owner)
            ->get(route('subscriptions.guide.pdf'))
            ->assertOk()
            ->assertDownload('guide-utilisation-guide-client-sarl.pdf');
    }

    public function test_active_partner_can_read_the_partner_guide(): void
    {
        PlatformSetting::query()
            ->where('key', 'partners.enabled')
            ->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);

        $partner = Partner::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->withSession(['partner_auth_version' => $partner->auth_version])
            ->actingAs($partner, 'partner')
            ->get(route('partner.guide'))
            ->assertOk()
            ->assertSee('Guide partenaire')
            ->assertSee('Mon code partenaire')
            ->assertSee('Commissions');
    }

    public function test_partner_guide_pdf_downloads_for_an_active_partner(): void
    {
        PlatformSetting::query()
            ->where('key', 'partners.enabled')
            ->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);

        $partner = Partner::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->withSession(['partner_auth_version' => $partner->auth_version])
            ->actingAs($partner, 'partner')
            ->get(route('partner.guide.pdf'))
            ->assertOk()
            ->assertDownload('guide-utilisation-'.str(config('app.name'))->slug().'.pdf');
    }

    private function ownerWithCompany(): array
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = Company::create([
            'name' => 'Guide Client SARL',
            'email' => 'guide-client@example.test',
            'number1' => '90000000',
            'created_by' => $owner->id,
        ]);

        $membership = app(CompanyProvisioner::class)->provision($company, $owner);
        app(CompanyContext::class)->set($company, $membership->fresh('role.permissions'));

        return [$owner, $company];
    }
}
