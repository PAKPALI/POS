@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Rôles et permissions</h1>
            <p>Configuration propre à la compagnie active.</p>
        </div>
        <button class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="bi bi-plus-lg"></i> Nouveau rôle
        </button>
    </div>

    @if(session('success'))
        <div class="saas-alert saas-alert-success"><i class="bi bi-check-circle"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="saas-role-grid">
        @foreach($roles as $role)
            <div>
                <div class="saas-card h-100" style="display: flex; flex-direction: column;">
                    <div style="flex: 1;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 style="margin: 0 0 6px; color: var(--ds-text-primary); font-size: 1rem; font-weight: 700;">{{ $role->name }}</h3>
                                <span class="saas-status-badge {{ $role->is_system ? 'is-inactive' : 'is-active' }}">
                                    {{ $role->is_system ? 'Système' : 'Personnalisé' }}
                                </span>
                            </div>
                            <span class="saas-count-badge">{{ $role->users_count }}</span>
                        </div>

                        <p style="color: var(--ds-text-muted); font-size: .76rem; margin-bottom: 10px;">
                            {{ $role->permissions->count() }} permission(s)
                        </p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @forelse($role->permissions->take(6) as $permission)
                                <span class="saas-badge" style="background: var(--ds-glass-1); color: var(--ds-text-secondary); border: 1px solid var(--ds-border-soft);">
                                    {{ $permission->description ?: $permission->key }}
                                </span>
                            @empty
                                <span style="color: var(--ds-text-muted); font-size: .76rem;">Aucune permission</span>
                            @endforelse
                            @if($role->permissions->count() > 6)
                                <span class="saas-badge" style="background: var(--ds-accent-soft); color: var(--ds-accent);">
                                    +{{ $role->permissions->count() - 6 }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--ds-border-soft); padding-top: 12px; margin-top: auto;">
                        @if($role->key === 'owner')
                            <p style="color: var(--ds-text-muted); font-size: .74rem; margin: 0;">
                                <i class="bi bi-info-circle"></i> Le propriétaire conserve toujours tous les accès.
                            </p>
                        @else
                            <div class="d-flex gap-2">
                                <button class="saas-btn saas-btn-outline saas-btn-sm" data-bs-toggle="modal" data-bs-target="#editRole{{ $role->id }}">
                                    <i class="bi bi-gear"></i> Configurer
                                </button>
                                @if(!$role->is_system && $role->users_count === 0)
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce rôle ?')">
                                        @csrf @method('DELETE')
                                        <button class="saas-btn saas-btn-ghost saas-btn-sm" style="color: var(--ds-danger, #FF626E);">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($role->key !== 'owner')
                <div class="modal fade" id="editRole{{ $role->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <form action="{{ route('roles.update', $role) }}" method="POST" class="modal-content saas-modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <div>
                                    <p class="saas-modal-eyebrow">Modification</p>
                                    <h3 class="modal-title">Configurer {{ $role->name }}</h3>
                                </div>
                                <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                @include('role.partials.form', ['selectedPermissions' => $role->permissions->pluck('id')->all()])
                            </div>
                            <div class="modal-footer" style="border-top: 1px solid var(--ds-border-soft);">
                                <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…">
                                    <i class="bi bi-check-lg"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Modale Créer rôle --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form action="{{ route('roles.store') }}" method="POST" class="modal-content saas-modal-content saas-modal-primary">
                @csrf
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Création</p>
                        <h3 class="modal-title">Créer un rôle</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @include('role.partials.form', ['role' => null, 'selectedPermissions' => old('permissions', [])])
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--ds-border-soft);">
                    <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Création…">
                        <i class="bi bi-plus-lg"></i> Créer le rôle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('module-permission-toggle')) {
            var module = event.target.closest('[data-permission-module]');
            module.querySelectorAll('.permission-item').forEach(function (permission) {
                permission.checked = event.target.checked;
            });
        }
        if (event.target.classList.contains('permission-item')) {
            var module = event.target.closest('[data-permission-module]');
            synchronizeModuleToggle(module);
        }
    });

    function synchronizeModuleToggle(module) {
        var toggle = module.querySelector('.module-permission-toggle');
        var permissions = Array.from(module.querySelectorAll('.permission-item'));
        var checkedCount = permissions.filter(function(p) { return p.checked; }).length;
        toggle.checked = checkedCount === permissions.length;
        toggle.indeterminate = checkedCount > 0 && checkedCount < permissions.length;
    }

    document.querySelectorAll('[data-permission-module]').forEach(synchronizeModuleToggle);
    </script>
@endsection
