<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_details', fn (Blueprint $table) => $table->index(
            ['company_id', 'created_at', 'product_id'],
            'sale_details_tenant_period_product_index'
        ));
    }

    public function down(): void
    {
        Schema::table('sale_details', fn (Blueprint $table) => $table->dropIndex(
            'sale_details_tenant_period_product_index'
        ));
    }
};
