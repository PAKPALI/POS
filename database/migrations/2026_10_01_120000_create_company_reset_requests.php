<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_reset_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('company_settings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('email');
            $table->string('company_name');
            $table->string('session_hash', 64);
            $table->string('code_hash');
            $table->json('selection');
            $table->json('summary');
            $table->string('fingerprint', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('terms_accepted_at');
            $table->string('terms_version')->default('2026-10-01');
            $table->string('request_ip', 45)->nullable();
            $table->timestamps();
            $table->index(['company_id', 'created_at']);
        });
        Schema::table('quota_payments', fn (Blueprint $table) => $table->timestamp('reset_hidden_at')->nullable()->index());
        Schema::table('company_settings', fn (Blueprint $table) => $table->timestamp('data_reset_at')->nullable());
    }

    public function down(): void
    {
        Schema::table('quota_payments', fn (Blueprint $table) => $table->dropColumn('reset_hidden_at'));
        Schema::table('company_settings', fn (Blueprint $table) => $table->dropColumn('data_reset_at'));
        Schema::dropIfExists('company_reset_requests');
    }
};
