<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories') || !Schema::hasColumn('categories', 'company_id')) {
            return;
        }

        $hasDuplicates = DB::table('categories')
            ->select('company_id', 'name')
            ->whereNotNull('company_id')
            ->groupBy('company_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new RuntimeException(
                'Impossible d’ajouter la contrainte des catégories : des noms en double existent déjà dans une même entreprise.'
            );
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->unique(['company_id', 'name'], 'categories_company_name_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropUnique('categories_company_name_unique');
            });
        }
    }
};
