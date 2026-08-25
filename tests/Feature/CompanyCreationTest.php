<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class CompanyCreationTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_creation_waits_for_confirmation_before_switching_company(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $firstCompany = $this->activateCompanyFor($owner, 'first');

        $response = $this->actingAs($owner)->postJson(route('company.store'), [
            'name' => 'Deuxième compagnie',
            'email' => 'second-company@test.local',
            'adress' => 'Lomé',
            'number1' => '90000000',
            'default_tax' => 18,
        ]);

        $response->assertOk()->assertJson([
            'status' => true,
            'selection_url' => route('companies.select'),
        ]);

        $secondCompany = Company::where('name', 'Deuxième compagnie')->firstOrFail();
        $this->assertNotSame($firstCompany->id, $secondCompany->id);
        $this->assertSame($firstCompany->id, session('active_company_id'));
        $this->assertDatabaseHas('company_user', [
            'company_id' => $secondCompany->id,
            'user_id' => $owner->id,
            'status' => 'active',
        ]);
        $mainCash = CashAccount::withoutCompanyScope()->where('company_id', $secondCompany->id)->where('is_default', true)->firstOrFail();
        $taxCash = CashAccount::withoutCompanyScope()->where('company_id', $secondCompany->id)->where('is_tax', true)->firstOrFail();
        $setting = Setting::withoutCompanyScope()->where('company_id', $secondCompany->id)->firstOrFail();
        $this->assertNotSame($mainCash->id, $taxCash->id);
        $this->assertSame($mainCash->id, $setting->default_cash_id);
        $this->assertSame($taxCash->id, $setting->tax_cash_id);
        $this->assertEquals(18, $setting->default_tax);

        $this->actingAs($owner)->get(route('companies.select'))
            ->assertOk()
            ->assertSee($firstCompany->name)
            ->assertSee($secondCompany->name)
            ->assertSee('Retour à l’application')
            ->assertSee(route('dashboard'), false);

        $this->actingAs($owner)
            ->post(route('companies.switch', $secondCompany->id))
            ->assertRedirect(route('dashboard'));
        $this->assertSame($secondCompany->id, session('active_company_id'));

        $this->actingAs($owner)->get(route('company.index'))
            ->assertOk()
            ->assertSee($firstCompany->name)
            ->assertSee($secondCompany->name)
            ->assertSee('Informations de la compagnie active');

        $this->actingAs($owner)->putJson(route('company.update', $secondCompany->id), [
            'name' => 'Compagnie renommée',
            'email' => $secondCompany->email,
            'adress' => $secondCompany->adress,
            'number1' => $secondCompany->number1,
        ])->assertOk()->assertJson(['status' => true]);

        $this->actingAs($owner)->get(route('company.index'))
            ->assertOk()->assertSee('Compagnie renommée');
    }
}
