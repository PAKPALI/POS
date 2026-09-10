<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_withdrawal_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->char('country_code', 2);
            $table->string('gateway', 40);
            $table->text('phone_e164');
            $table->char('phone_fingerprint', 64);
            $table->string('beneficiary_name', 120);
            $table->string('status', 24)->default('pending_verification')->index();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['partner_id', 'phone_fingerprint'], 'partner_withdrawal_account_phone_unique');
            $table->index(['partner_id', 'status', 'is_primary']);
        });

        Schema::create('partner_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->foreignId('partner_withdrawal_account_id')->constrained('partner_withdrawal_accounts')->restrictOnDelete();
            $table->uuid('transaction_id')->unique();
            $table->string('idempotency_key', 140)->unique();
            $table->string('kpp_reference', 140)->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('fees')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->boolean('with_fees')->default(false);
            $table->string('status', 24)->default('requested')->index();
            $table->string('provider_status', 40)->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('unknown_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->text('review_reason')->nullable();
            $table->json('account_snapshot');
            $table->timestamps();
            $table->index(['partner_id', 'status', 'requested_at']);
        });

        Schema::create('partner_withdrawal_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_withdrawal_id')->constrained('partner_withdrawals')->cascadeOnDelete();
            $table->foreignId('partner_commission_id')->constrained('partner_commissions')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamps();
            $table->unique(['partner_withdrawal_id', 'partner_commission_id'], 'partner_withdrawal_allocation_unique');
        });

        Schema::create('partner_payout_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_withdrawal_id')->constrained('partner_withdrawals')->restrictOnDelete();
            $table->string('event_id', 140)->unique();
            $table->string('event_type', 60);
            $table->string('provider_status', 40)->nullable();
            $table->char('payload_hash', 64);
            $table->json('payload')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();
            $table->index(['partner_withdrawal_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_payout_events');
        Schema::dropIfExists('partner_withdrawal_allocations');
        Schema::dropIfExists('partner_withdrawals');
        Schema::dropIfExists('partner_withdrawal_accounts');
    }
};
