<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('sale_whatsapp_enabled')->default(true);
            $table->boolean('sale_sms_enabled')->default(false);
            $table->boolean('inventory_whatsapp_enabled')->default(true);
            $table->boolean('inventory_sms_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sale_whatsapp_enabled', 'sale_sms_enabled',
                'inventory_whatsapp_enabled', 'inventory_sms_enabled',
            ]);
        });
    }
};
