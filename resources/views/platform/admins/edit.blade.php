@extends('layouts.platform')
@section('title', 'Modifier un administrateur')
@section('page-title', 'Modifier '.$admin->name)

@section('content')
<div class="platform-admin-page platform-admin-edit-page">
    <a href="{{ route('platform.admins.index') }}" class="platform-panel-link platform-admin-back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour aux administrateurs</a>

    <header class="platform-system-hero platform-admin-hero">
        <div class="platform-admin-hero-copy">
            <p class="platform-eyebrow"><i class="bi bi-pencil-square" aria-hidden="true"></i> Gestion des accès</p>
            <h2>Modifier le compte de {{ $admin->name }}</h2>
            <p>Mettez à jour l’identité et le rôle de ce compte sans modifier son historique de sécurité.</p>
        </div>
        <span class="platform-status-chip {{ $admin->is_active ? 'is-success' : 'is-danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $admin->is_active ? 'Compte actif' : 'Compte désactivé' }}</span>
    </header>

    <section class="platform-admin-create platform-admin-edit-card platform-card">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow"><i class="bi bi-person-lines-fill" aria-hidden="true"></i> Informations du compte</p>
                <h2>Identité et rôle</h2>
                <p>Les changements seront enregistrés dans l’audit de la console après confirmation.</p>
            </div>
            <span class="platform-status-chip is-info"><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $admin->roleLabel() }}</span>
        </header>

        <form method="POST" action="{{ route('platform.admins.update', $admin) }}">
            @csrf @method('PUT')
            <div class="platform-admin-form-grid">
                <div class="platform-filter-field"><label class="form-label" for="admin-edit-name">Nom complet</label><input id="admin-edit-name" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required></div>
                <div class="platform-filter-field"><label class="form-label" for="admin-edit-email">E-mail professionnel</label><input id="admin-edit-email" name="email" type="email" class="form-control" value="{{ old('email', $admin->email) }}" required></div>
                <div class="platform-filter-field platform-admin-field-wide"><label class="form-label" for="admin-edit-role">Rôle plateforme</label><select id="admin-edit-role" name="role" class="form-select" required @disabled(auth('platform')->id() === $admin->id)>@foreach($roles as $key => $definition)<option value="{{ $key }}" @selected(old('role', $admin->role) === $key)>{{ $definition['label'] }} — {{ $definition['description'] }}</option>@endforeach</select>@if(auth('platform')->id() === $admin->id)<input type="hidden" name="role" value="{{ $admin->role }}"><small class="platform-field-help">Vous ne pouvez pas modifier votre propre rôle.</small>@endif</div>
                <div class="platform-filter-field platform-admin-field-wide"><label class="form-label" for="admin-edit-reason">Motif de modification</label><textarea id="admin-edit-reason" name="reason" class="form-control" minlength="5" maxlength="500" rows="3" placeholder="Pourquoi ce compte est-il modifié ?" required>{{ old('reason') }}</textarea></div>
                <div class="platform-filter-field platform-admin-field-wide"><label class="form-label" for="admin-edit-current-password">Votre mot de passe plateforme</label><div class="input-group"><input id="admin-edit-current-password" name="current_password" type="password" class="form-control" required><button type="button" class="btn btn-outline-secondary password-toggle" data-target="admin-edit-current-password" aria-label="Afficher votre mot de passe"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
            </div>
            <div class="platform-admin-edit-actions"><a href="{{ route('platform.admins.index') }}" class="btn btn-outline-secondary">Annuler</a><button class="btn btn-warning" data-loading-text="Mise à jour…"><i class="bi bi-save2" aria-hidden="true"></i> Enregistrer les modifications</button></div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => { const input = document.getElementById(button.dataset.target); const show = input.type === 'password'; input.type = show ? 'text' : 'password'; button.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye'; }));
</script>
@endpush
