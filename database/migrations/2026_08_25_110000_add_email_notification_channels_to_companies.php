<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('sale_email_enabled')->default(true)->after('whatsapp_count');
            $table->boolean('inventory_email_enabled')->default(true)->after('sale_sms_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['sale_email_enabled', 'inventory_email_enabled']);
        });
    }
};
