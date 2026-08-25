<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $tables = [
        'cash_accounts' => 'cascade',
        'categories' => 'cascade',
        'clients' => 'cascade',
        'code_promos' => 'cascade',
        'inventories' => 'cascade',
        'menu_products' => 'cascade',
        'products' => 'cascade',
        'sale_details' => 'cascade',
        'sales' => 'cascade',
        'settings' => 'cascade',
        'suppliers' => 'cascade',
        'transactions' => 'cascade',
    ];

    public function up(): void
    {
        $blockingTables = collect(array_keys($this->tables))
            ->filter(fn (string $table) => DB::table($table)->whereNull('company_id')->exists())
            ->values();

        if ($blockingTables->isNotEmpty()) {
            throw new RuntimeException(
                'Durcissement annulé avant modification : company_id est absent dans '.
                $blockingTables->implode(', ').'. Effectuez un backfill validé puis relancez la migration.'
            );
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table => $deleteRule) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign(['company_id']));
            DB::statement("ALTER TABLE `{$table}` MODIFY `company_id` BIGINT UNSIGNED NOT NULL");
            Schema::table($table, function (Blueprint $blueprint) use ($deleteRule) {
                $foreign = $blueprint->foreign('company_id')->references('id')->on('company_settings');
                $deleteRule === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table => $deleteRule) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign(['company_id']));
            DB::statement("ALTER TABLE `{$table}` MODIFY `company_id` BIGINT UNSIGNED NULL");
            Schema::table($table, function (Blueprint $blueprint) use ($deleteRule) {
                $foreign = $blueprint->foreign('company_id')->references('id')->on('company_settings');
                $deleteRule === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
            });
        }
    }
};
