<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\PlatformSetting;
use App\Models\Role;
use App\Models\User;
use App\Exceptions\SubscriptionLimitReached;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use App\Services\EntitlementService;
use App\Services\SubscriptionAccountService;
use App\Services\SubscriptionCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubscriptionAccessTest extends TestCase
{
    use RefreshDatabase;

    private function ownerWithCompany(): array
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = Company::create([
            'name' => 'Protection abonnement',
            'email' => 'protection-abonnement@example.test',
            'number1' => '90000000',
            'created_by' => $owner->id,
        ]);
        $membership = app(CompanyProvisioner::class)->provision($company, $owner);
        app(CompanyContext::class)->set($company, $membership->fresh('role.permissions'));
        $this->withSession(['active_company_id' => $company->id]);

        return [$owner, $company];
    }

    public function test_new_company_owner_can_open_subscription_management(): void
    {
        [$owner] = $this->ownerWithCompany();

        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('Bronze Pro')->assertSee('7 500')->assertSee('Fournisseurs inclus')->assertSee('E-commerce inclus')->assertSee('Codes promo clients inclus')->assertSee('Choisir la durée')->assertSee('Durée souhaitée')->assertSee('Expiration estimée')->assertSee('Réduction annuelle appliquée (1 mois offert)')->assertSee('partner-promo-input')->assertSee('Code promotionnel partenaire')->assertSee('Continuer vers le paiement')->assertSee('Termes et conditions de paiement')->assertSee('redirigé vers la page de paiement KPrimePay')->assertSee('terms_accepted', false)->assertDontSee('Continuer vers KPrimePay');
    }

    public function test_subscription_checkout_requires_payment_terms_on_the_server(): void
    {
        [$owner] = $this->ownerWithCompany();

        $this->actingAs($owner)->postJson(route('subscriptions.checkout'), [
            'plan' => 'bronze', 'months' => 1,
        ])->assertStatus(422)->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_accepted_payment_terms_are_snapshotted_on_subscription_payment(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $owner->id, 'bronze', 1, null, '2026-09-11');

        $this->assertSame('2026-09-11', $payment->snapshot['payment_terms_version']);
        $this->assertNotEmpty($payment->snapshot['payment_terms_accepted_at']);
    }

    public function test_expired_plan_keeps_subscription_readable_but_blocks_member_and_role_writes(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        $subscription = app(EntitlementService::class)->current($company);
        $subscription->update(['ends_at' => now()->subMinute()]);
        PlatformSetting::updateOrCreate(['key' => 'subscriptions.enforcement_enabled'], ['value' => '1', 'type' => 'string']);
        app(EntitlementService::class)->current($company)->refresh();

        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk();
        $this->actingAs($owner)->post(route('roles.store'), ['name' => 'Opérateur', 'permissions' => []])->assertForbidden();
        $this->actingAs($owner)->post(route('user.attach-existing'), ['email' => 'new-member@example.test', 'role_id' => 999999])->assertForbidden();
        $this->actingAs($owner)->postJson(route('user.store'), [
            'name' => 'Utilisateur bloqué',
            'email' => 'utilisateur-bloque@example.test',
            'role_id' => Role::where('company_id', $company->id)->where('key', 'cashier')->value('id'),
            'phone' => '90000001',
        ])->assertForbidden();
    }

    public function test_expired_plan_blocks_quota_checkout_but_keeps_subscription_management_available(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        app(EntitlementService::class)->current($company)->update(['ends_at' => now()->subMinute()]);
        PlatformSetting::updateOrCreate(['key' => 'subscriptions.enforcement_enabled'], ['value' => '1', 'type' => 'string']);

        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk();
        $this->actingAs($owner)->post(route('sms-quota.checkout'), [
            'sms_quantity' => 1,
            'whatsapp_quantity' => 0,
            'terms_accepted' => true,
        ])->assertForbidden();
    }

    public function test_suspended_company_cannot_execute_business_writes(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        $company->update(['status' => 'suspended']);

        $this->actingAs($owner)->post(route('roles.store'), [
            'name' => 'Rôle qui ne doit pas être créé',
            'permissions' => [],
        ])->assertRedirect(route('companies.select'));

        $this->assertDatabaseMissing('roles', [
            'company_id' => $company->id,
            'name' => 'Rôle qui ne doit pas être créé',
        ]);
    }

    public function test_trial_limits_are_enforced_inside_the_company_and_member_write_transactions(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        PlatformSetting::updateOrCreate(['key' => 'subscriptions.enforcement_enabled'], ['value' => '1', 'type' => 'string']);

        $entitlements = app(EntitlementService::class);
        $this->assertTrue($entitlements->enforcementEnabledFor($company));
        $this->assertFalse($entitlements->canAdd($company->fresh(), 'company'));

        $this->actingAs($owner)->postJson(route('companies.store'), [
            'name' => 'Deuxième entreprise refusée',
            'email' => 'second-company@example.test',
            'adress' => 'Lomé',
            'number1' => '90101010',
            'country_code' => 'TG',
        ])->assertStatus(422)->assertJson(['title' => 'LIMITE DU PLAN ATTEINTE']);
        $this->assertDatabaseCount('company_settings', 1);

        $anotherUser = User::factory()->create();
        try {
            DB::transaction(fn () => app(EntitlementService::class)->assertCanAddUser($company, $anotherUser->id));
            $this->fail('La limite utilisateur de l’essai devait être refusée.');
        } catch (SubscriptionLimitReached $exception) {
            $this->assertSame('La limite d’utilisateurs de votre plan est atteinte.', $exception->getMessage());
        }
    }
}
