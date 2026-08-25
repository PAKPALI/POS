<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_location_url', 2048)->nullable()->after('customer_address');
            $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_location_url');
            $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_location_url', 'delivery_latitude', 'delivery_longitude']);
        });
    }
};
