<?php

namespace Tests\Feature;

use App\Jobs\SendCompanyResetReceipt;
use App\Mail\CompanyResetMail;
use App\Models\{Category, Company, CompanyResetRequest, CompanyUser, Product, Role, User};
use App\Services\{CompanyContext, CompanyProvisioner, CompanyResetService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Mail};
use Tests\TestCase;

class CompanyResetTest extends TestCase
{
    use RefreshDatabase;

    private function setupCompany(): array
    {
        Mail::fake();
        $owner = User::create(['name' => 'Owner', 'email' => uniqid().'@test.local', 'password' => 'password', 'status' => '1']);
        $company = Company::create(['name' => 'Entreprise à réinitialiser', 'email' => uniqid().'@test.local', 'number1' => '90000000', 'created_by' => $owner->id]);
        $membership = app(CompanyProvisioner::class)->provision($company, $owner);
        app(CompanyContext::class)->set($company->fresh(), $membership->load('role.permissions'));
        $this->actingAs($owner)->withSession(['active_company_id' => $company->id]);
        $category = Category::create(['company_id' => $company->id, 'name' => 'Essai', 'created_by' => $owner->id]);
        $product = Product::create(['company_id' => $company->id, 'category_id' => $category->id, 'name' => 'Essai', 'created_by' => $owner->id, 'price' => 100, 'qte' => 10, 'type' => 1, 'status' => '1']);
        return [$owner, $company->fresh(), $category, $product];
    }

    private function challenge(array $selection): array
    {
        $response = $this->postJson(route('company.reset.challenge'), ['selection' => $selection, 'terms' => true, 'current_password' => 'password'])->assertOk();
        $code = null;
        Mail::assertSent(CompanyResetMail::class, function ($mail) use (&$code) { if ($mail->code) $code = $mail->code; return true; });
        return [$response->json('id'), $code];
    }

    public function test_full_catalog_reset_resolves_dependencies_preserves_other_company_and_billing(): void
    {
        [$owner, $company, $category, $product] = $this->setupCompany();
        $other = Company::create(['name' => 'Autre entreprise', 'email' => 'other@test.local', 'number1' => '90000001', 'created_by' => $owner->id]);
        $otherCategory = Category::create(['company_id' => $other->id, 'name' => 'Conserver', 'created_by' => $owner->id]);
        $pack = Product::create(['company_id' => $company->id, 'category_id' => $category->id, 'name' => 'Pack', 'created_by' => $owner->id, 'price' => 100, 'qte' => 5, 'type' => 2, 'status' => '1']);
        DB::table('menu_products')->insert(['company_id' => $company->id, 'menu_id' => $pack->id, 'product_id' => $product->id, 'quantity' => 1]);
        $saleId = DB::table('sales')->insertGetId(['company_id' => $company->id, 'code' => 12345678, 'received_amount' => 100, 'total_amount' => 100, 'remaining_amount' => 0, 'cashier' => 'Owner', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('sale_details')->insert(['company_id' => $company->id, 'sale_id' => $saleId, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'total_price' => 100, 'profit' => 0]);
        $orderId = DB::table('orders')->insertGetId(['company_id' => $company->id, 'code' => 'ORDER-TEST', 'customer_name' => 'Client', 'customer_phone' => '90000000', 'sale_id' => $saleId]);
        DB::table('order_items')->insert(['company_id' => $company->id, 'order_id' => $orderId, 'product_id' => $pack->id, 'product_name' => 'Pack', 'quantity' => 1, 'unit_price' => 100, 'total_price' => 100]);
        $cashId = DB::table('cash_accounts')->where('company_id', $company->id)->value('id');
        DB::table('transactions')->insert(['company_id' => $company->id, 'to_cash_id' => $cashId, 'type' => 'IN', 'amount' => 100, 'created_by' => $owner->id]);
        DB::table('cash_accounts')->where('id', $cashId)->update(['balance' => 100]);
        DB::table('inventories')->insert(['company_id' => $company->id, 'product_id' => $product->id, 'qte_added' => 10, 'qte_after' => 10, 'created_by' => $owner->id]);
        [$id, $code] = $this->challenge(['categories', 'cash_accounts']);
        $reset = CompanyResetRequest::findOrFail($id);
        $this->assertContains('sales', $reset->selection);
        $this->assertContains('orders', $reset->selection);
        $this->assertContains('inventories', $reset->selection);
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertOk();
        foreach (['sales', 'sale_details', 'products', 'menu_products', 'categories', 'inventories', 'orders', 'order_items', 'transactions'] as $table) {
            $this->assertSame(0, DB::table($table)->where('company_id', $company->id)->count(), $table);
        }
        $this->assertDatabaseHas('categories', ['id' => $otherCategory->id]);
        $this->assertSame(2, DB::table('cash_accounts')->where('company_id', $company->id)->count());
        $this->assertEquals(0, DB::table('cash_accounts')->where('company_id', $company->id)->sum('balance'));
        $this->assertEquals($company->sms_count, $company->fresh()->sms_count);
        $this->assertEquals($company->whatsapp_count, $company->fresh()->whatsapp_count);
        $this->assertSame($company->subscription_account_id, $company->fresh()->subscription_account_id);
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'user_id' => $owner->id]);
        (new SendCompanyResetReceipt($id))->handle();
        Mail::assertSent(CompanyResetMail::class, fn ($mail) => $mail->code === null && $mail->hasTo($owner->email));
        $this->assertNotNull($reset->fresh()->notification_sent_at);
        // Le même challenge ne peut pas effacer les nouvelles données.
        $newCategory = Category::create(['company_id' => $company->id, 'name' => 'Vrai catalogue', 'created_by' => $owner->id]);
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertOk();
        $this->assertDatabaseHas('categories', ['id' => $newCategory->id]);
    }

