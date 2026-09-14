@extends('layouts.platform')

@section('title', 'Fiche partenaire — '.$partner->name)
@section('page-title', 'Fiche partenaire')

@php
    $stats = $stats;
    $chart = $chart;
    $statusLabels = [
        'active' => 'Actif', 'pending_email' => 'À vérifier', 'suspended' => 'Suspendu',
        'reversed' => 'Annulée', 'pending' => 'En attente', 'available' => 'Disponible', 'succeeded' => 'Réussi',
        'reserved' => 'Réservée', 'paid' => 'Payée', 'requested' => 'Demandé', 'otp_verified' => 'Confirmé',
        'approved' => 'Approuvé', 'processing' => 'En traitement', 'unknown' => 'À réconcilier',
        'failed' => 'Échoué', 'cancelled' => 'Annulé',
    ];
    $statusVariants = [
        'active' => 'success', 'pending_email' => 'warning', 'suspended' => 'danger', 'reversed' => 'danger',
        'pending' => 'pending', 'available' => 'success', 'reserved' => 'warning', 'paid' => 'success', 'succeeded' => 'success',
        'requested' => 'pending', 'otp_verified' => 'pending', 'approved' => 'success', 'processing' => 'pending',
        'unknown' => 'warning', 'failed' => 'danger', 'cancelled' => 'neutral',
    ];
    $formatXof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF';
    $activeCode = $activeCode;
@endphp

