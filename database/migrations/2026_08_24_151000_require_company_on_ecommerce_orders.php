<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE order_items oi INNER JOIN orders o ON o.id = oi.order_id SET oi.company_id = o.company_id WHERE oi.company_id IS NULL AND o.company_id IS NOT NULL');

        if (DB::table('orders')->whereNull('company_id')->exists()
            || DB::table('order_items')->whereNull('company_id')->exists()) {
            throw new \RuntimeException('Des commandes sans compagnie existent. Affectez-les manuellement avant de relancer cette migration.');
        }

        Schema::table('orders', fn (Blueprint $table) => $table->dropForeign(['company_id']));
        Schema::table('order_items', fn (Blueprint $table) => $table->dropForeign(['company_id']));

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY company_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE order_items MODIFY company_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('company_settings')->restrictOnDelete();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('company_settings')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropForeign(['company_id']));
        Schema::table('order_items', fn (Blueprint $table) => $table->dropForeign(['company_id']));

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY company_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE order_items MODIFY company_id BIGINT UNSIGNED NULL');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('company_settings')->nullOnDelete();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('company_settings')->cascadeOnDelete();
        });
    }
};
