<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_invitations', function (Blueprint $table) {
            $table->foreignId('invited_by')->nullable()->after('role_id')->constrained('users')->nullOnDelete();
            $table->timestamp('declined_at')->nullable()->after('accepted_at');
            $table->timestamp('revoked_at')->nullable()->after('declined_at');
            $table->timestamp('last_sent_at')->nullable()->after('expires_at');
            $table->unique('token_hash');
            $table->index(['company_id', 'accepted_at', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::table('company_invitations', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'accepted_at', 'revoked_at']);
            $table->dropUnique(['token_hash']);
            $table->dropConstrainedForeignId('invited_by');
            $table->dropColumn(['declined_at', 'revoked_at', 'last_sent_at']);
        });
    }
};
