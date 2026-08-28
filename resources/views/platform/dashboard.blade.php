@extends('layouts.platform')
@section('title', 'Vue générale')
@section('page-title', 'Vue générale du SaaS')
@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['Entreprises', $stats['companies'], 'bi-buildings', 'text-info'],
        ['Entreprises actives', $stats['active_companies'], 'bi-check-circle', 'text-success'],
        ['Utilisateurs', $stats['users'], 'bi-people', 'text-warning'],
        ['Adhésions actives', $stats['active_memberships'], 'bi-person-check', 'text-primary'],
        ['Ventes', $stats['sales'], 'bi-receipt', 'text-success'],
        ['Commandes e-commerce', $stats['orders'], 'bi-bag-check', 'text-info'],
        ['Communications', $stats['communications'], 'bi-chat-dots', 'text-warning'],
        ['Paiements quotas', $stats['payments'], 'bi-credit-card', 'text-primary'],
    ] as [$label, $value, $icon, $color])
    <div class="col-6 col-xl-3"><div class="platform-card p-3 h-100"><div class="d-flex justify-content-between"><div><div class="metric-value">{{ number_format($value, 0, ',', ' ') }}</div><div class="metric-label">{{ $label }}</div></div><i class="bi {{ $icon }} {{ $color }} fs-3"></i></div></div></div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="platform-card p-3"><span class="badge bg-success mb-2">Confirmés</span><div class="metric-value">{{ $stats['paid_payments'] }}</div><div class="metric-label">Paiements crédités</div></div></div>
    <div class="col-md-4"><div class="platform-card p-3"><span class="badge bg-warning text-dark mb-2">En attente</span><div class="metric-value">{{ $stats['pending_payments'] }}</div><div class="metric-label">Paiements à surveiller</div></div></div>
    <div class="col-md-4"><div class="platform-card p-3"><span class="badge bg-danger mb-2">Échec</span><div class="metric-value">{{ $stats['failed_payments'] }}</div><div class="metric-label">Paiements refusés ou expirés</div></div></div>
</div>

<div class="row g-4">
    <div class="col-xl-7"><div class="platform-card p-3"><h2 class="h5 mb-3">Entreprises récemment créées</h2><div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Entreprise</th><th>Statut</th><th>Membres</th><th>Commandes</th><th>Création</th></tr></thead><tbody>@forelse($recentCompanies as $company)<tr><td><div class="fw-semibold">{{ $company->name }}</div><small class="text-secondary">{{ $company->slug }}</small></td><td><span class="badge {{ $company->status === 'active' ? 'bg-success' : 'bg-danger' }}">{{ $company->status === 'active' ? 'Active' : 'Inactive' }}</span></td><td>{{ $company->memberships_count }}</td><td>{{ $company->orders_count }}</td><td>{{ $company->created_at?->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">Aucune entreprise enregistrée.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-xl-5"><div class="platform-card p-3"><h2 class="h5 mb-3">Paiements récents</h2><div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Transaction</th><th>Montant</th><th>Statut</th></tr></thead><tbody>@forelse($recentPayments as $payment)<tr><td><small>{{ $payment->transaction_id }}</small></td><td>{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td><td><span class="badge bg-secondary">{{ $payment->status }}</span></td></tr>@empty<tr><td colspan="3" class="text-center text-secondary py-4">Aucun paiement enregistré.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@endsection
