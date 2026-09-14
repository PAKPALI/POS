<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Partner;
use App\Models\User;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_company_owner_and_active_context(): void
    {
        $response = $this->postJson(route('register.store'), [
            'name' => 'Alice Martin',
            'company_name' => 'Boutique Alice',
            'email' => 'alice@example.test',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
            'user_type' => 2,
        ]);

        $response->assertOk()->assertJson(['status' => true]);

        $user = User::where('email', 'alice@example.test')->firstOrFail();
        $company = Company::where('name', 'Boutique Alice')->firstOrFail();
        $membership = $user->memberships()->where('company_id', $company->id)->with('role')->firstOrFail();

        $this->assertSame('owner', $membership->role->key);
        $this->assertSame('active', $membership->status);
        $response->assertSessionHas('active_company_id', $company->id);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company->id, 'name' => 'Caisse principale', 'is_default' => 1, 'is_tax' => 0,
        ]);
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company->id, 'name' => 'Caisse de taxe', 'is_default' => 0, 'is_tax' => 1,
        ]);
        $this->assertDatabaseHas('settings', ['company_id' => $company->id, 'default_tax' => null]);
    }

    public function test_company_name_is_required_during_registration(): void
    {
        $this->postJson(route('register.store'), [
            'name' => 'Alice Martin',
            'email' => 'alice@example.test',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
            'user_type' => 2,
        ])->assertOk()->assertJson(['status' => false]);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('company_settings', 0);
    }

    public function test_registration_rejects_an_email_already_used_by_a_pos_account(): void
    {
        User::factory()->create(['email' => 'existing-pos@example.test']);

        $response = $this->postJson(route('register.store'), [
            'name' => 'Nouvel administrateur',
            'company_name' => 'Nouvelle boutique',
            'email' => 'EXISTING-POS@example.test',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
            'country_code' => 'TG',
        ]);

        $response->assertOk()->assertJson(['status' => false]);
        $this->assertStringContainsString('mail existe', (string) $response->json('msg'));
        $this->assertDatabaseCount('company_settings', 0);
    }

    public function test_registration_rejects_a_phone_already_used_by_a_pos_account(): void
    {
        User::factory()->create(['phone' => '90000001', 'country_code' => 'TG']);

        $response = $this->postJson(route('register.store'), [
            'name' => 'Nouvel administrateur',
            'company_name' => 'Nouvelle boutique',
            'email' => 'new-pos@example.test',
            'phone' => '+228 90 00 00 01',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
            'country_code' => 'TG',
        ]);

        $response->assertOk()->assertJson(['status' => false]);
        $this->assertSame('Ce numéro est déjà associé à un compte POS.', $response->json('msg'));
        $this->assertDatabaseCount('company_settings', 0);
    }

    public function test_pos_registration_identity_is_independent_from_partner_accounts(): void
    {
        Partner::factory()->create([
            'email' => 'shared-identity@example.test',
            'normalized_email' => 'shared-identity@example.test',
            'phone_country_code' => 'TG',
            'country_code' => 'TG',
            'phone_e164' => '+22890000002',
        ]);

        $response = $this->postJson(route('register.store'), [
            'name' => 'Administrateur POS',
            'company_name' => 'Boutique POS',
            'email' => 'SHARED-IDENTITY@example.test',
            'phone' => '90 00 00 02',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
            'country_code' => 'TG',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('users', [
            'email' => 'shared-identity@example.test',
            'phone' => '90000002',
            'country_code' => 'TG',
        ]);
    }
}
