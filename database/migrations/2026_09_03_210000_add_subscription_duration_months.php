<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->unsignedTinyInteger('duration_months')->default(1)->after('billing_period');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedTinyInteger('duration_months')->default(1)->after('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', fn (Blueprint $table) => $table->dropColumn('duration_months'));
        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropColumn('duration_months'));
    }
};
