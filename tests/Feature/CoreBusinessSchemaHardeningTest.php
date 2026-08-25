<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoreBusinessSchemaHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_business_company_columns_are_required(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Le contrôle INFORMATION_SCHEMA cible MySQL.');
        }

        $tables = [
            'cash_accounts', 'categories', 'clients', 'code_promos', 'inventories',
            'menu_products', 'products', 'sale_details', 'sales', 'settings',
            'suppliers', 'transactions',
        ];

        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)
            ->where('COLUMN_NAME', 'company_id')
            ->pluck('IS_NULLABLE', 'TABLE_NAME');

        $this->assertCount(count($tables), $columns);
        foreach ($tables as $table) {
            $this->assertSame('NO', $columns[$table], $table.'.company_id doit être obligatoire.');
        }
    }

    public function test_actions_and_roles_are_required_after_legacy_archiving(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Le contrôle INFORMATION_SCHEMA cible MySQL.');
        }

        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', ['actions', 'roles'])
            ->where('COLUMN_NAME', 'company_id')
            ->pluck('IS_NULLABLE', 'TABLE_NAME');

        $this->assertSame('NO', $columns['actions']);
        $this->assertSame('NO', $columns['roles']);
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('legacy_tenant_records'));
    }
}
