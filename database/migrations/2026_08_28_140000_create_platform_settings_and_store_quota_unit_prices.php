<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type', 30)->default('string');
            $table->foreignId('updated_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('platform_setting_histories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->text('reason');
            $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('quota_payments', function (Blueprint $table) {
            $table->unsignedInteger('sms_unit_price')->nullable()->after('sms_quantity');
            $table->unsignedInteger('whatsapp_unit_price')->nullable()->after('whatsapp_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('quota_payments', function (Blueprint $table) {
            $table->dropColumn(['sms_unit_price', 'whatsapp_unit_price']);
        });
        Schema::dropIfExists('platform_setting_histories');
        Schema::dropIfExists('platform_settings');
    }
};
