<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_cash_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('to_cash_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->enum('type', ['IN', 'OUT', 'TRANSFER']);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ams_transactions');
    }
};
