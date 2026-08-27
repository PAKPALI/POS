<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('company_settings', fn (Blueprint $table) => $table->char('country_code', 2)->default('TG')->after('locale'));
        Schema::table('users', fn (Blueprint $table) => $table->char('country_code', 2)->default('TG')->after('phone'));
        Schema::table('clients', fn (Blueprint $table) => $table->char('country_code', 2)->default('TG')->after('phone'));
    }
    public function down(): void
    {
        Schema::table('clients', fn (Blueprint $table) => $table->dropColumn('country_code'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('country_code'));
        Schema::table('company_settings', fn (Blueprint $table) => $table->dropColumn('country_code'));
    }
};
