@extends('layouts.platform')
@section('title', 'Administrateurs plateforme')
@section('page-title', 'Administrateurs plateforme')

@php
    $roleLabels = collect($roles)->mapWithKeys(fn ($definition, $key) => [$key => $definition['label']])->all();
@endphp

@section('content')
<div class="platform-admin-page">
    <header class="platform-system-hero platform-admin-hero">
        <div class="platform-admin-hero-copy">
            <p class="platform-eyebrow"><i class="bi bi-person-gear" aria-hidden="true"></i> Accès plateforme</p>
            <h2>Gérez les administrateurs en toute sécurité</h2>
            <p>Créez les comptes de la console SaaS, attribuez le bon niveau d’accès et gardez une visibilité claire sur leur sécurité.</p>
        </div>
        <a class="btn btn-warning platform-admin-hero-action" href="#admin-create"><i class="bi bi-person-plus" aria-hidden="true"></i> Nouvel administrateur</a>
    </header>

    <section class="platform-summary-grid platform-summary-grid-four platform-admin-summary" aria-label="Résumé des administrateurs">
        @foreach([
            ['Comptes plateforme', $summary['total'], 'bi-people', 'info'],
            ['Comptes actifs', $summary['active'], 'bi-person-check', 'success'],
            ['Comptes désactivés', $summary['inactive'], 'bi-person-x', 'danger'],
            ['2FA activée', $summary['two_factor'], 'bi-shield-check', 'violet'],
        ] as [$label, $value, $icon, $tone])
            <article class="platform-summary-metric is-{{ $tone }}">
                <span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                <span>{{ $label }}</span>
                <strong>{{ number_format($value, 0, ',', ' ') }}</strong>
            </article>
        @endforeach
    </section>

    <div class="platform-admin-manage-grid">
        <section id="admin-create" class="platform-admin-create platform-card">
            <header class="platform-panel-head">
                <div>
                    <p class="platform-eyebrow"><i class="bi bi-person-plus" aria-hidden="true"></i> Création</p>
                    <h2>Créer un administrateur</h2>
                    <p>Le nouveau compte devra changer son mot de passe à sa première connexion.</p>
                </div>
            </header>
            <div class="platform-admin-notice"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Utilisez une adresse professionnelle et attribuez uniquement les permissions nécessaires.</span></div>
            <form method="POST" action="{{ route('platform.admins.store') }}">
                @csrf
                <div class="platform-admin-form-grid">
                    <div class="platform-filter-field"><label class="form-label" for="name">Nom complet</label><input id="name" name="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="platform-filter-field"><label class="form-label" for="email">E-mail professionnel</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required></div>
                    <div class="platform-filter-field platform-admin-field-wide"><label class="form-label" for="role">Rôle plateforme</label><select id="role" name="role" class="form-select" required>@foreach($roles as $key => $definition)<option value="{{ $key }}" @selected(old('role') === $key)>{{ $definition['label'] }}</option>@endforeach</select><small id="roleHelp" class="platform-field-help"></small></div>
                    <div class="platform-filter-field"><label class="form-label" for="password">Mot de passe initial</label><div class="input-group"><input id="password" name="password" type="password" class="form-control" required><button type="button" class="btn btn-outline-secondary password-toggle" data-target="password" aria-label="Afficher le mot de passe"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                    <div class="platform-filter-field"><label class="form-label" for="password_confirmation">Confirmation du mot de passe</label><div class="input-group"><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required><button type="button" class="btn btn-outline-secondary password-toggle" data-target="password_confirmation" aria-label="Afficher la confirmation"><i class="bi bi-eye" aria-hidden="true"></i></button></div><small class="platform-field-help">12 caractères, majuscule, minuscule, chiffre et symbole.</small></div>
                    <div class="platform-filter-field platform-admin-field-wide"><label class="form-label" for="reason">Motif de création</label><textarea id="reason" name="reason" class="form-control" minlength="5" maxlength="500" rows="3" placeholder="Pourquoi ce compte doit-il être créé ?" required>{{ old('reason') }}</textarea></div>
                    <div class="platform-filter-field platform-admin-field-wide"><label class="form-label" for="current_password">Votre mot de passe plateforme</label><div class="input-group"><input id="current_password" name="current_password" type="password" class="form-control" required><button type="button" class="btn btn-outline-secondary password-toggle" data-target="current_password" aria-label="Afficher votre mot de passe"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                </div>
                <button class="btn btn-warning w-100 platform-admin-create-submit" data-loading-text="Création…"><i class="bi bi-person-plus" aria-hidden="true"></i> Créer l’administrateur</button>
            </form>
        </section>

        <section class="platform-admin-list platform-card platform-data-panel">
            <header class="platform-panel-head">
                <div>
                    <p class="platform-eyebrow"><i class="bi bi-people" aria-hidden="true"></i> Comptes</p>
                    <h2>Administrateurs de la plateforme</h2>
                    <p>Recherchez un compte et contrôlez son accès ou sa double authentification.</p>
                </div>
                <span class="platform-status-chip is-muted"><i class="bi bi-database" aria-hidden="true"></i> {{ number_format($admins->total(), 0, ',', ' ') }} résultat(s)</span>
            </header>

            <form method="GET" class="platform-admin-table-toolbar">
                <div class="platform-admin-table-toolbar-copy"><strong>Filtrer les comptes</strong><small>La recherche porte sur le nom et l’adresse e-mail.</small></div>
                <div class="platform-admin-table-toolbar-controls">
                    <div class="platform-admin-search-field"><label for="admin-search">Rechercher</label><div class="platform-table-search-input"><i class="bi bi-search" aria-hidden="true"></i><input id="admin-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom ou e-mail…"></div></div>
                    <div class="platform-admin-filter-field"><label for="admin-role">Rôle</label><select id="admin-role" name="role"><option value="">Tous les rôles</option>@foreach($roleLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="platform-admin-filter-field"><label for="admin-status">Statut</label><select id="admin-status" name="status"><option value="">Tous les statuts</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Actif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Désactivé</option></select></div>
                    <div class="platform-admin-page-size"><label for="admin-per-page">Lignes</label><select id="admin-per-page" name="per_page"><option value="10" @selected((int) ($filters['per_page'] ?? 20) === 10)>10</option><option value="20" @selected((int) ($filters['per_page'] ?? 20) === 20)>20</option><option value="50" @selected((int) ($filters['per_page'] ?? 20) === 50)>50</option><option value="100" @selected((int) ($filters['per_page'] ?? 20) === 100)>100</option></select></div>
                    <button class="btn btn-warning platform-admin-search-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Rechercher</button>
                    @if(!empty($filters['search']) || !empty($filters['role']) || !empty($filters['status']))<a class="platform-table-clear-search" href="{{ route('platform.admins.index', request()->except(['search', 'role', 'status', 'per_page', 'page'])) }}">Effacer</a>@endif
                </div>
            </form>

            <div class="platform-datatable"><div class="platform-datatable-meta"><span>Résultats filtrés</span><small>Affichage de {{ $admins->firstItem() ?? 0 }} à {{ $admins->lastItem() ?? 0 }} sur {{ $admins->total() }}</small></div><div class="table-responsive platform-table-scroll">
                <table class="table platform-data-table platform-admins-table">
                    <thead><tr><th>Administrateur</th><th>Rôle</th><th>Statut</th><th>Double authentification</th><th>Dernière connexion</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td><div class="platform-admin-table-identity"><span class="platform-admin-table-avatar">{{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}</span><span><strong>{{ $admin->name }}</strong><small class="platform-table-subtext">{{ $admin->email }}</small></span></div></td>
                            <td><span class="platform-status-chip is-info"><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $admin->roleLabel() }}</span></td>
                            <td><span class="platform-status-chip {{ $admin->is_active ? 'is-success' : 'is-danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $admin->is_active ? 'Actif' : 'Désactivé' }}</span>@if($admin->must_change_password)<small class="platform-table-subtext is-warning">Mot de passe à changer</small>@endif</td>
                            <td><span class="platform-status-chip {{ $admin->two_factor_enabled ? 'is-success' : 'is-warning' }}"><i class="bi bi-shield-{{ $admin->two_factor_enabled ? 'check' : 'exclamation' }}" aria-hidden="true"></i> {{ $admin->two_factor_enabled ? 'Activée' : 'Inactive' }}</span></td>
                            <td><span class="platform-table-date">{{ $admin->last_login_at?->format('d/m/Y') ?? 'Jamais' }}</span>@if($admin->last_login_at)<small class="platform-table-subtext">{{ $admin->last_login_at->format('H:i') }}</small>@endif</td>
                            <td><div class="platform-admin-actions"><a href="{{ route('platform.admins.edit', $admin) }}" class="btn btn-sm btn-outline-secondary" title="Modifier {{ $admin->name }}"><i class="bi bi-pencil" aria-hidden="true"></i><span>Modifier</span></a>@unless(auth('platform')->id() === $admin->id)<button class="btn btn-sm {{ $admin->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} admin-status" data-url="{{ route('platform.admins.status', $admin) }}" data-active="{{ $admin->is_active ? '0' : '1' }}" data-name="{{ $admin->name }}" title="{{ $admin->is_active ? 'Désactiver' : 'Réactiver' }}" aria-label="{{ $admin->is_active ? 'Désactiver' : 'Réactiver' }} {{ $admin->name }}"><i class="bi {{ $admin->is_active ? 'bi-person-x' : 'bi-person-check' }}" aria-hidden="true"></i></button>@endunless <button class="btn btn-sm {{ $admin->two_factor_enabled ? 'btn-outline-danger' : 'btn-outline-success' }} two-factor-toggle" data-url="{{ route('platform.admins.two-factor.update', $admin) }}" data-name="{{ $admin->name }}" data-enabled="{{ $admin->two_factor_enabled ? '0' : '1' }}" title="{{ $admin->two_factor_enabled ? 'Désactiver la 2FA' : 'Activer la 2FA' }}" aria-label="{{ $admin->two_factor_enabled ? 'Désactiver la 2FA' : 'Activer la 2FA' }}"><i class="bi bi-shield-{{ $admin->two_factor_enabled ? 'x' : 'check' }}" aria-hidden="true"></i></button>@if($admin->two_factor_enabled)<button class="btn btn-sm btn-outline-warning two-factor-reset" data-url="{{ route('platform.admins.two-factor.reset', $admin) }}" data-name="{{ $admin->name }}" title="Réinitialiser la 2FA" aria-label="Réinitialiser la 2FA"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="platform-table-empty"><i class="bi bi-people" aria-hidden="true"></i><span>Aucun administrateur ne correspond à ces filtres.</span></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
            <div class="platform-pagination">{{ $admins->links() }}</div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
