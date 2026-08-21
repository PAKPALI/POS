<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Champs tenant新增
            $table->uuid('public_id')->nullable()->after('id');
            $table->string('slug')->nullable()->after('name');
            $table->string('status')->default('active')->after('slug');
            $table->string('timezone')->default('Africa/Douala')->after('status');
            $table->string('currency')->default('FCFA')->after('timezone');
            $table->string('locale')->default('fr')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['public_id', 'slug', 'status', 'timezone', 'currency', 'locale']);
        });
    }
};
