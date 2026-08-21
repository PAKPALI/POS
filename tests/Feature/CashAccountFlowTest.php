<?php

namespace Tests\Feature;

use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\AMS\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\InteractsWithCompanies;

class CashAccountFlowTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'cash-test@example.com',
            'password' => bcrypt('password'),
            'user_type' => '1',
            'status' => '1',
        ]);
        $this->activateCompanyFor($this->user, 'cash');
    }

    /**
     * Test: Creating a cash account via controller
     * NOTE: Controller auto-generates the code
     */
    public function test_create_cash_account(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ams/cash-account', [
            'name' => 'Nouvelle Caisse',
            'description' => 'Test caisse',
        ]);

        $response->assertJson(['status' => true]);

        $cash = CashAccount::where('name', 'Nouvelle Caisse')->first();
        $this->assertNotNull($cash);
        $this->assertEquals('Nouvelle Caisse', $cash->name);
        $this->assertNotNull($cash->code); // Auto-generated
    }

    public function test_cash_creation_after_company_switch_uses_target_company_and_unique_code(): void
    {
        $companyA = $this->company;
        $this->actingAs($this->user)->postJson('/ams/cash-account', [
            'name' => 'Caisse Matrix',
        ])->assertJson(['status' => true]);

        $companyB = $this->activateCompanyFor($this->user, 'phenix');
        $this->actingAs($this->user)->withSession(['active_company_id' => $companyB->id])
            ->postJson('/ams/cash-account', ['name' => 'Caisse Phenix'])
            ->assertJson(['status' => true]);

        $matrixCash = CashAccount::withoutCompanyScope()->where('company_id', $companyA->id)
            ->where('name', 'Caisse Matrix')->firstOrFail();
        $phenixCash = CashAccount::withoutCompanyScope()->where('company_id', $companyB->id)
            ->where('name', 'Caisse Phenix')->firstOrFail();

        $this->assertNotSame($matrixCash->code, $phenixCash->code);
        $this->assertStringContainsString('CASH-'.$companyB->id.'-', $phenixCash->code);
    }

    /**
     * Test: Default cash is unique
     */
    public function test_set_default_cash(): void
    {
        $cash1 = CashAccount::create([
            'name' => 'Caisse 1',
            'code' => 'C001',
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $cash2 = CashAccount::create([
            'name' => 'Caisse 2',
            'code' => 'C002',
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 0,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        CashAccount::setDefaultCash($cash2->id);

        $cash1->refresh();
        $cash2->refresh();

        $this->assertEquals(0, $cash1->is_default);
        $this->assertEquals(1, $cash2->is_default);
    }

    public function test_same_cash_cannot_be_default_and_tax(): void
    {
        $cash = CashAccount::create([
            'name' => 'Caisse exclusive',
            'code' => 'EXC001',
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        CashAccount::setTaxCash($cash->id);
        $cash->refresh();
        $this->assertFalse((bool) $cash->is_default);
        $this->assertTrue((bool) $cash->is_tax);

        CashAccount::setDefaultCash($cash->id);
        $cash->refresh();
        $this->assertTrue((bool) $cash->is_default);
        $this->assertFalse((bool) $cash->is_tax);
    }

    public function test_creation_with_both_options_keeps_only_default(): void
    {
        $this->actingAs($this->user)->postJson('/ams/cash-account', [
            'name' => 'Caisse double demande',
            'is_default' => 1,
            'is_tax' => 1,
            'status' => 1,
        ])->assertJson(['status' => true]);

        $cash = CashAccount::where('name', 'Caisse double demande')->firstOrFail();
        $this->assertTrue((bool) $cash->is_default);
        $this->assertFalse((bool) $cash->is_tax);
    }

    /**
     * Test: Cash account balance is correct
     */
    public function test_cash_account_balance(): void
    {
        $cash = CashAccount::create([
            'name' => 'Test Caisse',
            'code' => 'TC001',
            'balance' => 50000,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(50000, $cash->balance);
    }

    /**
     * Test: Transaction creates correctly
     */
    public function test_create_transaction(): void
    {
        $cash = CashAccount::create([
            'name' => 'Test Caisse',
            'code' => 'TC002',
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $transaction = Transaction::create([
            'to_cash_id' => $cash->id,
            'type' => 'IN',
            'amount' => 25000,
            'description' => 'Test transaction',
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($transaction);
        $this->assertEquals('IN', $transaction->type);
        $this->assertEquals(25000, $transaction->amount);
        $this->assertEquals($cash->id, $transaction->to_cash_id);
    }

    /**
     * Test: Cash increment increases balance
     */
    public function test_cash_increment(): void
    {
        $cash = CashAccount::create([
            'name' => 'Test Caisse',
            'code' => 'TC003',
            'balance' => 10000,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $cash->increment('balance', 5000);
        $cash->refresh();

        $this->assertEquals(15000, $cash->balance);
    }

    /**
     * Test: Cash decrement decreases balance
     */
    public function test_cash_decrement(): void
    {
        $cash = CashAccount::create([
            'name' => 'Test Caisse',
            'code' => 'TC004',
            'balance' => 10000,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $cash->decrement('balance', 3000);
        $cash->refresh();

        $this->assertEquals(7000, $cash->balance);
    }

    /**
     * Test: Transaction types are valid
     */
    public function test_transaction_types(): void
    {
        $validTypes = ['IN', 'OUT', 'TRANSFER'];

        foreach ($validTypes as $type) {
            $cash = CashAccount::create([
                'name' => "Caisse {$type}",
                'code' => "C{$type}001",
                'balance' => 0,
                'currency' => 'FCFA',
                'is_default' => 0,
                'is_tax' => 0,
                'status' => 1,
                'created_by' => $this->user->id,
            ]);

            $transaction = Transaction::create([
                'to_cash_id' => $cash->id,
                'type' => $type,
                'amount' => 1000,
                'description' => "Test {$type}",
                'created_by' => $this->user->id,
            ]);

            $this->assertEquals($type, $transaction->type);
        }
    }

    /**
     * Test: Settings link to cash accounts
     */
    public function test_settings_link_to_cash(): void
    {
        $mainCash = CashAccount::create([
            'name' => 'Main Cash',
            'code' => 'MC001',
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $taxCash = CashAccount::create([
            'name' => 'Tax Cash',
            'code' => 'TXC001',
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 0,
            'is_tax' => 1,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $setting = Setting::create([
            'default_cash_id' => $mainCash->id,
            'tax_cash_id' => $taxCash->id,
            'default_tax' => 18.00,
        ]);

        $this->assertEquals($mainCash->id, $setting->cash->id);
        $this->assertEquals($taxCash->id, $setting->taxCash->id);
    }
}