const roleDefinitions = @json($roles);
const roleSelect = document.getElementById('role');
const roleHelp = document.getElementById('roleHelp');
const refreshRole = () => { roleHelp.textContent = roleDefinitions[roleSelect.value]?.description || ''; };
roleSelect.addEventListener('change', refreshRole);
refreshRole();
document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => { const input = document.getElementById(button.dataset.target); const show = input.type === 'password'; input.type = show ? 'text' : 'password'; button.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye'; }));
document.querySelectorAll('.admin-status').forEach(button => button.addEventListener('click', async () => {
    const activating = button.dataset.active === '1';
    const result = await Swal.fire({ title: (activating ? 'Réactiver ' : 'Désactiver ') + button.dataset.name + ' ?', icon: 'warning', html: '<input id="statusReason" class="swal2-input" placeholder="Motif obligatoire"><input id="statusPassword" type="password" class="swal2-input" placeholder="Votre mot de passe plateforme">', showCancelButton: true, confirmButtonText: activating ? 'Réactiver' : 'Désactiver', cancelButtonText: 'Annuler', showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(), preConfirm: async () => { const reason = document.getElementById('statusReason').value, password = document.getElementById('statusPassword').value; if (reason.trim().length < 5) return Swal.showValidationMessage('Motif de 5 caractères minimum.'); if (!password) return Swal.showValidationMessage('Saisissez votre mot de passe.'); const response = await fetch(button.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ _method: 'PATCH', is_active: activating ? 1 : 0, reason: reason.trim(), current_password: password }) }); const data = await response.json().catch(() => ({})); if (!response.ok) return Swal.showValidationMessage(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Opération impossible.')); return data; } });
    if (result?.isConfirmed) window.location.reload();
}));
document.querySelectorAll('.two-factor-reset').forEach(button => button.addEventListener('click', async () => {
    const result = await Swal.fire({ title: 'Réinitialiser la sécurité de ' + button.dataset.name + ' ?', icon: 'warning', html: '<input id="resetReason" class="swal2-input" placeholder="Motif obligatoire"><input id="resetPassword" type="password" class="swal2-input" placeholder="Votre mot de passe plateforme">', showCancelButton: true, confirmButtonText: 'Oui, réinitialiser', cancelButtonText: 'Annuler', showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(), preConfirm: async () => { const reason = document.getElementById('resetReason').value, password = document.getElementById('resetPassword').value; if (reason.trim().length < 5) return Swal.showValidationMessage('Motif de 5 caractères minimum.'); if (!password) return Swal.showValidationMessage('Saisissez votre mot de passe.'); const response = await fetch(button.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ reason: reason.trim(), current_password: password }) }); const data = await response.json().catch(() => ({})); if (!response.ok) return Swal.showValidationMessage(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Opération impossible.')); return data; } });
    if (result?.isConfirmed) window.location.reload();
}));
document.querySelectorAll('.two-factor-toggle').forEach(button => button.addEventListener('click', async () => {
    const enabling = button.dataset.enabled === '1';
    const result = await Swal.fire({ title: (enabling ? 'Activer' : 'Désactiver') + ' la double authentification ?', text: button.dataset.name, icon: enabling ? 'question' : 'warning', html: '<p>' + button.dataset.name + '</p><input id="toggleReason" class="swal2-input" placeholder="Motif obligatoire"><input id="togglePassword" type="password" class="swal2-input" placeholder="Votre mot de passe plateforme">', showCancelButton: true, confirmButtonText: enabling ? 'Oui, activer' : 'Oui, désactiver', cancelButtonText: 'Annuler', showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(), preConfirm: async () => { const reason = document.getElementById('toggleReason').value, password = document.getElementById('togglePassword').value; if (reason.trim().length < 5) return Swal.showValidationMessage('Motif de 5 caractères minimum.'); if (!password) return Swal.showValidationMessage('Saisissez votre mot de passe.'); const response = await fetch(button.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ _method: 'PATCH', enabled: enabling ? 1 : 0, reason: reason.trim(), current_password: password }) }); const data = await response.json().catch(() => ({})); if (!response.ok) return Swal.showValidationMessage(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Opération impossible.')); return data; } });
    if (result?.isConfirmed) window.location.reload();
}));
</script>
@endpush
