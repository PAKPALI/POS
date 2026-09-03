@extends('layouts.platform')
@section('title', 'Pré-contrôle abonnements')
@section('page-title', 'Pré-contrôle abonnements')
@section('content')
<div class="platform-subscription-page">
    <header class="platform-subscription-hero">
        <div class="platform-subscription-hero-copy">
            <p class="platform-eyebrow"><i class="bi bi-clipboard2-check" aria-hidden="true"></i> Lecture seule</p>
            <h2>Vérifier avant d’activer les abonnements</h2>
            <p>Repérez les anomalies et les paiements à suivre avant d’activer le contrôle d’accès. Cet écran ne modifie aucun prix, plan, abonnement, paiement ou quota.</p>
        </div>
        <div class="platform-header-actions platform-subscription-hero-actions">
            <a class="btn btn-outline-secondary" href="{{ route('platform.settings.general') }}"><i class="bi bi-sliders2" aria-hidden="true"></i> Paramètres généraux</a>
            <a class="btn btn-warning" href="{{ route('platform.subscriptions.catalog') }}"><i class="bi bi-layers" aria-hidden="true"></i> Gérer les versions</a>
        </div>
    </header>

    <section class="platform-summary-grid platform-subscription-summary" aria-label="Résumé des abonnements">
        @foreach([
            ['Contrôle d\'accès', $summary['enforcement_enabled'] ? 'Actif' : 'Désactivé', 'bi-shield-check', $summary['enforcement_enabled'] ? 'warning' : 'success'],
            ['KPrimePay', $summary['kprimepay_enabled'] && $summary['kprimepay_configured'] ? 'Prêt' : 'À configurer', 'bi-credit-card', $summary['kprimepay_enabled'] && $summary['kprimepay_configured'] ? 'success' : 'danger'],
            ['Comptes de facturation', $summary['accounts_total'], 'bi-receipt', 'info'],
            ['Abonnements en cours', $summary['current_subscriptions'], 'bi-check2-circle', 'success'],
            ['Paiements à suivre', $summary['pending_payments'], 'bi-hourglass-split', $summary['pending_payments'] ? 'warning' : 'success'],
        ] as [$label, $value, $icon, $tone])
            <article class="platform-summary-metric is-{{ $tone }}">
                <span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
            </article>
        @endforeach
    </section>

    <div class="platform-subscription-split">
        <section class="platform-subscription-section">
            <header class="platform-panel-head">
                <div><p class="platform-eyebrow"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Points à résoudre</p><h2>Points à résoudre avant l’activation</h2><p>Les éléments qui méritent une vérification avant le passage en production.</p></div>
            </header>
            <div class="platform-subscription-check-list">
                @foreach([
                    ['Entreprises actives sans compte de facturation', $summary['companies_without_account']],
                    ['Abonnements arrivés à échéance', $summary['expired_subscriptions']],
                    ['Abonnements expirant sous 3 jours', $summary['expiring_soon']],
                    ['Paiements expirés en attente de réconciliation', $summary['expired_pending_payments']],
                    ['Plans payants publiés', $summary['active_paid_plans']],
                ] as [$item, $count])
                    <div class="platform-subscription-check-item"><span>{{ $item }}</span><strong class="{{ $count ? 'is-warning' : 'is-success' }}">{{ $count }}</strong></div>
                @endforeach
            </div>
            <div class="platform-subscription-callout"><i class="bi bi-shield-exclamation" aria-hidden="true"></i><span>Avant de passer le contrôle à « Actif », vérifiez les écarts ci-dessus, testez un checkout KPrimePay dans un environnement sûr et confirmez que les rappels clients sont prêts.</span></div>
        </section>

        <section class="platform-subscription-section">
            <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-shield-lock" aria-hidden="true"></i> Règles</p><h2>Règles de sûreté financière</h2><p>Les principes à respecter pour protéger les engagements existants.</p></div></header>
            <ul class="platform-subscription-rules">
                <li>Un prix déjà souscrit reste figé dans le snapshot du paiement et de l’abonnement.</li>
                <li>Une modification commerciale doit créer une nouvelle version de plan, jamais réécrire les engagements existants.</li>
                <li>Les paiements en attente doivent être vérifiés côté KPrimePay avant toute décision.</li>
                <li>Le contrôle global peut rester désactivé pour le travail local sans contourner les permissions métier.</li>
            </ul>
        </section>
    </div>

    <section class="platform-subscription-section platform-subscription-data-panel">
        <header class="platform-panel-head">
            <div><p class="platform-eyebrow"><i class="bi bi-layers" aria-hidden="true"></i> Catalogue</p><h2>Catalogue publié</h2><p>Vue de contrôle des limites et fonctionnalités. Aucun bouton de modification n’est exposé ici.</p></div>
            <span class="platform-status-chip is-muted"><i class="bi bi-circle-fill" aria-hidden="true"></i> Versions actuelles</span>
        </header>
        <div class="platform-datatable"><div class="platform-datatable-meta"><span>Plans disponibles</span><small>{{ $plans->count() }} version(s) publiée(s)</small></div><div class="table-responsive platform-table-scroll">
            <table class="table platform-data-table">
                <thead><tr><th>Plan</th><th>Version</th><th>Prix mensuel</th><th>Prix annuel</th><th>Limites</th><th>Fonctionnalités</th><th>État</th></tr></thead>
                <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td><strong>{{ $plan->name }}</strong><small class="platform-table-subtext">{{ $plan->key }}</small></td>
                        <td><span class="platform-table-code">v{{ $plan->version }}</span></td>
                        <td><strong>{{ number_format($plan->monthly_price, 0, ',', ' ') }}</strong><small class="platform-table-subtext">{{ $plan->currency }}</small></td>
                        <td><strong>{{ number_format($plan->annual_price, 0, ',', ' ') }}</strong><small class="platform-table-subtext">{{ $plan->currency }}</small></td>
                        <td><span class="platform-table-stack">{{ $plan->company_limit }} entreprise(s)<br>{{ $plan->user_limit }} utilisateur(s)<br>{{ $plan->product_limit }} produit(s)</span></td>
                        <td><div class="platform-table-chips">@foreach($plan->features as $feature)<span class="platform-status-chip {{ $feature->enabled ? 'is-success' : 'is-muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $feature->feature_key }}</span>@endforeach</div></td>
                        <td><span class="platform-status-chip {{ $plan->is_active ? 'is-success' : 'is-muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $plan->is_active ? 'Publié' : 'Masqué' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="platform-table-empty"><i class="bi bi-layers" aria-hidden="true"></i><span>Aucun plan publié.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </section>

    <section class="platform-subscription-section platform-subscription-data-panel">
        <header class="platform-panel-head">
            <div><p class="platform-eyebrow"><i class="bi bi-credit-card" aria-hidden="true"></i> Paiements d’abonnement</p><h2>Paiements d’abonnement à suivre</h2><p>Les dix derniers paiements créés ou en attente. La réconciliation automatique est disponible via la commande planifiée.</p></div>
            <span class="platform-status-chip {{ $summary['pending_payments'] ? 'is-warning' : 'is-success' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $summary['pending_payments'] }} en attente</span>
        </header>
        <div class="platform-datatable"><div class="platform-datatable-meta"><span>Suivi des transactions</span><small>Dernières opérations connues</small></div><div class="table-responsive platform-table-scroll">
            <table class="table platform-data-table">
                <thead><tr><th>Transaction</th><th>Entreprise facturée</th><th>Plan</th><th>Montant</th><th>Expiration</th><th>Statut</th></tr></thead>
                <tbody>
                @forelse($pendingPayments as $payment)
                    <tr>
                        <td><span class="platform-table-code">{{ $payment->transaction_id }}</span></td>
                        <td><strong>{{ $payment->subscriptionAccount?->billingCompany?->name ?? 'Entreprise indisponible' }}</strong></td>
                        <td>{{ $payment->plan?->name ?? 'Plan indisponible' }}</td>
                        <td><strong>{{ number_format($payment->amount, 0, ',', ' ') }}</strong><small class="platform-table-subtext">{{ $payment->currency }}</small></td>
                        <td>{{ $payment->expires_at?->format('d/m/Y H:i') ?? 'Non définie' }}</td>
                        <td><span class="platform-status-chip is-warning"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $payment->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="platform-table-empty"><i class="bi bi-credit-card" aria-hidden="true"></i><span>Aucun paiement d’abonnement en attente.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </section>
</div>
@endsection
