<?php

use App\Services\CompanyProvisioner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (CompanyProvisioner::PERMISSIONS as $key => $description) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                [
                    'module' => explode('.', $key)[0],
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'key');
        foreach (DB::table('company_settings')->pluck('id') as $companyId) {
            foreach (['owner' => 'Propriétaire', 'admin' => 'Administrateur', 'cashier' => 'Caissier'] as $key => $name) {
                DB::table('roles')->updateOrInsert(
                    ['company_id' => $companyId, 'key' => $key],
                    ['name' => $name, 'is_system' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            $roles = DB::table('roles')->where('company_id', $companyId)->pluck('id', 'key');
            foreach (['owner', 'admin'] as $key) {
                foreach ($permissionIds as $permissionId) {
                    DB::table('permission_role')->insertOrIgnore([
                        'role_id' => $roles[$key], 'permission_id' => $permissionId,
                    ]);
                }
            }
            foreach (['dashboard.view', 'sales.manage', 'clients.manage'] as $permissionKey) {
                DB::table('permission_role')->insertOrIgnore([
                    'role_id' => $roles['cashier'], 'permission_id' => $permissionIds[$permissionKey],
                ]);
            }
        }
    }

    public function down(): void
    {
        // Les rôles peuvent déjà être référencés par des adhésions : rollback non destructif.
    }
};
