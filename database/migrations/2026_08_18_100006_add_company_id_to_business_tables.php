<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'categories', 'products', 'suppliers', 'clients', 'code_promos',
        'sales', 'sale_details', 'menu_products', 'inventories',
        'cash_accounts', 'transactions', 'settings', 'actions', 'order_items',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('company_id')->nullable()->after('id')
                        ->constrained('company_settings')->cascadeOnDelete();
                });
            }
        }

        $companyId = DB::table('company_settings')->orderBy('id')->value('id');
        if (!$companyId) {
            return;
        }

        foreach ($this->tables as $tableName) {
            DB::table($tableName)->whereNull('company_id')->update(['company_id' => $companyId]);
        }
        DB::table('orders')->whereNull('company_id')->update(['company_id' => $companyId]);

        $ownerRoleId = DB::table('roles')->where('company_id', $companyId)->where('key', 'owner')->value('id');
        if (!$ownerRoleId) {
            $ownerRoleId = DB::table('roles')->insertGetId([
                'company_id' => $companyId, 'name' => 'Propriétaire', 'key' => 'owner',
                'is_system' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('company_user')->updateOrInsert(
                ['company_id' => $companyId, 'user_id' => $userId],
                ['role_id' => $ownerRoleId, 'status' => 'active', 'joined_at' => now(),
                 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropConstrainedForeignId('company_id'));
            }
        }
    }
};
