@extends('layouts.platform')

@section('title', 'Vue générale')
@section('page-title', 'Vue générale du SaaS')

@section('content')
@php
    $metrics = [
        ['Entreprises', $stats['companies'], 'bi-buildings', 'accent', 'Comptes créés sur la plateforme'],
        ['Entreprises actives', $stats['active_companies'], 'bi-patch-check', 'success', 'Espaces actuellement actifs'],
        ['Utilisateurs', $stats['users'], 'bi-people', 'violet', 'Utilisateurs rattachés'],
        ['Adhésions actives', $stats['active_memberships'], 'bi-person-check', 'cyan', 'Accès professionnels valides'],
        ['Ventes', $stats['sales'], 'bi-receipt', 'success', 'Transactions enregistrées'],
        ['Commandes e-commerce', $stats['orders'], 'bi-bag-check', 'cyan', 'Commandes en ligne'],
        ['Communications', $stats['communications'], 'bi-chat-dots', 'warning', 'SMS et WhatsApp suivis'],
        ['Paiements quotas', $stats['payments'], 'bi-credit-card', 'accent', 'Recharges traitées'],
    ];
@endphp

<div class="platform-dashboard">
    <section class="platform-dashboard-intro" aria-labelledby="platform-overview-title">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Vue consolidée</p>
            <h2 id="platform-overview-title">Le pilotage de MAXANOU, en un regard.</h2>
            <p>Suivez l’activité, les accès et les flux importants de votre plateforme sans quitter cette console.</p>
        </div>
        <div class="platform-intro-status"><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Console protégée</strong><small>Données réservées à l’administration</small></span></div>
    </section>

    <section class="platform-metric-grid" aria-label="Indicateurs principaux">
        @foreach($metrics as [$label, $value, $icon, $tone, $note])
            <article class="platform-metric is-{{ $tone }}">
                <div class="platform-metric-head"><span>{{ $label }}</span><span class="platform-metric-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span></div>
                <strong class="platform-metric-value">{{ number_format($value, 0, ',', ' ') }}</strong>
                <small>{{ $note }}</small>
            </article>
        @endforeach
    </section>

    <section class="platform-payment-health" aria-labelledby="payment-health-title">
        <div class="platform-section-heading"><div><p class="platform-eyebrow">Encaissements</p><h2 id="payment-health-title">État des paiements de quotas</h2></div><p>Une lecture immédiate des recharges enregistrées.</p></div>
        <div class="platform-payment-grid">
            <article class="platform-payment-state is-success"><span class="platform-payment-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><div><span>Confirmés</span><strong>{{ number_format($stats['paid_payments'], 0, ',', ' ') }}</strong><small>Paiements crédités</small></div></article>
            <article class="platform-payment-state is-warning"><span class="platform-payment-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span><div><span>En attente</span><strong>{{ number_format($stats['pending_payments'], 0, ',', ' ') }}</strong><small>À surveiller</small></div></article>
            <article class="platform-payment-state is-danger"><span class="platform-payment-icon"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i></span><div><span>Échec ou expiration</span><strong>{{ number_format($stats['failed_payments'], 0, ',', ' ') }}</strong><small>Intervention éventuelle</small></div></article>
        </div>
    </section>

    <div class="platform-dashboard-grid">
        @if(auth('platform')->user()->hasPlatformPermission('platform.companies.view'))
        <section class="platform-card platform-data-panel" aria-labelledby="recent-companies-title">
            <header class="platform-panel-head"><div><p class="platform-eyebrow">Croissance</p><h2 id="recent-companies-title">Entreprises récemment créées</h2><p>Les derniers espaces mis en place sur la plateforme.</p></div><a href="{{ route('platform.companies.index') }}" class="platform-panel-link">Voir les entreprises <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></header>
            <div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Entreprise</th><th>Statut</th><th>Membres</th><th>Commandes</th><th>Création</th></tr></thead><tbody>@forelse($recentCompanies as $company)<tr><td><div class="fw-semibold">{{ $company->name }}</div><small class="text-secondary">{{ $company->slug }}</small></td><td><span class="platform-status-chip is-{{ $company->status === 'active' ? 'success' : 'danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $company->status === 'active' ? 'Active' : 'Inactive' }}</span></td><td>{{ $company->memberships_count }}</td><td>{{ $company->orders_count }}</td><td>{{ $company->created_at?->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4"><i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>Aucune entreprise enregistrée.</td></tr>@endforelse</tbody></table></div>
        </section>
        @endif

        @if(auth('platform')->user()->hasPlatformPermission('platform.payments.view'))
        <section class="platform-card platform-data-panel" aria-labelledby="recent-payments-title">
            <header class="platform-panel-head"><div><p class="platform-eyebrow">Flux récents</p><h2 id="recent-payments-title">Paiements récents</h2><p>Dernières recharges de quotas reçues.</p></div><a href="{{ route('platform.payments.index') }}" class="platform-panel-link" aria-label="Voir tous les paiements">Tout voir <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></header>
            <div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Transaction</th><th>Montant</th><th>Statut</th></tr></thead><tbody>@forelse($recentPayments as $payment)<tr><td><small>{{ $payment->transaction_id }}</small></td><td>{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td><td><span class="platform-status-chip is-{{ $payment->status==='paid'?'success':(in_array($payment->status,['failed','expired'])?'danger':'warning') }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $payment->status }}</span></td></tr>@empty<tr><td colspan="3" class="text-center text-secondary py-4"><i class="bi bi-credit-card fs-1 d-block mb-2 opacity-25"></i>Aucun paiement enregistré.</td></tr>@endforelse</tbody></table></div>
        </section>
        @endif
    </div>
</div>
@endsection
