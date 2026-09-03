<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Exceptions\SubscriptionLimitReached;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use App\Services\EntitlementService;
use App\Services\SubscriptionAccountService;
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

        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('Choisir la durée')->assertSee('Durée souhaitée')->assertSee('Expiration estimée')->assertSee('Réduction annuelle appliquée (1 mois offert)')->assertDontSee('Continuer vers KPrimePay');
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
    }

    public function test_trial_limits_are_enforced_inside_the_company_and_member_write_transactions(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        PlatformSetting::updateOrCreate(['key' => 'subscriptions.enforcement_enabled'], ['value' => '1', 'type' => 'string']);

        $this->actingAs($owner)->postJson(route('companies.store'), [
            'name' => 'Deuxième entreprise refusée',
            'email' => 'second-company@example.test',
            'adress' => 'Lomé',
            'number1' => '90101010',
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
