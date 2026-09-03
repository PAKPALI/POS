@extends('layouts.platform')
@section('title', 'Entreprises')
@section('page-title', 'Entreprises de la plateforme')

@section('content')
<div class="platform-page-stack">
    <section class="platform-card platform-filter-card">
        <header>
            <p class="platform-eyebrow"><i class="bi bi-buildings" aria-hidden="true"></i> Répertoire</p>
            <h2 class="h5 mb-0">Rechercher une entreprise</h2>
        </header>
        <form method="GET" class="platform-filter-grid platform-filter-grid-short">
            <div class="platform-filter-field is-wide">
                <label class="form-label" for="q">Nom, e-mail, slug ou identifiant public</label>
                <input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Rechercher une entreprise">
            </div>
            <div class="platform-filter-field">
                <label class="form-label" for="status">Statut</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Tous</option>
                    <option value="active" @selected(request('status') === 'active')>Actives</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Suspendues ou inactives</option>
                </select>
            </div>
            <button class="btn btn-warning platform-filter-submit" data-loading-text="Recherche…">
                <i class="bi bi-search" aria-hidden="true"></i>Rechercher
            </button>
        </form>
    </section>

    <section class="platform-card platform-data-panel">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow">Entreprises</p>
                <h2>{{ number_format($companies->total(), 0, ',', ' ') }} entreprise(s)</h2>
                <p>Espaces créés sur la plateforme, leurs membres et quotas.</p>
            </div>
            @if(request()->hasAny(['q','status']))
                <a href="{{ route('platform.companies.index') }}" class="platform-panel-link">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>Réinitialiser
                </a>
            @endif
        </header>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Propriétaire</th>
                        <th>Statut</th>
                        <th>Membres</th>
                        <th>Commandes</th>
                        <th>Quotas</th>
                        <th>Création</th>
                        <th><span class="visually-hidden">Détails</span></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($companies as $company)
                    @php($owner = $company->memberships->first()?->user)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $company->name }}</div>
                            <small class="text-secondary">{{ $company->slug }}</small>
                        </td>
                        <td>
                            {{ $owner?->name ?? 'Non identifié' }}
                            <br><small class="text-secondary">{{ $owner?->email }}</small>
                        </td>
                        <td>
                            <span class="platform-status-chip is-{{ $company->status === 'active' ? 'success' : 'danger' }}">
                                <i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $company->status === 'active' ? 'Active' : 'Suspendue' }}
                            </span>
                        </td>
                        <td>{{ $company->memberships_count }}</td>
                        <td>{{ $company->orders_count }}</td>
                        <td>
                            <small>{{ number_format($company->sms_count) }} SMS</small>
                            <br><small class="text-secondary">{{ number_format($company->whatsapp_count) }} WhatsApp</small>
                        </td>
                        <td>{{ $company->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('platform.companies.show', $company) }}" class="platform-action-btn" aria-label="Consulter {{ $company->name }}">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-5">
                            <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                            Aucune entreprise ne correspond aux critères.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="platform-pagination">{{ $companies->links() }}</div>
    </section>
</div>
@endsection
