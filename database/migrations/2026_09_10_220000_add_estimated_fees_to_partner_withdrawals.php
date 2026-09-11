<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table): void {
            $table->unsignedBigInteger('estimated_fees')->default(0)->after('fees');
        });

        // Les anciennes demandes avaient uniquement la colonne `fees` : elle représentait
        // alors le montant réservé. On la préserve comme estimation pour garder la piste d'audit.
        DB::table('partner_withdrawals')->update(['estimated_fees' => DB::raw('fees')]);
    }

    public function down(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table): void {
            $table->dropColumn('estimated_fees');
        });
    }
};
