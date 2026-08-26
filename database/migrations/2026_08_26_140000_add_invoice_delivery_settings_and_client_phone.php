<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', fn (Blueprint $table) => $table->string('phone', 30)->nullable()->after('name'));
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('invoice_whatsapp_enabled')->default(false)->after('sale_sms_enabled');
            $table->boolean('invoice_sms_enabled')->default(false)->after('invoice_whatsapp_enabled');
        });
    }
    public function down(): void
    {
        Schema::table('company_settings', fn (Blueprint $table) => $table->dropColumn(['invoice_whatsapp_enabled', 'invoice_sms_enabled']));
        Schema::table('clients', fn (Blueprint $table) => $table->dropColumn('phone'));
    }
};
