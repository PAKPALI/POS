@extends('layouts.platform')
@section('title', $user->name)
@section('page-title', $user->name)
@section('content')
<div class="mb-4"><a href="{{ route('platform.users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Utilisateurs</a></div>
<div class="row g-4">
    <div class="col-xl-4">
        <div class="platform-card p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="platform-user-avatar"><i class="bi bi-person-fill fs-3"></i></div>
                <div>
                    <h2 class="h5 mb-1">{{ $user->name }}</h2>
                    <span class="platform-status-chip is-{{ (int)$user->status === 1 ? 'success' : 'danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ (int)$user->status === 1 ? 'Actif' : 'Désactivé' }}</span>
                </div>
            </div>
            <dl class="row mb-0">
                <dt class="col-4">E-mail</dt>
                <dd class="col-8 text-break">{{ $user->email }}</dd>
                <dt class="col-4">Téléphone</dt>
                <dd class="col-8">{{ $user->country_code }} {{ $user->phone ?: 'Non renseigné' }}</dd>
                <dt class="col-4">Inscription</dt>
                <dd class="col-8">{{ $user->created_at?->format('d/m/Y H:i') }}</dd>
                <dt class="col-4">Entreprises</dt>
                <dd class="col-8">{{ $user->memberships->count() }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="platform-card p-3">
            <header class="platform-panel-head"><div><p class="platform-eyebrow">Entreprises</p><h2 class="h5 mb-0">Adhésions et rôles</h2></div></header>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead><tr><th>Entreprise</th><th>Rôle</th><th>Adhésion</th><th>Dernier accès</th></tr></thead>
                    <tbody>
                    @forelse($user->memberships as $membership)
                        <tr>
                            <td>
                                <a href="{{ route('platform.companies.show', $membership->company) }}" class="text-decoration-none fw-semibold">{{ $membership->company?->name }}</a>
                                <br><small class="text-secondary">{{ $membership->company?->slug }}</small>
                            </td>
                            <td>{{ $membership->role?->name ?? 'Non attribué' }}</td>
                            <td><span class="platform-status-chip is-{{ $membership->status === 'active' ? 'success' : 'muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $membership->status }}</span></td>
                            <td>{{ $membership->last_accessed_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">Aucune adhésion.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="platform-card p-3 mt-4">
    <header class="platform-panel-head"><div><p class="platform-eyebrow">Historique</p><h2 class="h5 mb-0">Invitations associées à cet e-mail</h2></div></header>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead><tr><th>Entreprise</th><th>État</th><th>Expiration</th><th>Dernier envoi</th></tr></thead>
            <tbody>
            @forelse($invitations as $invitation)
                <tr>
                    <td>{{ $invitation->company?->name }}</td>
                    <td><span class="badge bg-{{ $invitation->status_badge_class }}">{{ $invitation->status_label }}</span></td>
                    <td>{{ $invitation->expires_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $invitation->last_sent_at?->format('d/m/Y H:i') ?? 'Non renseigné' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-secondary py-4">Aucune invitation.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
