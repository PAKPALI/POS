<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La revue obligatoire est retirée de l’interface : elle ne doit plus
        // maintenir les demandes confirmées dans un état d’attente.
        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'partners.risk_review_enabled'],
            ['value' => 'false', 'type' => 'boolean', 'updated_at' => now(), 'created_at' => now()],
        );

        // Une valeur nulle ou héritée de l’ancien mode manuel ne doit pas
        // désactiver silencieusement l’envoi automatique.
        DB::table('platform_settings')
            ->where('key', 'partners.auto_approval_max_xof')
            ->whereIn('value', ['0', ''])
            ->update(['value' => '100000000', 'type' => 'integer', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Migration de compatibilité : aucune donnée financière n’est annulée.
    }
};
