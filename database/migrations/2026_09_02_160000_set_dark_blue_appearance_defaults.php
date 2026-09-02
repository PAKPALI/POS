<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('appearance_mode', 10)->default('dark')->change();
            $table->char('accent_color', 7)->default('#3B82F6')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('appearance_mode', 10)->default('system')->change();
            $table->char('accent_color', 7)->default('#FF9F43')->change();
        });
    }
};
