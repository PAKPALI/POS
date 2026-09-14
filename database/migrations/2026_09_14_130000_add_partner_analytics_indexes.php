<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table): void {
            $table->index('created_at', 'partners_created_at_index');
            $table->index('email_verified_at', 'partners_email_verified_at_index');
        });

        Schema::table('partner_attributions', function (Blueprint $table): void {
            $table->index('attributed_at', 'partner_attributions_attributed_at_index');
        });

        Schema::table('partner_commissions', function (Blueprint $table): void {
            $table->index('created_at', 'partner_commissions_created_at_index');
            $table->index(['partner_id', 'created_at'], 'partner_commissions_partner_created_index');
        });

        Schema::table('partner_withdrawals', function (Blueprint $table): void {
            $table->index('requested_at', 'partner_withdrawals_requested_at_index');
            $table->index(['partner_id', 'requested_at'], 'partner_withdrawals_partner_requested_index');
        });
    }

    public function down(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table): void {
            $table->dropIndex('partner_withdrawals_requested_at_index');
            $table->dropIndex('partner_withdrawals_partner_requested_index');
        });

        Schema::table('partner_commissions', function (Blueprint $table): void {
            $table->dropIndex('partner_commissions_created_at_index');
            $table->dropIndex('partner_commissions_partner_created_index');
        });

        Schema::table('partner_attributions', function (Blueprint $table): void {
            $table->dropIndex('partner_attributions_attributed_at_index');
        });

        Schema::table('partners', function (Blueprint $table): void {
            $table->dropIndex('partners_created_at_index');
            $table->dropIndex('partners_email_verified_at_index');
        });
    }
};
