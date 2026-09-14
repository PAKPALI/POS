@extends('layouts.platform')

@section('title', 'Pilotage partenaires')
@section('page-title', 'Pilotage partenaires')

@php
    $summary = $overview['summary'];
    $chart = $overview['chart'];
    $statusLabels = ['active' => 'Actif', 'pending_email' => 'À vérifier', 'suspended' => 'Suspendu'];
    $statusVariants = ['active' => 'success', 'pending_email' => 'warning', 'suspended' => 'danger'];
    $formatXof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF';
@endphp

@section('content')
<div class="platform-partner-page">
    <x-ui.page-header
        title="Programme partenaires"
        eyebrow="Monétisation"
        icon="bi-person-badge-fill"
        description="Suivez l’acquisition, les commissions, les soldes et les retraits depuis une même vue opérationnelle."
    >
        <x-slot:actions>
            @if(auth('platform')->user()->hasPlatformPermission('platform.partners.manage'))
                <x-ui.button href="{{ route('platform.settings.partners.edit') }}" variant="secondary"><i class="bi bi-sliders2" aria-hidden="true"></i> Configurer</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="platform-partner-kpis">
        <x-ui.stat-card label="Partenaires" :value="$summary['partners']" :hint="$summary['new_in_period'].' nouvelle(s) sur '.$overview['period'].' jours'" icon="bi-people-fill" />
        <x-ui.stat-card label="Actifs" :value="$summary['active']" :hint="$summary['pending'].' compte(s) en attente'" icon="bi-person-check-fill" />
        <x-ui.stat-card label="Taux vérifié" :value="$summary['verified_rate'].' %'" hint="E-mails confirmés" icon="bi-patch-check-fill" />
        <x-ui.stat-card label="Clients qualifiés" :value="$summary['qualified_clients']" hint="Cumul déclaré par les partenaires" icon="bi-graph-up-arrow" />
        <x-ui.stat-card label="Commissions" :value="$formatXof($summary['commission_amount'])" :hint="$summary['commission_count'].' écriture(s) non annulée(s)'" icon="bi-wallet2" />
        <x-ui.stat-card label="Solde disponible" :value="$formatXof($summary['available_balance'])" :hint="$summary['reserved_balance'] > 0 ? $formatXof($summary['reserved_balance']).' réservé' : 'Aucun montant réservé'" icon="bi-cash-stack" />
    </div>

    @if($summary['open_withdrawals'] > 0)
        <x-ui.notice variant="warning" icon="bi-exclamation-triangle-fill" class="platform-partner-alert">
            <strong>{{ $summary['open_withdrawals'] }} retrait(s) nécessitent une attention.</strong>
            <span>{{ $formatXof($summary['withdrawal_amount']) }} ont été demandés au total ; vérifiez les statuts « en traitement » ou « à réconcilier ».</span>
        </x-ui.notice>
    @endif

    <x-ui.card class="platform-partner-filter-card" title="Fenêtre d’analyse" description="Les graphiques utilisent la période sélectionnée ; les soldes sont des montants courants.">
        <form method="GET" action="{{ route('platform.partners.index') }}" class="platform-partner-period-form">
            <label for="partner-period">Période des graphiques</label>
            <select id="partner-period" name="period" class="form-select">
                @foreach([30 => '30 derniers jours', 90 => '90 derniers jours', 365 => '12 derniers mois'] as $period => $label)
                    <option value="{{ $period }}" @selected((int) $overview['period'] === $period)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <input type="hidden" name="country_code" value="{{ $filters['country_code'] ?? '' }}">
            <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'recent' }}">
            <input type="hidden" name="per_page" value="{{ $filters['per_page'] ?? 20 }}">
            <x-ui.button type="submit" variant="secondary" data-loading-text="Actualisation…"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualiser</x-ui.button>
        </form>
    </x-ui.card>

    <div class="platform-partner-chart-grid">
        <x-ui.card title="Dynamique du programme" description="Inscriptions, validations et nouvelles attributions.">
            <div id="partnerGrowthChart" class="platform-partner-chart" role="img" aria-label="Graphique de la dynamique du programme partenaire"></div>
            <p class="platform-partner-chart-note">Les valeurs exactes restent consultables dans le tableau de données accessible ci-dessous.</p>
            <details class="platform-partner-data-details">
                <summary>Voir les données du graphique</summary>
                <div class="saas-table-wrap">
                    <table class="saas-data-table platform-partner-mini-table">
                        <thead><tr><th>Date</th><th>Inscriptions</th><th>Vérifiés</th><th>Attributions</th></tr></thead>
                        <tbody>
                        @foreach($chart['labels'] as $index => $label)
                            <tr><td>{{ $label }}</td><td>{{ $chart['registrations'][$index] }}</td><td>{{ $chart['verified'][$index] }}</td><td>{{ $chart['attributions'][$index] }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </x-ui.card>

        <x-ui.card title="Flux financiers partenaires" description="Commissions créées et montants de retraits demandés.">
            <div id="partnerFinanceChart" class="platform-partner-chart" role="img" aria-label="Graphique des flux financiers partenaires"></div>
            <p class="platform-partner-chart-note">Les montants sont exprimés en XOF et ne remplacent pas le journal financier.</p>
            <details class="platform-partner-data-details">
                <summary>Voir les données du graphique</summary>
                <div class="saas-table-wrap">
                    <table class="saas-data-table platform-partner-mini-table">
                        <thead><tr><th>Date</th><th>Commissions</th><th>Retraits</th></tr></thead>
                        <tbody>
                        @foreach($chart['labels'] as $index => $label)
                            <tr><td>{{ $label }}</td><td>{{ $formatXof($chart['commissions'][$index]) }}</td><td>{{ $formatXof($chart['withdrawals'][$index]) }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </x-ui.card>
    </div>

    <x-ui.card class="platform-partner-list-card" title="Tous les partenaires" description="Ouvrez une fiche pour consulter le détail d’un partenaire, son activité et son historique.">
        <x-ui.table-shell class="platform-partner-table-shell">
            <x-slot:toolbar>
                <form method="GET" action="{{ route('platform.partners.index') }}" class="platform-partner-filters">
                    <input type="hidden" name="period" value="{{ $overview['period'] }}">
                    <label class="visually-hidden" for="partner-search">Rechercher un partenaire</label>
                    <input id="partner-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Nom, identifiant ou e-mail">
                    <label class="visually-hidden" for="partner-status">Filtrer par statut</label>
                    <select id="partner-status" name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach($statusLabels as $status => $label)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>@endforeach
                    </select>
                    <label class="visually-hidden" for="partner-country">Filtrer par pays</label>
                    <select id="partner-country" name="country_code" class="form-select">
                        <option value="">Tous les pays</option>
                        @foreach($countries as $country)<option value="{{ $country }}" @selected(($filters['country_code'] ?? '') === $country)>{{ $country }}</option>@endforeach
                    </select>
                    <label class="visually-hidden" for="partner-sort">Trier les partenaires</label>
                    <select id="partner-sort" name="sort" class="form-select">
                        <option value="recent" @selected(($filters['sort'] ?? 'recent') === 'recent')>Plus récents</option>
                        <option value="clients" @selected(($filters['sort'] ?? '') === 'clients')>Clients qualifiés</option>
                        <option value="commissions" @selected(($filters['sort'] ?? '') === 'commissions')>Commissions</option>
                        <option value="available" @selected(($filters['sort'] ?? '') === 'available')>Solde disponible</option>
                    </select>
                    <x-ui.button type="submit" variant="secondary" data-loading-text="Filtrage…"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrer</x-ui.button>
                </form>
            </x-slot:toolbar>
            <x-slot:table>
                <thead>
                    <tr><th scope="col">Partenaire</th><th scope="col">Statut</th><th scope="col">Code principal</th><th scope="col">Clients</th><th scope="col">Commissions</th><th scope="col">Disponible</th><th scope="col"><span class="visually-hidden">Action</span></th></tr>
                </thead>
                <tbody>
                @forelse($partners as $partner)
                    @php($primaryCode = $partner->promoCodes->first())
                    <tr>
                        <td data-label="Partenaire"><div class="platform-partner-person"><span class="platform-partner-avatar">{{ strtoupper(substr($partner->name, 0, 1)) }}</span><span><strong>{{ $partner->name }}</strong><small>{{ '@'.$partner->username }} · {{ $partner->email }}</small></span></div></td>
                        <td data-label="Statut"><x-ui.status variant="{{ $statusVariants[$partner->status] ?? 'neutral' }}">{{ $statusLabels[$partner->status] ?? $partner->status }}</x-ui.status></td>
                        <td data-label="Code principal"><code>{{ $primaryCode?->normalized_code ?? '—' }}</code></td>
                        <td data-label="Clients">{{ number_format((int) ($partner->active_clients_count ?? 0), 0, ',', ' ') }}</td>
                        <td data-label="Commissions">{{ $formatXof($partner->commissions_total ?? 0) }}</td>
                        <td data-label="Disponible"><strong>{{ $formatXof($partner->available_balance ?? 0) }}</strong></td>
                        <td data-label="Action"><x-ui.button href="{{ route('platform.partners.show', $partner) }}" variant="ghost" aria-label="Voir {{ $partner->name }}">Détails <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state icon="bi-person-x" title="Aucun partenaire trouvé" description="Modifiez votre recherche ou vos filtres pour élargir la liste." /></td></tr>
                @endforelse
                </tbody>
            </x-slot:table>
            <x-slot:footer>
                <div class="platform-partner-table-footer"><span>{{ $partners->total() }} partenaire(s)</span>{{ $partners->links() }}</div>
            </x-slot:footer>
        </x-ui.table-shell>
    </x-ui.card>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('hub/assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script>
        (() => {
            const payload = @json($chart);
            const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || 'currentColor';
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const base = {
                chart: { height: 300, type: 'area', background: 'transparent', toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: !reducedMotion } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.24, opacityTo: 0.02 } },
                grid: { borderColor: token('--ds-border-soft'), strokeDashArray: 4 },
                xaxis: { categories: payload.labels, labels: { rotate: -45, trim: true, hideOverlappingLabels: true } },
                yaxis: { min: 0, labels: { formatter: (value) => Math.round(value).toLocaleString('fr-FR') } },
                legend: { position: 'top', horizontalAlign: 'left' },
                theme: { mode: document.documentElement.dataset.dsTheme === 'light' ? 'light' : 'dark' },
                tooltip: { shared: true, intersect: false },
            };
            const render = (id, series, colors, formatter) => {
                const element = document.getElementById(id);
                if (!element || !window.ApexCharts) return;
                const options = { ...base, colors, series, tooltip: { ...base.tooltip, y: { formatter } } };
                new window.ApexCharts(element, options).render();
            };
            render('partnerGrowthChart', [
                { name: 'Inscriptions', data: payload.registrations },
                { name: 'Vérifiés', data: payload.verified },
                { name: 'Attributions', data: payload.attributions },
            ], [token('--ds-accent'), token('--ds-info'), token('--ds-success')], (value) => `${Math.round(value).toLocaleString('fr-FR')}`);
            render('partnerFinanceChart', [
                { name: 'Commissions', data: payload.commissions },
                { name: 'Retraits', data: payload.withdrawals },
            ], [token('--ds-warning'), token('--ds-danger')], (value) => `${Math.round(value).toLocaleString('fr-FR')} XOF`);
        })();
    </script>
@endpush
