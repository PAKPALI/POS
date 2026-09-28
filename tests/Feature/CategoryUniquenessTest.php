<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class CategoryUniquenessTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_a_company_cannot_create_two_categories_with_the_same_name(): void
    {
        $owner = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($owner, 'category-unique-create');

        Category::create([
            'company_id' => $company->id,
            'name' => 'Boissons',
            'created_by' => $owner->id,
            'status' => 1,
        ]);

        $this->actingAs($owner)->withSession(['active_company_id' => $company->id])
            ->postJson(route('category.store'), ['name' => '  boissons  '])
            ->assertOk()
            ->assertJson(['status' => false])
            ->assertJsonPath('msg', 'Cette catégorie existe déjà dans votre entreprise.');

        $this->assertSame(1, Category::where('company_id', $company->id)->count());
    }

    public function test_a_company_cannot_rename_a_category_to_an_existing_name(): void
    {
        $owner = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($owner, 'category-unique-update');
        $first = Category::create(['company_id' => $company->id, 'name' => 'Boissons', 'created_by' => $owner->id, 'status' => 1]);
        $second = Category::create(['company_id' => $company->id, 'name' => 'Épicerie', 'created_by' => $owner->id, 'status' => 1]);

        $this->actingAs($owner)->withSession(['active_company_id' => $company->id])
            ->putJson(route('category.update', $second->id), ['name' => ' boissons '])
            ->assertOk()
            ->assertJson(['status' => false])
            ->assertJsonPath('msg', 'Cette catégorie existe déjà dans votre entreprise.');

        $this->assertSame('Épicerie', $second->fresh()->name);
        $this->assertSame(2, Category::where('company_id', $company->id)->count());
    }
}
