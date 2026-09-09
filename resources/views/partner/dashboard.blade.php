@extends('layouts.partner')

@section('title', 'Tableau de bord partenaire')
@section('page-title', 'Tableau de bord')

@section('content')
@php
    $xof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF';
    $maxChartAmount = max(1, ...array_column($chart, 'amount'));
@endphp

<x-ui.page-header title="Votre activité partenaire" eyebrow="Vue d’ensemble" icon="bi-graph-up-arrow" description="Suivez vos commissions, vos clients attribués et votre progression, sans exposer leurs coordonnées personnelles.">
    <x-slot:actions>
        <x-ui.button :href="route('partner.code')" variant="secondary"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Mon code</x-ui.button>
        <x-ui.button :href="route('partner.commissions')"><i class="bi bi-wallet2" aria-hidden="true"></i> Voir les commissions</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="saas-metric-grid partner-dashboard-metrics">
    <x-ui.stat-card label="Solde disponible" :value="$xof($balances['available'])" hint="Éligible aux futures règles de retrait" icon="bi-wallet2" />
    <x-ui.stat-card label="Commissions en attente" :value="$xof($balances['pending'])" hint="Aucune réserve appliquée actuellement" icon="bi-hourglass-split" />
    <x-ui.stat-card label="Clients qualifiés" :value="$qualifiedClients" :hint="$activeClients.' abonnement(s) actuellement actif(s)'" icon="bi-people" />
    <x-ui.stat-card label="Taux actuel" :value="number_format($partner->current_rate_bps / 100, 0, ',', ' ').' %'" :hint="$progress['label']" icon="bi-percent" />
</div>

<div class="partner-dashboard-grid">
    <x-ui.card class="partner-chart-card" title="Commissions des 30 derniers jours" description="Montants comptabilisés par jour, en XOF.">
        @if(collect($chart)->sum('amount') > 0)
            <div class="partner-bar-chart" role="img" aria-label="Évolution des commissions sur les trente derniers jours">
                @foreach($chart as $point)
                    <div class="partner-bar-chart-column" title="{{ $point['label'] }} : {{ $xof($point['amount']) }}">
                        <span class="partner-bar-chart-value">{{ $point['amount'] > 0 ? $xof($point['amount']) : '' }}</span>
                        <i style="--partner-bar-height: {{ max(3, (int) round($point['amount'] / $maxChartAmount * 100)) }}%"></i>
                        <small>{{ $loop->iteration % 5 === 0 ? $point['label'] : '' }}</small>
                    </div>
                @endforeach
            </div>
        @else
            <x-ui.empty-state icon="bi-bar-chart" title="Aucune commission sur cette période" description="Vos gains apparaîtront ici dès qu’un abonnement attribué sera confirmé." />
        @endif
    </x-ui.card>

    <x-ui.card title="Progression de votre taux" description="Le taux acquis reste lié à chaque client déjà attribué.">
        <div class="partner-progress-summary">
            <div><span>Taux actuellement appliqué</span><strong>{{ number_format($partner->current_rate_bps / 100, 0, ',', ' ') }} %</strong></div>
            <x-ui.status variant="info">{{ $qualifiedClients }} client(s) qualifié(s)</x-ui.status>
        </div>
        <x-ui.progress :value="$progress['percent']" :label="$progress['label']" />
        <p class="partner-progress-caption">{{ $progress['label'] }}</p>
        <x-ui.notice variant="info">Les commissions disponibles sont visibles immédiatement après la confirmation du paiement. Les conditions de retrait seront appliquées séparément lorsqu’elles seront activées.</x-ui.notice>
    </x-ui.card>
</div>

<x-ui.card title="Dernières commissions" description="Les montants sont calculés côté serveur à partir des paiements confirmés.">
    @if($recentCommissions->isNotEmpty())
        <x-ui.table-shell>
            <x-slot:toolbar><span class="partner-table-caption"><i class="bi bi-clock-history" aria-hidden="true"></i> Les 5 mouvements les plus récents</span><a href="{{ route('partner.commissions') }}" class="saas-btn saas-btn-ghost">Tout afficher</a></x-slot:toolbar>
            <x-slot:table>
                <thead><tr><th>Date</th><th>Type</th><th>Plan</th><th>Statut</th><th class="is-numeric">Commission</th></tr></thead>
                <tbody>@foreach($recentCommissions as $commission)
                    <tr><td>{{ $commission->created_at->format('d/m/Y') }}</td><td>{{ ['acquisition' => 'Acquisition', 'renewal' => 'Renouvellement', 'upgrade' => 'Montée de plan'][$commission->type] ?? ucfirst($commission->type) }}</td><td>{{ $commission->subscriptionPayment?->plan?->name ?? '—' }}</td><td><x-ui.status :variant="$commission->status === 'available' ? 'success' : 'neutral'">{{ $commission->status === 'available' ? 'Disponible' : ucfirst($commission->status) }}</x-ui.status></td><td class="is-numeric"><strong>{{ $xof($commission->commission_amount) }}</strong></td></tr>
                @endforeach</tbody>
            </x-slot:table>
        </x-ui.table-shell>
    @else
        <x-ui.empty-state icon="bi-wallet2" title="Aucune commission pour le moment" description="Partagez votre code partenaire pour commencer à acquérir des clients." />
    @endif
</x-ui.card>
@endsection
