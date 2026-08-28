<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_alert_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->json('recipient_admin_ids')->nullable();
            $table->unsignedInteger('failed_jobs_threshold')->default(1);
            $table->unsignedInteger('queue_age_minutes')->default(15);
            $table->unsignedInteger('blocked_payment_minutes')->default(120);
            $table->unsignedTinyInteger('delivery_failure_percent')->default(25);
            $table->unsignedInteger('delivery_minimum_volume')->default(5);
            $table->unsignedInteger('cooldown_minutes')->default(60);
            $table->timestamps();
        });

        Schema::create('platform_operational_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('fingerprint')->index();
            $table->string('severity', 20)->default('warning')->index();
            $table->string('status', 20)->default('open')->index();
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('last_detected_at');
            $table->timestamp('last_notified_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['fingerprint', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_operational_alerts');
        Schema::dropIfExists('platform_alert_settings');
    }
};