    public function test_non_owner_and_forged_owner_role_cannot_access_reset(): void
    {
        [, $company] = $this->setupCompany();
        $member = User::create(['name' => 'Autre', 'email' => 'member@test.local', 'password' => 'password', 'status' => '1']);
        $ownerRole = Role::where('company_id', $company->id)->where('key', 'owner')->firstOrFail();
        CompanyUser::create(['company_id' => $company->id, 'user_id' => $member->id, 'role_id' => $ownerRole->id, 'status' => 'active']);
        $this->actingAs($member)->get(route('company.reset.index'))->assertForbidden();
        $this->postJson(route('company.reset.challenge'), ['selection' => ['products'], 'terms' => true, 'current_password' => 'password'])->assertForbidden();
        $this->get(route('profil'))->assertOk()->assertDontSee('Réinitialiser l’entreprise');
    }

    public function test_password_terms_and_invalid_code_are_checked_without_deleting(): void
    {
        [, , , $product] = $this->setupCompany();
        $this->postJson(route('company.reset.challenge'), ['selection' => ['products'], 'terms' => false, 'current_password' => 'password'])->assertUnprocessable();
        $this->postJson(route('company.reset.challenge'), ['selection' => ['products'], 'terms' => true, 'current_password' => 'wrong'])->assertUnprocessable();
        [$id] = $this->challenge(['products']);
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => '000000', 'terms' => true])->assertUnprocessable();
        }
        $this->assertSame(5, CompanyResetRequest::findOrFail($id)->attempts);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_changed_data_and_expired_code_require_a_new_confirmation(): void
    {
        [$owner, $company, , $product] = $this->setupCompany();
        [$id, $code] = $this->challenge(['products']);
        Category::create(['company_id' => $company->id, 'name' => 'Nouvelle donnée', 'created_by' => $owner->id]);
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertUnprocessable();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        CompanyResetRequest::whereKey($id)->update(['expires_at' => now()->subMinute()]);
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertUnprocessable();
    }

    public function test_custom_roles_detach_members_only_in_active_company(): void
    {
        [$owner, $company] = $this->setupCompany();
        $member = User::create(['name' => 'Employé', 'email' => 'employee@test.local', 'password' => 'password']);
        $role = Role::create(['company_id' => $company->id, 'name' => 'Personnalisé', 'key' => 'custom', 'is_system' => false]);
        CompanyUser::create(['company_id' => $company->id, 'user_id' => $member->id, 'role_id' => $role->id, 'status' => 'active']);
        [$id, $code] = $this->challenge(['roles']);
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseMissing('company_user', ['company_id' => $company->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('users', ['id' => $member->id]);
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'user_id' => $owner->id]);
    }

    public function test_quota_history_is_hidden_without_deleting_payment_or_recrediting_balance(): void
    {
        [$owner, $company] = $this->setupCompany();
        $paidId = DB::table('quota_payments')->insertGetId(['company_id' => $company->id, 'user_id' => $owner->id, 'transaction_id' => 'TEST-PAID', 'idempotency_key' => 'TEST-PAID', 'amount' => 100, 'status' => 'paid']);
        $pendingId = DB::table('quota_payments')->insertGetId(['company_id' => $company->id, 'user_id' => $owner->id, 'transaction_id' => 'TEST-PENDING', 'idempotency_key' => 'TEST-PENDING', 'amount' => 100, 'status' => 'pending']);
        [$id, $code] = $this->challenge(['quota_history']);
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertOk();
        $this->assertNotNull(DB::table('quota_payments')->where('id', $paidId)->value('reset_hidden_at'));
        $this->assertNull(DB::table('quota_payments')->where('id', $pendingId)->value('reset_hidden_at'));
        $this->assertSame(2, DB::table('quota_payments')->where('company_id', $company->id)->count());
        $this->assertEquals($company->sms_count, $company->fresh()->sms_count);
    }

    public function test_failed_operation_rolls_back_every_deletion_and_does_not_consume_code(): void
    {
        [, , , $product] = $this->setupCompany();
        [$id, $code] = $this->challenge(['products']);
        $this->partialMock(CompanyResetService::class, function ($mock) use ($product) {
            $mock->shouldReceive('execute')->once()->andReturnUsing(function () use ($product) {
                DB::table('products')->where('id', $product->id)->delete();
                throw new \RuntimeException('Simulated reset failure');
            });
        });
        $this->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertServerError();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertNull(CompanyResetRequest::findOrFail($id)->completed_at);
    }

    public function test_page_lists_only_existing_sections_and_confirmation_email_uses_shared_brand(): void
    {
        $this->setupCompany();
        $this->get(route('company.reset.index'))->assertOk()->assertSee('reset-products', false)->assertDontSee('reset-clients', false)->assertSee('resetTerms', false);
        [$id, $code] = $this->challenge(['products']);
        $html = (new CompanyResetMail(CompanyResetRequest::findOrFail($id), $code))->render();
        $this->assertStringContainsString($code, $html);
        $this->assertStringContainsString('Entreprise à réinitialiser', $html);
    }

    public function test_code_cannot_be_used_from_another_session_or_company(): void
    {
        [, , , $product] = $this->setupCompany();
        [$id, $code] = $this->challenge(['products']);
        $this->withSession(['company_reset_binding' => 'another-session'])
            ->postJson(route('company.reset.execute'), ['id' => $id, 'code' => $code, 'terms' => true])->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
