<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->string('type', 40);
            $table->json('filters')->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->string('path')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason', 300)->nullable();
            $table->timestamps();
            $table->index(['partner_id', 'type', 'created_at'], 'partner_export_history_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_exports');
    }
};
