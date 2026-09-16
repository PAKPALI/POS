<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('social_network_prompt_hidden')->default(false)->after('product_registration_notice_dismissed');
            $table->timestamp('social_network_prompt_snoozed_until')->nullable()->after('social_network_prompt_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['social_network_prompt_hidden', 'social_network_prompt_snoozed_until']);
        });
    }
};
