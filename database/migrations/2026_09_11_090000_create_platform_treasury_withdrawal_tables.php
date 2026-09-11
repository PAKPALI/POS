<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_withdrawal_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('platform_admin_id')->constrained('platform_admins')->restrictOnDelete();
            $table->char('country_code', 2);
            $table->string('gateway', 40);
            $table->text('phone_e164');
            $table->char('phone_fingerprint', 64);
            $table->string('beneficiary_name', 120);
            $table->string('status', 24)->default('pending_verification')->index();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['platform_admin_id', 'phone_fingerprint'], 'platform_withdrawal_account_phone_unique');
            $table->index(['platform_admin_id', 'status', 'is_primary'], 'platform_withdrawal_account_admin_status_primary_idx');
        });

        Schema::create('platform_withdrawal_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('platform_admin_id')->constrained('platform_admins')->restrictOnDelete();
            $table->string('purpose', 32);
            $table->string('code_hash');
            $table->json('payload')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->timestamps();
            $table->index(['platform_admin_id', 'purpose', 'expires_at'], 'platform_withdrawal_challenge_admin_purpose_exp_idx');
        });

        Schema::create('platform_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('platform_admin_id')->constrained('platform_admins')->restrictOnDelete();
            $table->foreignId('platform_withdrawal_account_id')->constrained('platform_withdrawal_accounts')->restrictOnDelete();
            $table->uuid('transaction_id')->unique();
            $table->string('idempotency_key', 140)->unique();
            $table->string('kpp_reference', 140)->nullable()->unique();
            $table->string('event_id', 140)->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('estimated_fees')->default(0);
            $table->unsignedBigInteger('fees')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->boolean('with_fees')->default(false);
            $table->string('status', 24)->default('otp_verified')->index();
            $table->string('provider_status', 40)->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('account_snapshot');
            $table->json('funding_snapshot');
            $table->timestamp('requested_at');
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('unknown_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_withdrawals');
        Schema::dropIfExists('platform_withdrawal_challenges');
        Schema::dropIfExists('platform_withdrawal_accounts');
    }
};
