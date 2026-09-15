<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_details', function (Blueprint $table): void {
            $table->index(
                ['company_id', 'created_at', 'product_id', 'quantity'],
                'sale_details_tenant_period_product_quantity_index'
            );
            $table->index(
                ['company_id', 'sale_id', 'product_id', 'quantity'],
                'sale_details_tenant_sale_product_quantity_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table): void {
            $table->dropIndex('sale_details_tenant_period_product_quantity_index');
            $table->dropIndex('sale_details_tenant_sale_product_quantity_index');
        });
    }
};
