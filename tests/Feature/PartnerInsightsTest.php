<?php

namespace Tests\Feature;

use App\Jobs\GeneratePartnerCommissionExport;
use App\Models\Company;
use App\Models\Partner;
use App\Models\PartnerExport;
use App\Models\PartnerPromoCode;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\PartnerDashboardQueryService;
use App\Services\PlatformConfigurationService;
use App\Services\SubscriptionAccountService;
use App\Services\SubscriptionCheckoutService;
use App\Services\SubscriptionSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerInsightsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled', 'partners.commission_hold_days']);
    }

    private function partner(string $username): Partner
    {
        return Partner::factory()->create([
            'name' => 'Partenaire '.$username,
            'username' => $username,
            'normalized_username' => $username,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function attributedPayment(Partner $partner, string $companyName, string $email): array
    {
        $user = User::factory()->create(['email' => $email]);
        $company = Company::create([
            'name' => $companyName,
            'email' => $email,
            'number1' => '90000000',
            'country_code' => 'TG',
            'created_by' => $user->id,
        ]);
        app(SubscriptionAccountService::class)->ensureFor($company, $user->id);
        $code = PartnerPromoCode::create([
            'partner_id' => $partner->id,
            'code' => strtoupper($partner->username).'2026',
            'normalized_code' => strtoupper($partner->username).'2026',
            'status' => 'active',
            'is_primary' => true,
            'activated_at' => now(),
        ]);
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 1, $code->code);
        app(SubscriptionSettlementService::class)->creditVerified($payment, [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500,
        ]);

        return [$company->fresh(), $payment->fresh()];
    }

    public function test_dashboard_shows_immediately_available_commission_and_navigation(): void
    {
        $partner = $this->partner('insightone');
        $this->attributedPayment($partner, 'Entreprise visible', 'visible@example.test');

        $this->actingAs($partner, 'partner')->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('Solde disponible')
            ->assertSee('500 XOF')
            ->assertSee('Mes clients')
            ->assertSee('Mes commissions')
            ->assertSee('Dernières commissions');
    }

    public function test_clients_and_commissions_are_partner_isolated_and_do_not_expose_contact_details(): void
    {
        $firstPartner = $this->partner('isolateone');
        $secondPartner = $this->partner('isolatetwo');
        [$firstCompany] = $this->attributedPayment($firstPartner, 'Entreprise Alpha', 'alpha.private@example.test');
        $this->attributedPayment($secondPartner, 'Entreprise Beta', 'beta.private@example.test');

        $this->actingAs($firstPartner, 'partner')->get(route('partner.clients'))
            ->assertOk()
            ->assertSee('Entreprise Alpha')
            ->assertDontSee('Entreprise Beta')
            ->assertDontSee('alpha.private@example.test')
            ->assertDontSee($firstCompany->number1);

        $this->actingAs($firstPartner, 'partner')->get(route('partner.commissions'))
            ->assertOk()
            ->assertSee('Entreprise Alpha')
            ->assertDontSee('Entreprise Beta')
            ->assertSee('Voir le calcul')
            ->assertSee('id="commissionPeriod"', false)
            ->assertSee('bootstrap-daterangepicker/daterangepicker.css', false)
            ->assertDontSee('id="commissionFrom" type="date"', false);
    }

    public function test_commission_export_is_queued_generated_and_private_to_its_partner(): void
    {
        Storage::fake('local');
        Queue::fake();
        $owner = $this->partner('exportowner');
        $other = $this->partner('exportother');
        $this->attributedPayment($owner, 'Entreprise Export', 'export.private@example.test');

        $this->actingAs($owner, 'partner')->post(route('partner.commissions.export'), ['status' => 'available'])
            ->assertSessionHas('success');
        $export = PartnerExport::firstOrFail();
        $this->assertSame($owner->id, $export->partner_id);
        $this->assertSame('queued', $export->status);
        Queue::assertPushed(GeneratePartnerCommissionExport::class, fn (GeneratePartnerCommissionExport $job) => $job->exportId === $export->id);

        (new GeneratePartnerCommissionExport($export->id))->handle(app(PartnerDashboardQueryService::class));
        $export->refresh();
        $this->assertSame('completed', $export->status);
        Storage::disk('local')->assertExists($export->path);

        $this->actingAs($other, 'partner')->get(route('partner.exports.download', $export))->assertNotFound();
        $this->actingAs($owner, 'partner')->get(route('partner.exports.download', $export))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
