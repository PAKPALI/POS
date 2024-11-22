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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table ->foreignId('category_id')->nullable()->constrained() ->onDelete('cascade');
            $table->string('name');
            $table->integer('qte');
            $table->integer('margin')->nullable();
            $table->integer('price');
            $table->integer('purchase_price')->nullable();
            $table->integer('profit')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default(1);
            $table->string('email')->default(0);
            $table->integer('type');
            $table->integer('created_by');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