@section('content')
<div class="platform-partner-page platform-partner-detail-page">
    <x-ui.page-header
        title="{{ $partner->name }}"
        eyebrow="Fiche partenaire"
        icon="bi-person-badge-fill"
        description="Lecture consolidée de l’identité, de l’acquisition, des commissions, des retraits et des événements du partenaire."
    >
        <x-slot:actions>
            <x-ui.button href="{{ route('platform.partners.index', ['period' => $filters['period']]) }}" variant="secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Tous les partenaires</x-ui.button>
            <x-ui.status :variant="$statusVariants[$partner->status] ?? 'neutral'">{{ $statusLabels[$partner->status] ?? $partner->status }}</x-ui.status>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="platform-partner-kpis">
        <x-ui.stat-card label="Clients attribués" :value="$stats['attributions']" :hint="$stats['active_clients'].' abonnement(s) actif(s)'" icon="bi-people-fill" />
        <x-ui.stat-card label="Commissions" :value="$formatXof($stats['commission_amount'])" :hint="$stats['commissions_count'].' écriture(s) non annulée(s)'" icon="bi-wallet2" />
        <x-ui.stat-card label="Chiffre attribué" :value="$formatXof($stats['attributed_gross'])" hint="Base brute des paiements" icon="bi-bar-chart-line-fill" />
        <x-ui.stat-card label="Disponible" :value="$formatXof($stats['available_balance'])" hint="Retirable selon les garde-fous" icon="bi-cash-stack" />
        <x-ui.stat-card label="Réservé" :value="$formatXof($stats['reserved_balance'])" hint="Retraits ouverts" icon="bi-hourglass-split" />
        <x-ui.stat-card label="Déjà payé" :value="$formatXof($stats['paid_balance'])" :hint="$stats['withdrawals'].' retrait(s) enregistré(s)'" icon="bi-check2-circle" />
    </div>

    <div class="platform-partner-detail-grid">
        <x-ui.card title="Identité et accès" description="Informations utiles au suivi opérationnel.">
            <div class="platform-partner-profile">
                <span class="platform-partner-avatar platform-partner-avatar-large">{{ strtoupper(substr($partner->name, 0, 1)) }}</span>
                <div><h2>{{ $partner->name }}</h2><p>{{ '@'.$partner->username }}</p></div>
            </div>
            <x-ui.details-list class="platform-partner-details-list">
                <x-ui.detail label="E-mail">{{ $partner->email }} @if($partner->email_verified_at)<x-ui.status variant="success">Vérifié</x-ui.status>@else<x-ui.status variant="warning">À vérifier</x-ui.status>@endif</x-ui.detail>
                <x-ui.detail label="Pays / téléphone">{{ $partner->country_code ?: '—' }} · {{ $partner->phone_e164 ?: '—' }}</x-ui.detail>
                <x-ui.detail label="Inscription">{{ optional($partner->created_at)->format('d/m/Y à H:i') ?: '—' }}</x-ui.detail>
                <x-ui.detail label="Dernière connexion">{{ optional($partner->last_login_at)->format('d/m/Y à H:i') ?: 'Jamais' }}</x-ui.detail>
                <x-ui.detail label="Taux courant">{{ number_format(((int) $partner->current_rate_bps) / 100, 2, ',', ' ') }} %</x-ui.detail>
                <x-ui.detail label="Code principal"><code>{{ $activeCode?->normalized_code ?? 'Aucun code actif' }}</code></x-ui.detail>
            </x-ui.details-list>
        </x-ui.card>

        <x-ui.card title="Période d’analyse" description="Activité enregistrée sur les {{ $filters['period'] }} derniers jours.">
            <form method="GET" action="{{ route('platform.partners.show', $partner) }}" class="platform-partner-period-form platform-partner-period-form-detail">
                <label for="partner-detail-period">Période des graphiques</label>
                <select id="partner-detail-period" name="period" class="form-select">
                    @foreach([30 => '30 derniers jours', 90 => '90 derniers jours', 365 => '12 derniers mois'] as $period => $label)
                        <option value="{{ $period }}" @selected((int) $filters['period'] === $period)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-ui.button type="submit" variant="secondary" data-loading-text="Actualisation…"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualiser</x-ui.button>
            </form>
            <div class="platform-partner-code-callout">
                <span><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Code utilisé</span>
                <strong>{{ $activeCode?->normalized_code ?? '—' }}</strong>
                <small>{{ $activeCode?->last_used_at ? 'Dernière utilisation : '.$activeCode->last_used_at->format('d/m/Y à H:i') : 'Aucune utilisation enregistrée' }}</small>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Activité du partenaire" description="Attributions et commissions sur la période sélectionnée.">
        <div id="partnerDetailChart" class="platform-partner-chart platform-partner-chart-large" role="img" aria-label="Graphique de l’activité du partenaire"></div>
        <p class="platform-partner-chart-note">Les attributions sont comptées en volume ; les commissions sont affichées en XOF.</p>
        <details class="platform-partner-data-details">
            <summary>Voir les données du graphique</summary>
            <div class="saas-table-wrap">
                <table class="saas-data-table platform-partner-mini-table">
                    <thead><tr><th>Date</th><th>Attributions</th><th>Commissions</th></tr></thead>
                    <tbody>
                    @foreach($chart['labels'] as $index => $label)
                        <tr><td>{{ $label }}</td><td>{{ $chart['attributions'][$index] }}</td><td>{{ $formatXof($chart['commissions'][$index]) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </x-ui.card>

    <div class="platform-partner-detail-sections">
        <x-ui.card title="Clients attribués" description="Les dernières attributions connues et leur entreprise de facturation.">
            <x-ui.table-shell class="platform-partner-table-shell">
                <x-slot:table>
                    <thead><tr><th>Entreprise</th><th>Code</th><th>Plan courant</th><th>Rang</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody>
                    @forelse($attributions as $attribution)
                        <tr>
                            <td data-label="Entreprise"><strong>{{ $attribution->subscriptionAccount?->billingCompany?->name ?? 'Compte sans entreprise' }}</strong><small class="platform-partner-table-subtext">{{ $attribution->subscriptionAccount?->billingCompany?->country_code ?? '—' }}</small></td>
                            <td data-label="Code"><code>{{ $attribution->promoCode?->normalized_code ?? '—' }}</code></td>
                            <td data-label="Plan courant">{{ $attribution->subscriptionAccount?->latestSubscription?->plan?->name ?? '—' }}</td>
                            <td data-label="Rang">#{{ $attribution->acquisition_rank }}</td>
                            <td data-label="Statut"><x-ui.status :variant="$attribution->status === 'active' ? 'success' : 'neutral'">{{ $statusLabels[$attribution->status] ?? ucfirst($attribution->status) }}</x-ui.status></td>
                            <td data-label="Date">{{ optional($attribution->attributed_at)->format('d/m/Y') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-ui.empty-state icon="bi-diagram-3" title="Aucune attribution" description="Les clients associés à ce partenaire apparaîtront ici." /></td></tr>
                    @endforelse
                    </tbody>
                </x-slot:table>
            </x-ui.table-shell>
        </x-ui.card>

        @if(auth('platform')->user()->hasPlatformPermission('platform.partner_commissions.view'))
            <x-ui.card title="Commissions récentes" description="Détail des dernières commissions créées pour ce partenaire.">
                <x-ui.table-shell class="platform-partner-table-shell">
                    <x-slot:table>
                        <thead><tr><th>Date</th><th>Type</th><th>Entreprise</th><th>Brut</th><th>Commission</th><th>Statut</th></tr></thead>
                        <tbody>
                        @forelse($commissions as $commission)
                            <tr>
                                <td data-label="Date">{{ optional($commission->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                <td data-label="Type">{{ match($commission->type) { 'acquisition' => 'Acquisition', 'renewal' => 'Renouvellement', 'upgrade' => 'Montée de plan', default => ucfirst($commission->type) } }}</td>
                                <td data-label="Entreprise">{{ $commission->attribution?->subscriptionAccount?->billingCompany?->name ?? '—' }}</td>
                                <td data-label="Brut">{{ $formatXof($commission->gross_amount) }}</td>
                                <td data-label="Commission"><strong>{{ $formatXof($commission->commission_amount) }}</strong><small class="platform-partner-table-subtext">{{ number_format($commission->commission_rate_bps / 100, 2, ',', ' ') }} %</small></td>
                                <td data-label="Statut"><x-ui.status :variant="$statusVariants[$commission->status] ?? 'neutral'">{{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}</x-ui.status></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state icon="bi-wallet2" title="Aucune commission" description="Les commissions confirmées apparaîtront ici." /></td></tr>
                        @endforelse
                        </tbody>
                    </x-slot:table>
                </x-ui.table-shell>
            </x-ui.card>
        @endif

        @if(auth('platform')->user()->hasPlatformPermission('platform.partner_withdrawals.view'))
            <x-ui.card title="Retraits" description="Suivi des demandes et de leur état de règlement.">
                <x-ui.table-shell class="platform-partner-table-shell">
                    <x-slot:table>
                        <thead><tr><th>Date</th><th>Montant</th><th>Frais</th><th>Compte</th><th>Référence</th><th>Statut</th></tr></thead>
                        <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td data-label="Date">{{ optional($withdrawal->requested_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                <td data-label="Montant"><strong>{{ $formatXof($withdrawal->amount) }}</strong></td>
                                <td data-label="Frais">{{ $formatXof($withdrawal->fees) }}</td>
                                <td data-label="Compte">{{ $withdrawal->account?->gateway ?? '—' }} · {{ $withdrawal->account?->maskedPhone() ?? '—' }}</td>
                                <td data-label="Référence">{{ $withdrawal->kpp_reference ?? 'En attente' }}</td>
                                <td data-label="Statut"><x-ui.status :variant="$statusVariants[$withdrawal->status] ?? 'neutral'">{{ $statusLabels[$withdrawal->status] ?? ucfirst($withdrawal->status) }}</x-ui.status></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state icon="bi-arrow-down-left-circle" title="Aucun retrait" description="Les demandes de retrait du partenaire apparaîtront ici." /></td></tr>
                        @endforelse
                        </tbody>
                    </x-slot:table>
                </x-ui.table-shell>
            </x-ui.card>
        @endif

        <x-ui.card title="Journal du partenaire" description="Événements d’identité, de code et d’activité conservés pour la traçabilité.">
            <x-ui.table-shell class="platform-partner-table-shell">
                <x-slot:table>
                    <thead><tr><th>Date</th><th>Événement</th><th>Cible</th><th>Informations</th></tr></thead>
                    <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td data-label="Date">{{ optional($log->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                            <td data-label="Événement"><code>{{ str_replace('.', ' · ', $log->action) }}</code></td>
                            <td data-label="Cible">{{ class_basename($log->target_type ?: '—') }}{{ $log->target_id ? ' #'.$log->target_id : '' }}</td>
                            <td data-label="Informations"><span class="platform-partner-audit-value">{{ $log->reason ?: ($log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '—') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="bi-journal-x" title="Journal vide" description="Les événements du partenaire apparaîtront ici." /></td></tr>
                    @endforelse
                    </tbody>
                </x-slot:table>
            </x-ui.table-shell>
        </x-ui.card>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('hub/assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script>
        (() => {
            const payload = @json($chart);
            const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || 'currentColor';
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const element = document.getElementById('partnerDetailChart');
            if (!element || !window.ApexCharts) return;
            new window.ApexCharts(element, {
                chart: { height: 320, type: 'area', background: 'transparent', toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: !reducedMotion } },
                colors: [token('--ds-info'), token('--ds-accent')],
                series: [{ name: 'Attributions', data: payload.attributions }, { name: 'Commissions', data: payload.commissions }],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.24, opacityTo: 0.02 } },
                grid: { borderColor: token('--ds-border-soft'), strokeDashArray: 4 },
                xaxis: { categories: payload.labels, labels: { rotate: -45, trim: true, hideOverlappingLabels: true } },
                yaxis: { min: 0, labels: { formatter: (value) => Math.round(value).toLocaleString('fr-FR') } },
                legend: { position: 'top', horizontalAlign: 'left' },
                theme: { mode: document.documentElement.dataset.dsTheme === 'light' ? 'light' : 'dark' },
                tooltip: { shared: true, intersect: false, y: { formatter: (value, context) => context.seriesIndex === 1 ? `${Math.round(value).toLocaleString('fr-FR')} XOF` : `${Math.round(value).toLocaleString('fr-FR')}` } },
            }).render();
        })();
    </script>
@endpush
