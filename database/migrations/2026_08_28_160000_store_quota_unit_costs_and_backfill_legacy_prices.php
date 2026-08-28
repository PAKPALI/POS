<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_payments', function (Blueprint $table) {
            $table->unsignedInteger('sms_unit_cost')->nullable()->after('sms_unit_price');
            $table->unsignedInteger('whatsapp_unit_cost')->nullable()->after('whatsapp_unit_price');
        });

        DB::table('quota_payments')->update([
            'sms_unit_cost' => 15,
            'whatsapp_unit_cost' => 15,
        ]);

        DB::table('quota_payments')
            ->whereNull('sms_unit_price')
            ->whereNull('whatsapp_unit_price')
            ->whereRaw('amount = (sms_quantity * 35) + (whatsapp_quantity * 30)')
            ->update(['sms_unit_price' => 35, 'whatsapp_unit_price' => 30]);
    }

    public function down(): void
    {
        Schema::table('quota_payments', function (Blueprint $table) {
            $table->dropColumn(['sms_unit_cost', 'whatsapp_unit_cost']);
        });
    }
};
