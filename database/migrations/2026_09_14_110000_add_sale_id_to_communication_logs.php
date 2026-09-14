<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_logs', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->after('company_id')->constrained('sales')->nullOnDelete();
            $table->index(['company_id', 'sale_id', 'sent_at'], 'communication_logs_sale_history');
        });
    }

    public function down(): void
    {
        Schema::table('communication_logs', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropIndex('communication_logs_sale_history');
            $table->dropColumn('sale_id');
        });
    }
};
