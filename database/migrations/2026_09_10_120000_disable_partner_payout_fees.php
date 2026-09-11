<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // `with_fees` est distinct du tarif KPrimePay ; aucun tarif n'est modifié ici.
    }

    public function down(): void
    {
        // Migration conservée pour compatibilité d'historique.
    }
};
