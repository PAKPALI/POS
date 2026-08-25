<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceSlugCustomizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_check_and_confirm_a_unique_custom_storefront_slug(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = Company::create([
            'name' => 'Boutique Principale',
            'email' => 'principale@test.local',
            'number1' => '100',
            'description' => 'Repère de la boutique principale',
            'ecommerce_active' => true,
        ]);
        $otherCompany = Company::create([
            'name' => 'Adresse Occupée',
            'email' => 'occupee@test.local',
            'number1' => '200',
            'ecommerce_active' => true,
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Propriétaire',
            'key' => 'owner',
            'is_system' => true,
        ]);
        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $owner->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)->withSession([
            'active_company_id' => $company->id,
            'active_company_name' => $company->name,
        ]);

        $this->getJson(route('ecommerce.slug.check', ['slug' => 'Ma Boutique Élégante']))
            ->assertOk()
            ->assertJson([
                'status' => true,
                'available' => true,
                'slug' => 'ma-boutique-elegante',
            ]);

        $this->getJson(route('ecommerce.slug.check', ['slug' => $otherCompany->slug]))
            ->assertOk()
            ->assertJson(['available' => false]);

        $originalSlug = $company->slug;
        $this->postJson(route('ecommerce.settings.update'), [
            'slug' => 'ma-boutique-elegante',
        ])->assertUnprocessable()->assertJson([
            'status' => false,
            'requires_confirmation' => true,
        ]);
        $this->assertSame($originalSlug, $company->fresh()->slug);

        $this->postJson(route('ecommerce.settings.update'), [
            'slug' => 'admin',
            'confirm_slug_change' => true,
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $this->postJson(route('ecommerce.settings.update'), [
            'slug' => $otherCompany->slug,
            'company_id' => $otherCompany->id,
            'confirm_slug_change' => true,
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $response = $this->postJson(route('ecommerce.settings.update'), [
            'slug' => 'Ma Boutique Élégante',
            'company_id' => $otherCompany->id,
            'description' => $company->description,
            'ecommerce_active' => true,
            'confirm_slug_change' => true,
        ])->assertOk()->assertJson([
            'status' => true,
            'slug' => 'ma-boutique-elegante',
        ]);

        $this->assertSame('ma-boutique-elegante', $company->fresh()->slug);
        $this->assertSame('adresse-occupee', $otherCompany->fresh()->slug);
        $this->get('/boutique/'.$originalSlug)
            ->assertOk()
            ->assertSee('No active company')
            ->assertDontSee('Repère de la boutique principale');
        $this->get($response->json('storefront_url'))
            ->assertOk()
            ->assertSee('Repère de la boutique principale');
    }
}
