<?php

namespace Tests\Feature;

use App\Models\Company;
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
        $response = $this->postJson(route('admin_register'), [
            'name' => 'Alice Martin',
            'company_name' => 'Boutique Alice',
            'email' => 'alice@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
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
        $this->assertDatabaseHas('settings', ['company_id' => $company->id, 'default_tax' => 0]);
    }

    public function test_company_name_is_required_during_registration(): void
    {
        $this->postJson(route('admin_register'), [
            'name' => 'Alice Martin',
            'email' => 'alice@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'user_type' => 2,
        ])->assertOk()->assertJson(['status' => false]);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('company_settings', 0);
    }
}
