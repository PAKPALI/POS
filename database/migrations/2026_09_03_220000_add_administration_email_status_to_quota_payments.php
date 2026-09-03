<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_payments', function (Blueprint $table): void {
            $table->json('administration_email_status')->nullable()->after('failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('quota_payments', function (Blueprint $table): void {
            $table->dropColumn('administration_email_status');
        });
    }
};
