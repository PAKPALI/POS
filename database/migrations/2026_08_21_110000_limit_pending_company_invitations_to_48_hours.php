<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $maximumExpiration = now()->addHours(48);

        DB::table('company_invitations')
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', $maximumExpiration)
            ->update(['expires_at' => $maximumExpiration]);
    }

    public function down(): void
    {
        // Une ancienne date d’expiration ne peut pas être restaurée de façon fiable.
    }
};
