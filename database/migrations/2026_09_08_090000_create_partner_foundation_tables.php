<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('username', 30);
            $table->string('normalized_username', 30)->unique();
            $table->string('email');
            $table->string('normalized_email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->char('phone_country_code', 2);
            $table->string('phone_e164', 20)->unique();
            $table->char('country_code', 2);
            $table->string('password');
            $table->string('status', 20)->default('pending_email')->index();
            $table->boolean('two_factor_login_enabled')->default(false);
            $table->unsignedInteger('auth_version')->default(1);
            $table->string('appearance_mode', 10)->default('system');
            $table->string('accent_color', 7)->default('#FF9F43');
            $table->unsignedInteger('qualified_clients_count')->default(0);
            $table->unsignedSmallInteger('current_rate_bps')->default(1000);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->index(['status', 'email_verified_at']);
        });

        Schema::create('partner_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('partner_two_factor_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->restrictOnDelete();
            $table->string('purpose', 30);
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->timestamp('consumed_at')->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['partner_id', 'purpose', 'consumed_at', 'expires_at'], 'partner_2fa_lookup_index');
        });

        Schema::create('partner_promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->restrictOnDelete();
            $table->string('code', 24);
            $table->string('normalized_code', 24)->unique();
            $table->string('status', 20)->default('active');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['partner_id', 'status']);
        });

        Schema::create('partner_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action')->index();
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->string('result', 20)->default('success')->index();
            $table->timestamps();
            $table->index(['partner_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        foreach ([
            'partners.enabled' => 'false',
            'partners.registration_enabled' => 'false',
            'partners.payouts_enabled' => 'false',
            'partners.first_discount_bps' => '1000',
            'partners.max_commission_bps' => '2500',
            'partners.commission_hold_days' => '7',
            'partners.payout_min_xof' => '5000',
            'partners.payout_min_qualified_clients' => '3',
            'partners.auto_approval_max_xof' => '0',
            'partners.code_change_cooldown_days' => '30',
            'partners.risk_review_enabled' => 'true',
        ] as $key => $value) {
            DB::table('platform_settings')->updateOrInsert(['key' => $key], [
                'value' => $value,
                'type' => in_array($value, ['true', 'false'], true) ? 'boolean' : 'integer',
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')->whereIn('key', [
            'partners.enabled', 'partners.registration_enabled', 'partners.payouts_enabled',
            'partners.first_discount_bps', 'partners.max_commission_bps', 'partners.commission_hold_days',
            'partners.payout_min_xof', 'partners.payout_min_qualified_clients',
            'partners.auto_approval_max_xof', 'partners.code_change_cooldown_days', 'partners.risk_review_enabled',
        ])->delete();
        Schema::dropIfExists('partner_audit_logs');
        Schema::dropIfExists('partner_promo_codes');
        Schema::dropIfExists('partner_two_factor_challenges');
        Schema::dropIfExists('partner_password_reset_tokens');
        Schema::dropIfExists('partners');
    }
};
