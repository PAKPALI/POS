<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->unique()->after('status')
                ->constrained('sales')->restrictOnDelete();
            $table->timestamp('converted_at')->nullable()->after('sale_id');
            $table->foreignId('converted_by')->nullable()->after('converted_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('converted_by');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')
                ->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 500)->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('sale_id');
            $table->dropColumn(['converted_at', 'cancelled_at', 'cancellation_reason']);
        });
    }
};
