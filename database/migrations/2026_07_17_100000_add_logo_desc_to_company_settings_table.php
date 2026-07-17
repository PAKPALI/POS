<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('message');
            $table->text('description')->nullable()->after('logo');
            $table->boolean('ecommerce_active')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['logo', 'description', 'ecommerce_active']);
        });
    }
};
