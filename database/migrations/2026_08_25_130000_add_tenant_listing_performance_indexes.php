<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'cash_accounts',
        'categories',
        'clients',
        'code_promos',
        'suppliers',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index(
                ['company_id', 'status', 'created_at'],
                $this->indexName($table)
            ));
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex(
                $this->indexName($table)
            ));
        }
    }

    private function indexName(string $table): string
    {
        return $table.'_tenant_listing_index';
    }
};
