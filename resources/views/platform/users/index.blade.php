@extends('layouts.platform')
@section('title', 'Utilisateurs')
@section('page-title', 'Utilisateurs de la plateforme')
@section('content')
<div class="platform-page-stack">
    <section class="platform-card platform-filter-card">
        <header><p class="platform-eyebrow"><i class="bi bi-search" aria-hidden="true"></i> Répertoire</p><h2 class="h5 mb-0">Rechercher un utilisateur</h2></header>
        <form method="GET" class="platform-filter-grid platform-filter-grid-short">
            <div class="platform-filter-field is-wide">
                <label class="form-label" for="q">Nom, e-mail ou téléphone</label>
                <input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Rechercher un utilisateur">
            </div>
            <div class="platform-filter-field">
                <label class="form-label" for="status">Statut</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Tous</option>
                    <option value="active" @selected(request('status') === 'active')>Actifs</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Désactivés</option>
                </select>
            </div>
            <button class="btn btn-warning platform-filter-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Rechercher</button>
        </form>
    </section>
    <section class="platform-card platform-data-panel">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow"><i class="bi bi-people" aria-hidden="true"></i> Utilisateurs</p>
                <h2>{{ number_format($users->total(), 0, ',', ' ') }} utilisateur(s)</h2>
                <p>Accès, entreprises et dernière activité de chaque compte.</p>
            </div>
            @if(request()->hasAny(['q','status']))
                <a href="{{ route('platform.users.index') }}" class="platform-panel-link"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Réinitialiser</a>
            @endif
        </header>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead><tr><th>Utilisateur</th><th>Téléphone</th><th>Statut</th><th>Entreprises</th><th>Adhésions</th><th>Dernier accès</th><th>Inscription</th><th><span class="visually-hidden">Détails</span></th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    @php($inactiveMemberships = max(0, $user->memberships_count - $user->active_memberships_count))
                    <tr>
                        <td><div class="fw-semibold">{{ $user->name }}</div><small class="text-secondary">{{ $user->email }}</small></td>
                        <td>{{ $user->phone ?: 'Non renseigné' }}</td>
                        <td><span class="platform-status-chip {{ (int)$user->status === 1 ? 'is-success' : 'is-danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ (int)$user->status === 1 ? 'Actif' : 'Désactivé' }}</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1" style="min-width:170px">
                                @forelse($user->memberships as $membership)
                                    @if($membership->company)
                                        <a href="{{ route('platform.companies.show', $membership->company) }}" class="platform-status-chip {{ $membership->status === 'active' ? 'is-info' : 'is-muted' }}" title="Adhésion {{ $membership->status }}" style="text-decoration:none">
                                            <i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $membership->company->name }}
                                        </a>
                                    @endif
                                @empty
                                    <span class="platform-status-chip is-muted"><i class="bi bi-dash-circle" aria-hidden="true"></i> Aucune entreprise</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            @if($user->memberships_count === 0)
                                <span class="platform-status-chip is-muted"><i class="bi bi-dash-circle" aria-hidden="true"></i> Aucune adhésion</span>
                            @else
                                <span class="platform-status-chip is-success"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $user->active_memberships_count }} active(s) sur {{ $user->memberships_count }}</span>
                                @if($inactiveMemberships > 0)
                                    <span class="platform-status-chip is-danger" style="margin-top:4px"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $inactiveMemberships }} inactive(s)</span>
                                @endif
                            @endif
                        </td>
                        <td>{{ $user->memberships_max_last_accessed_at ? \Carbon\Carbon::parse($user->memberships_max_last_accessed_at)->format('d/m/Y H:i') : 'Jamais' }}</td>
                        <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td><a href="{{ route('platform.users.show', $user) }}" class="platform-action-btn" aria-label="Consulter {{ $user->name }}"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-secondary py-5"><i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i> Aucun utilisateur ne correspond aux critères.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="platform-pagination">{{ $users->links() }}</div>
    </section>
</div>
@endsection
