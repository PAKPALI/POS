<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->foreignId('partner_attribution_id')->constrained('partner_attributions')->restrictOnDelete();
            $table->foreignId('subscription_payment_id')->unique()->constrained('subscription_payments')->restrictOnDelete();
            $table->string('type', 24);
            $table->unsignedBigInteger('gross_amount');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('net_paid_amount');
            $table->unsignedSmallInteger('commission_rate_bps');
            $table->unsignedBigInteger('commission_amount');
            $table->string('currency', 3)->default('XOF');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->string('rule_version', 40);
            $table->json('calculation_snapshot');
            $table->timestamps();
            $table->index(['partner_id', 'status', 'available_at'], 'partner_commission_availability_index');
            $table->index(['partner_attribution_id', 'created_at'], 'partner_commission_attribution_index');
        });

        Schema::create('partner_wallet_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->string('entry_type', 32);
            $table->string('bucket', 16);
            $table->string('direction', 8);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('XOF');
            $table->string('source_type', 48);
            $table->unsignedBigInteger('source_id');
            $table->string('idempotency_key', 140)->unique();
            $table->timestamp('occurred_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['partner_id', 'bucket', 'occurred_at'], 'partner_wallet_bucket_index');
            $table->index(['source_type', 'source_id'], 'partner_wallet_source_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_wallet_entries');
        Schema::dropIfExists('partner_commissions');
    }
};
