<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array<int, string>> */
    private array $indexes = [
        'actions' => ['company_id', 'created_at'],
        'inventories' => ['company_id', 'created_at'],
        'orders' => ['company_id', 'created_at'],
        'products' => ['company_id', 'status', 'qte'],
        'sale_details' => ['company_id', 'product_id'],
        'sales' => ['company_id', 'created_at'],
        'transactions' => ['company_id', 'created_at'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index(
                $columns,
                $this->indexName($table)
            ));
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes, true) as $table => $columns) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex(
                $this->indexName($table)
            ));
        }
    }

    private function indexName(string $table): string
    {
        return $table.'_tenant_query_index';
    }
};
