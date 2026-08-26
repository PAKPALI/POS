<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->whereNull('user_type')->update(['user_type' => '3']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->nullable(false)->change();
        });
    }
};
