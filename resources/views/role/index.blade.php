@extends('layouts.layout')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-header mb-1">Rôles et permissions</h1>
            <p class="text-muted mb-0">Configuration propre à la compagnie active.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="bi bi-plus-lg me-1"></i> Nouveau rôle
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        @foreach($roles as $role)
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="mb-1">{{ $role->name }}</h4>
                                <span class="badge bg-{{ $role->is_system ? 'secondary' : 'primary' }}">
                                    {{ $role->is_system ? 'Rôle système' : 'Rôle personnalisé' }}
                                </span>
                            </div>
                            <span class="badge bg-dark">{{ $role->users_count }} utilisateur(s)</span>
                        </div>

                        <p class="text-muted small">{{ $role->permissions->count() }} permission(s)</p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @forelse($role->permissions as $permission)
                                <span class="badge border border-secondary text-secondary">{{ $permission->description ?: $permission->key }}</span>
                            @empty
                                <span class="text-muted">Aucune permission</span>
                            @endforelse
                        </div>

                        @if($role->key === 'owner')
                            <div class="alert alert-info py-2 mb-0">Le propriétaire conserve toujours tous les accès.</div>
                        @else
                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editRole{{ $role->id }}">Configurer</button>
                            @if(!$role->is_system && $role->users_count === 0)
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce rôle ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Supprimer</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if($role->key !== 'owner')
                <div class="modal fade" id="editRole{{ $role->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <form action="{{ route('roles.update', $role) }}" method="POST" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">Configurer {{ $role->name }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">@include('role.partials.form', ['selectedPermissions' => $role->permissions->pluck('id')->all()])</div>
                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-warning">Enregistrer</button></div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('roles.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Créer un rôle</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('role.partials.form', ['role' => null, 'selectedPermissions' => old('permissions', [])])</div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('module-permission-toggle')) {
            const module = event.target.closest('[data-permission-module]');
            module.querySelectorAll('.permission-item').forEach(function (permission) {
                permission.checked = event.target.checked;
            });
        }

        if (event.target.classList.contains('permission-item')) {
            const module = event.target.closest('[data-permission-module]');
            synchronizeModuleToggle(module);
        }
    });

    function synchronizeModuleToggle(module) {
        const toggle = module.querySelector('.module-permission-toggle');
        const permissions = Array.from(module.querySelectorAll('.permission-item'));
        const checkedCount = permissions.filter(permission => permission.checked).length;
        toggle.checked = checkedCount === permissions.length;
        toggle.indeterminate = checkedCount > 0 && checkedCount < permissions.length;
    }

    document.querySelectorAll('[data-permission-module]').forEach(synchronizeModuleToggle);
</script>
@endsection
