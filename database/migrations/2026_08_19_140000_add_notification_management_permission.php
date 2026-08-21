<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'notifications.manage')->value('id');
        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'key' => 'notifications.manage', 'module' => 'notifications',
                'description' => 'Gérer les notifications',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach (DB::table('roles')->whereIn('key', ['owner', 'admin'])->pluck('id') as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permissionId, 'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'notifications.manage')->value('id');
        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
