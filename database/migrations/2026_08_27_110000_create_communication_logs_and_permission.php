<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', fn (Blueprint $table) => $table->decimal('default_tax', 5, 2)->nullable()->default(null)->change());
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company_settings')->cascadeOnDelete();
            $table->string('channel', 20);
            $table->string('function', 30);
            $table->string('recipient', 30);
            $table->char('country_code', 2);
            $table->unsignedSmallInteger('units')->default(1);
            $table->string('provider_message_id', 120)->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->index(['company_id', 'sent_at']);
            $table->index(['company_id', 'channel', 'function']);
        });
        $permissionId = DB::table('permissions')->where('key', 'communications.view')->value('id');
        if (!$permissionId) $permissionId = DB::table('permissions')->insertGetId([
            'key' => 'communications.view', 'module' => 'communications',
            'description' => 'Voir la consommation SMS et WhatsApp', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $roleIds = DB::table('roles')->whereIn('key', ['owner', 'admin'])->pluck('id');
        foreach ($roleIds as $roleId) DB::table('permission_role')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
    }
    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'communications.view')->value('id');
        if ($permissionId) DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('key', 'communications.view')->delete();
        Schema::dropIfExists('communication_logs');
        DB::table('settings')->whereNull('default_tax')->update(['default_tax' => 0]);
        Schema::table('settings', fn (Blueprint $table) => $table->decimal('default_tax', 5, 2)->default(0)->nullable(false)->change());
    }
};
