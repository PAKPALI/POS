<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company_settings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->boolean('email_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('sms_enabled')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'user_id', 'category']);
        });

        $members = DB::table('company_user')
            ->join('roles', 'roles.id', '=', 'company_user.role_id')
            ->where('company_user.status', 'active')
            ->whereIn('roles.key', ['owner', 'admin'])
            ->get(['company_user.company_id', 'company_user.user_id']);
        foreach ($members as $member) {
            foreach (['sale', 'inventory'] as $category) {
                DB::table('notification_recipients')->insert([
                    'company_id' => $member->company_id, 'user_id' => $member->user_id,
                    'category' => $category, 'email_enabled' => true,
                    'whatsapp_enabled' => true, 'sms_enabled' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
