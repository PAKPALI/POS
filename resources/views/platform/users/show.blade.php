@extends('layouts.platform')
@section('title', $user->name)
@section('page-title', 'Détails utilisateur')
@section('content')
<div class="platform-user-page">
<header class="platform-user-hero">
    <div class="platform-user-hero-copy">
        <a href="{{ route('platform.users.index') }}" class="platform-company-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour aux utilisateurs</a>
        <div class="platform-company-title-row">
            <span class="platform-user-mark" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
            <div>
                <p class="platform-eyebrow">Administration / Console SaaS</p>
                <h1>{{ $user->name }}</h1>
                <p>Profil d’accès, adhésions aux entreprises et historique des invitations.</p>
            </div>
        </div>
    </div>
    <span class="platform-status-chip is-{{ (int)$user->status === 1 ? 'success' : 'danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ (int)$user->status === 1 ? 'Utilisateur actif' : 'Utilisateur désactivé' }}</span>
</header>

<section class="platform-user-summary-grid" aria-label="Résumé de l'utilisateur">
    <article class="platform-summary-metric is-accent"><span class="platform-summary-icon"><i class="bi bi-building" aria-hidden="true"></i></span><span>Entreprises</span><strong>{{ $user->memberships->count() }}</strong></article>
    <article class="platform-summary-metric is-success"><span class="platform-summary-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span><span>Statut</span><strong>{{ (int)$user->status === 1 ? 'Actif' : 'Désactivé' }}</strong></article>
    <article class="platform-summary-metric is-violet"><span class="platform-summary-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span><span>Inscription</span><strong>{{ $user->created_at?->format('d/m/Y') }}</strong></article>
</section>

<div class="platform-user-content-grid">
    <section class="platform-card platform-company-panel" aria-labelledby="user-identity-title">
            <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-person-vcard" aria-hidden="true"></i> Profil</p><h2 id="user-identity-title">Informations utilisateur</h2><p>Coordonnées et état du compte.</p></div></header>
            <dl class="platform-company-details mb-0">
                <dt class="col-4">E-mail</dt>
                <dd class="col-8 text-break">{{ $user->email }}</dd>
                <dt class="col-4">Téléphone</dt>
                <dd class="col-8">{{ $user->country_code }} {{ $user->phone ?: 'Non renseigné' }}</dd>
                <dt class="col-4">Inscription</dt>
                <dd class="col-8">{{ $user->created_at?->format('d/m/Y H:i') }}</dd>
                <dt class="col-4">Entreprises</dt>
                <dd class="col-8">{{ $user->memberships->count() }}</dd>
            </dl>
    </section>
    <section class="platform-card platform-company-panel" aria-labelledby="user-memberships-title">
            <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-building-check" aria-hidden="true"></i> Entreprises</p><h2 id="user-memberships-title">Adhésions et rôles</h2><p>{{ $user->memberships->count() }} entreprise(s) rattachée(s) à cet utilisateur.</p></div></header>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 platform-company-table">
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
    </section>
</div>

<section class="platform-card platform-company-panel" aria-labelledby="user-invitations-title">
    <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-envelope-paper" aria-hidden="true"></i> Historique</p><h2 id="user-invitations-title">Invitations associées à cet e-mail</h2><p>Suivi des invitations envoyées à cette adresse.</p></div></header>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0 platform-company-table">
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
</section>
</div>
@endsection
