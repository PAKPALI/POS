@extends('layouts.platform')
@section('title', 'Entreprises')
@section('page-title', 'Entreprises de la plateforme')
@section('content')
<div class="platform-card p-3 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-lg-7"><label class="form-label" for="q">Recherche</label><input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nom, e-mail, slug ou identifiant public"></div>
        <div class="col-lg-3"><label class="form-label" for="status">Statut</label><select id="status" name="status" class="form-select"><option value="">Tous</option><option value="active" @selected(request('status') === 'active')>Actives</option><option value="inactive" @selected(request('status') === 'inactive')>Suspendues ou inactives</option></select></div>
        <div class="col-lg-2 d-grid"><button class="btn btn-warning" data-loading-text="Recherche…"><i class="bi bi-search me-1"></i> Rechercher</button></div>
    </form>
</div>
<div class="platform-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">{{ number_format($companies->total(), 0, ',', ' ') }} entreprise(s)</h2>@if(request()->hasAny(['q','status']))<a href="{{ route('platform.companies.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>@endif</div>
    <div class="table-responsive"><table class="table table-dark table-hover align-middle"><thead><tr><th>Entreprise</th><th>Propriétaire</th><th>Statut</th><th>Membres</th><th>Commandes</th><th>Quotas</th><th>Création</th><th></th></tr></thead><tbody>
    @forelse($companies as $company)
        @php($owner = $company->memberships->first()?->user)
        <tr><td><div class="fw-semibold">{{ $company->name }}</div><small class="text-secondary">{{ $company->slug }}</small></td><td>{{ $owner?->name ?? 'Non identifié' }}<br><small class="text-secondary">{{ $owner?->email }}</small></td><td><span class="badge {{ $company->status === 'active' ? 'bg-success' : 'bg-danger' }}">{{ $company->status === 'active' ? 'Active' : 'Suspendue' }}</span></td><td>{{ $company->memberships_count }}</td><td>{{ $company->orders_count }}</td><td><small>{{ number_format($company->sms_count) }} SMS<br>{{ number_format($company->whatsapp_count) }} WhatsApp</small></td><td>{{ $company->created_at?->format('d/m/Y') }}</td><td><a href="{{ route('platform.companies.show', $company) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye me-1"></i>Consulter</a></td></tr>
    @empty<tr><td colspan="8" class="text-center text-secondary py-5">Aucune entreprise ne correspond aux critères.</td></tr>@endforelse
    </tbody></table></div>
    <div class="mt-3">{{ $companies->links() }}</div>
</div>
@endsection
