<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quota_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company_settings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('transaction_id')->unique();
            $table->string('idempotency_key')->unique();
            $table->string('kpp_reference')->nullable()->index();
            $table->string('event_id')->nullable()->unique();
            $table->unsignedInteger('sms_quantity')->default(0);
            $table->unsignedInteger('whatsapp_quantity')->default(0);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('XOF');
            $table->string('status')->default('created')->index();
            $table->text('checkout_url')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'created_at']);
        });

        $permissionId = DB::table('permissions')->where('key', 'quota.manage')->value('id');
        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'key' => 'quota.manage',
                'module' => 'quota',
                'description' => 'Acheter des quotas SMS et WhatsApp',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        foreach (DB::table('roles')->whereIn('key', ['owner', 'admin'])->pluck('id') as $roleId) {
            DB::table('permission_role')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'quota.manage')->value('id');
        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
        Schema::dropIfExists('quota_payments');
    }
};
