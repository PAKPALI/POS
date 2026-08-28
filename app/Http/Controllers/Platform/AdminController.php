<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        $admins = PlatformAdmin::latest()->paginate(20);
        $roles = config('platform.roles');
        return view('platform.admins.index', compact('admins', 'roles'));
    }

    public function store(Request $request)
    {
        $actor = Auth::guard('platform')->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:platform_admins,email'],
            'role' => ['required', Rule::in(array_keys(config('platform.roles')))],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], $this->messages());

        DB::transaction(function () use ($request, $actor, $validated) {
            $admin = PlatformAdmin::create([
                'name' => $validated['name'], 'email' => mb_strtolower(trim($validated['email'])),
                'role' => $validated['role'], 'password' => $validated['password'],
                'is_active' => true, 'must_change_password' => true,
            ]);
            $this->audit($request, $actor, 'platform.admin.created', $admin, null, ['name' => $admin->name, 'email' => $admin->email, 'role' => $admin->role, 'is_active' => true], $validated['reason']);
        });
        return back()->with('success', 'Le compte plateforme a été créé. Son mot de passe devra être changé à la première connexion.');
    }

    public function edit(PlatformAdmin $admin)
    {
        $roles = config('platform.roles');
        return view('platform.admins.edit', compact('admin', 'roles'));
    }

    public function update(Request $request, PlatformAdmin $admin)
    {
        $actor = Auth::guard('platform')->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('platform_admins')->ignore($admin->id)],
            'role' => ['required', Rule::in(array_keys(config('platform.roles')))],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], $this->messages());
        abort_if($actor->is($admin) && $validated['role'] !== $admin->role, 422, 'Vous ne pouvez pas modifier votre propre rôle.');
        $this->protectLastSuperAdmin($admin, $validated['role'], $admin->is_active);
        $old = $admin->only(['name', 'email', 'role', 'is_active']);
        DB::transaction(function () use ($request, $actor, $admin, $validated, $old) {
            $admin->update(['name' => $validated['name'], 'email' => mb_strtolower(trim($validated['email'])), 'role' => $validated['role']]);
            $this->audit($request, $actor, 'platform.admin.updated', $admin, $old, $admin->only(['name', 'email', 'role', 'is_active']), $validated['reason']);
        });
        return redirect()->route('platform.admins.index')->with('success', 'Le compte plateforme a été mis à jour.');
    }

    public function updateStatus(Request $request, PlatformAdmin $admin)
    {
        $actor = Auth::guard('platform')->user();
        abort_if($actor->is($admin), 422, 'Vous ne pouvez pas désactiver votre propre compte.');
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'], 'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], $this->messages());
        $newStatus = (bool) $validated['is_active'];
        $this->protectLastSuperAdmin($admin, $admin->role, $newStatus);
        $old = ['is_active' => $admin->is_active];
        DB::transaction(function () use ($request, $actor, $admin, $validated, $newStatus, $old) {
            $admin->update(['is_active' => $newStatus]);
            $this->audit($request, $actor, $newStatus ? 'platform.admin.reactivated' : 'platform.admin.deactivated', $admin, $old, ['is_active' => $newStatus], $validated['reason']);
        });
        return back()->with('success', $newStatus ? 'Le compte a été réactivé.' : 'Le compte a été désactivé.');
    }

    public function resetTwoFactor(Request $request, PlatformAdmin $admin)
    {
        $actor = Auth::guard('platform')->user();
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], $this->messages());

        DB::transaction(function () use ($request, $actor, $admin, $validated) {
            $admin->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'two_factor_attempts' => 0,
                'auth_version' => $admin->auth_version + 1,
            ]);
            $this->audit($request, $actor, 'platform.admin.two_factor.reset', $admin, null,
                ['two_factor_enabled' => $admin->two_factor_enabled], $validated['reason']);
        });

        if ($actor->is($admin)) {
            $request->session()->put('platform_auth_version', $admin->auth_version);
        }

        return back()->with('success', 'La double authentification a été réinitialisée. Un nouveau code sera envoyé à la prochaine connexion.');
    }

    public function updateTwoFactor(Request $request, PlatformAdmin $admin)
    {
        $actor = Auth::guard('platform')->user();
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], $this->messages());
        $enabled = (bool) $validated['enabled'];
        $old = ['two_factor_enabled' => $admin->two_factor_enabled];

        DB::transaction(function () use ($request, $actor, $admin, $validated, $enabled, $old) {
            $admin->update([
                'two_factor_enabled' => $enabled,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'two_factor_attempts' => 0,
                'auth_version' => $admin->auth_version + 1,
            ]);
            $this->audit($request, $actor,
                $enabled ? 'platform.admin.two_factor.enabled' : 'platform.admin.two_factor.disabled',
                $admin, $old, ['two_factor_enabled' => $enabled], $validated['reason']);
        });

        if ($actor->is($admin)) {
            $request->session()->put('platform_auth_version', $admin->auth_version);
        }

        return back()->with('success', $enabled
            ? 'La double authentification est activée pour ce compte.'
            : 'La double authentification est désactivée pour ce compte.');
    }

    private function protectLastSuperAdmin(PlatformAdmin $admin, string $newRole, bool $newStatus): void
    {
        if ($admin->role === 'super_admin' && $admin->is_active && ($newRole !== 'super_admin' || !$newStatus)) {
            abort_if(PlatformAdmin::where('role', 'super_admin')->where('is_active', true)->count() <= 1, 422, 'Le dernier super-administrateur actif doit être conservé.');
        }
    }

    private function audit(Request $request, PlatformAdmin $actor, string $action, PlatformAdmin $target, ?array $old, array $new, string $reason): void
    {
        PlatformAuditLog::create(['platform_admin_id' => $actor->id, 'action' => $action, 'target_type' => PlatformAdmin::class, 'target_id' => (string) $target->id, 'old_values' => $old, 'new_values' => $new, 'reason' => $reason, 'ip_address' => $request->ip(), 'user_agent' => Str::limit((string) $request->userAgent(), 1000, '')]);
    }

    private function messages(): array
    {
        return ['current_password.current_password' => 'Votre mot de passe plateforme est incorrect.', 'reason.required' => 'Indiquez le motif de cette opération.', 'reason.min' => 'Le motif doit contenir au moins 5 caractères.', 'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.', 'password.min' => 'Le mot de passe doit contenir au moins 12 caractères.', 'password.mixed' => 'Le mot de passe doit contenir majuscule et minuscule.', 'password.numbers' => 'Le mot de passe doit contenir un chiffre.', 'password.symbols' => 'Le mot de passe doit contenir un symbole.'];
    }
}
