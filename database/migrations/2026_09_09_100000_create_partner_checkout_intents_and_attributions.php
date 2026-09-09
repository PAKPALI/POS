<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_checkout_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_payment_id')->unique()->constrained('subscription_payments')->cascadeOnDelete();
            $table->foreignId('subscription_account_id')->constrained('subscription_accounts')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->foreignId('partner_promo_code_id')->constrained('partner_promo_codes')->restrictOnDelete();
            $table->string('code', 24);
            $table->string('rule_version', 40)->default('partner-promotion-v1');
            $table->unsignedSmallInteger('discount_bps')->default(1000);
            $table->unsignedSmallInteger('candidate_commission_bps')->default(1000);
            $table->unsignedBigInteger('gross_amount');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('net_amount');
            $table->string('currency', 3)->default('XOF');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['subscription_account_id', 'status']);
        });

        Schema::create('partner_attributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_account_id')->unique()->constrained('subscription_accounts')->restrictOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->foreignId('partner_promo_code_id')->constrained('partner_promo_codes')->restrictOnDelete();
            $table->foreignId('first_subscription_payment_id')->unique()->constrained('subscription_payments')->restrictOnDelete();
            $table->unsignedInteger('acquisition_rank');
            $table->unsignedSmallInteger('commission_rate_bps');
            $table->string('rule_version', 40);
            $table->timestamp('attributed_at');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('reversed_at')->nullable();
            $table->string('reversal_reason')->nullable();
            $table->timestamps();
            $table->index(['partner_id', 'status', 'attributed_at']);
        });

        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->unsignedBigInteger('gross_amount')->nullable()->after('amount');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('gross_amount');
            // La relation est créée après le checkout intent pour éviter une dépendance circulaire de migration.
            $table->unsignedBigInteger('partner_checkout_intent_id')->nullable()->after('discount_amount')->unique();
            $table->string('promotion_rule_version', 40)->nullable()->after('partner_checkout_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropUnique(['partner_checkout_intent_id']);
            $table->dropColumn(['gross_amount', 'discount_amount', 'partner_checkout_intent_id', 'promotion_rule_version']);
        });
        Schema::dropIfExists('partner_attributions');
        Schema::dropIfExists('partner_checkout_intents');
    }
};
