<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_payments', function (Blueprint $table): void {
            $table->string('payment_terms_version', 30)->nullable()->after('currency');
            $table->timestamp('payment_terms_accepted_at')->nullable()->after('payment_terms_version');
        });
    }

    public function down(): void
    {
        Schema::table('quota_payments', function (Blueprint $table): void {
            $table->dropColumn(['payment_terms_version', 'payment_terms_accepted_at']);
        });
    }
};
