<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $companyId = app(CompanyContext::class)->getCompanyId();
        $roles = Role::where('company_id', $companyId)
            ->with('permissions')
            ->withCount('users')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $permissions = Permission::orderBy('module')->orderBy('description')->get()->groupBy('module');

        return view('role.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $companyId = app(CompanyContext::class)->getCompanyId();
        $validated = $this->validateRole($request, $companyId);

        DB::transaction(function () use ($validated, $companyId) {
            $baseKey = Str::slug($validated['name'], '_') ?: 'role';
            $key = $baseKey;
            $suffix = 2;
            while (Role::where('company_id', $companyId)->where('key', $key)->exists()) {
                $key = $baseKey.'_'.$suffix++;
            }

            $role = Role::create([
                'company_id' => $companyId,
                'name' => $validated['name'],
                'key' => $key,
                'is_system' => false,
            ]);
            $role->syncPermissions($validated['permissions'] ?? []);
        });

        return redirect()->route('roles.index')->with('success', 'Le rôle a été créé.');
    }

    public function update(Request $request, Role $role)
    {
        $this->ensureCompanyRole($role);
        abort_if($role->key === 'owner', 403, 'Le rôle propriétaire ne peut pas être modifié.');

        $validated = $this->validateRole($request, $role->company_id, $role->id);
        DB::transaction(function () use ($role, $validated) {
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($validated['permissions'] ?? []);
        });

        return redirect()->route('roles.index')->with('success', 'Le rôle a été mis à jour.');
    }

    public function destroy(Role $role)
    {
        $this->ensureCompanyRole($role);
        abort_if($role->is_system, 422, 'Un rôle système ne peut pas être supprimé.');
        abort_if($role->users()->exists(), 422, 'Ce rôle est encore attribué à un utilisateur.');
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Le rôle a été supprimé.');
    }

    private function validateRole(Request $request, int $companyId, ?int $roleId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('roles')->where(fn ($query) => $query->where('company_id', $companyId))->ignore($roleId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);
    }

    private function ensureCompanyRole(Role $role): void
    {
        abort_unless($role->company_id === app(CompanyContext::class)->getCompanyId(), 404);
    }
}
