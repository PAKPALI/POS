<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformSubscriptionCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): PlatformAdmin
    {
        return PlatformAdmin::create(['name' => 'Administrateur SaaS', 'email' => $role.'@example.test', 'password' => Hash::make('SecurePassword!123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false]);
    }

    private function versionPayload(): array
    {
        return ['name' => 'Bronze Plus', 'monthly_price' => 6000, 'annual_price' => 66000, 'company_limit' => 2, 'user_limit' => 4, 'product_limit' => 200, 'sms_quota' => 25, 'whatsapp_quota' => 25, 'features' => ['suppliers' => '1', 'ecommerce' => '1'], 'reason' => 'Nouvelle grille commerciale', 'current_password' => 'SecurePassword!123'];
    }

    public function test_catalog_creates_a_draft_version_without_changing_the_published_plan(): void
    {
        $admin = $this->admin();
        $bronze = SubscriptionPlan::where('key', 'bronze')->firstOrFail();
        $original = $bronze->only(['name', 'monthly_price', 'annual_price', 'is_active', 'version']);

        $this->actingAs($admin, 'platform')->post(route('platform.subscriptions.plans.versions.store', $bronze), $this->versionPayload())
            ->assertRedirect(route('platform.subscriptions.catalog'));

        $draft = SubscriptionPlan::where('key', 'bronze-v2')->firstOrFail();
        $this->assertSame(2, $draft->version);
        $this->assertFalse($draft->is_active);
        $this->assertSame(6000, (int) $draft->monthly_price);
        $this->assertSame($original, $bronze->fresh()->only(array_keys($original)));
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'subscription.plan_version.created', 'target_id' => (string) $draft->id]);
    }

    public function test_super_admin_can_open_the_catalogue(): void
    {
        $this->actingAs($this->admin(), 'platform')->get(route('platform.subscriptions.catalog'))
            ->assertOk()
            ->assertSee('Versions des plans');
    }

    public function test_publish_replaces_only_the_catalog_version_for_future_checkouts(): void
    {
        $admin = $this->admin();
        $bronze = SubscriptionPlan::where('key', 'bronze')->firstOrFail();
        $this->actingAs($admin, 'platform')->post(route('platform.subscriptions.plans.versions.store', $bronze), $this->versionPayload());
        $draft = SubscriptionPlan::where('key', 'bronze-v2')->firstOrFail();

        $this->actingAs($admin, 'platform')->post(route('platform.subscriptions.plans.publish', $draft), ['reason' => 'Publication tarif validée', 'current_password' => 'SecurePassword!123'])
            ->assertRedirect(route('platform.subscriptions.catalog'));

        $this->assertFalse($bronze->fresh()->is_active);
        $this->assertTrue($draft->fresh()->is_active);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'subscription.plan_version.published', 'target_id' => (string) $draft->id]);
    }

    public function test_invalid_annual_price_or_non_super_admin_cannot_change_catalog(): void
    {
        $bronze = SubscriptionPlan::where('key', 'bronze')->firstOrFail();
        $badPayload = $this->versionPayload();
        $badPayload['annual_price'] = 60001;
        $this->actingAs($this->admin(), 'platform')->from(route('platform.subscriptions.catalog'))
            ->post(route('platform.subscriptions.plans.versions.store', $bronze), $badPayload)
            ->assertRedirect(route('platform.subscriptions.catalog'))->assertSessionHasErrors('annual_price');
        $this->assertDatabaseMissing('subscription_plans', ['key' => 'bronze-v2']);

        $this->actingAs($this->admin('finance'), 'platform')->get(route('platform.subscriptions.catalog'))->assertForbidden();
    }
}
