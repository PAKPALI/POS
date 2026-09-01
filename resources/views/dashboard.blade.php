@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('title', 'Tableau de bord')
@section('eyebrow', $activeCompany->name ?? 'Entreprise active')
@section('page-title', 'Tableau de bord')

@section('content')
@php
    $can = fn (string $permission) => $currentMembership?->hasPermission($permission) ?? false;
    $metrics = [
        ['label' => 'Chiffre d’affaires', 'value' => number_format($sale_total_revenue, 0, ',', ' ').' FCFA', 'note' => 'Remises : '.number_format($sale_total_discount, 0, ',', ' ').' FCFA', 'icon' => 'bi-graph-up-arrow', 'href' => $can('sales.manage') ? route('history') : null],
        ['label' => 'Ventes', 'value' => number_format($saleCount, 0, ',', ' '), 'note' => 'Transactions enregistrées', 'icon' => 'bi-receipt', 'href' => $can('sales.manage') ? route('history') : null],
        ['label' => 'Produits', 'value' => number_format($productCount, 0, ',', ' '), 'note' => 'Références au catalogue', 'icon' => 'bi-box-seam', 'href' => $can('catalog.manage') ? route('product.index') : null],
        ['label' => 'Catégories', 'value' => number_format($categoryCount, 0, ',', ' '), 'note' => 'Familles de produits', 'icon' => 'bi-tags', 'href' => $can('catalog.manage') ? route('category.index') : null],
        ['label' => 'Clients', 'value' => number_format($clientCount, 0, ',', ' '), 'note' => 'Clients enregistrés', 'icon' => 'bi-people', 'href' => $can('clients.manage') ? route('client.index') : null],
        ['label' => 'Fournisseurs', 'value' => number_format($supplierCount, 0, ',', ' '), 'note' => 'Partenaires enregistrés', 'icon' => 'bi-truck', 'href' => $can('catalog.manage') ? route('supplier.index') : null],
    ];
    if ($canViewFinancials) {
        array_splice($metrics, 2, 0, [[
            'label' => 'Bénéfice', 'value' => number_format($sale_total_profit, 0, ',', ' ').' FCFA',
            'note' => 'Marge cumulée', 'icon' => 'bi-piggy-bank', 'href' => $can('sales.manage') ? route('history') : null,
        ]]);
    }
@endphp

<section class="saas-page-heading">
    <div>
        <h1>Bonjour {{ explode(' ', trim(auth()->user()->name))[0] }}</h1>
        <p>Voici l’activité de {{ $activeCompany->name ?? 'votre entreprise' }} en un coup d’œil.</p>
    </div>
    @if($can('sales.manage'))
        <a class="saas-primary-action" href="{{ route('sale.index') }}"><i class="bi bi-cart-plus"></i>Nouvelle vente</a>
    @endif
</section>

<section class="saas-metric-grid" aria-label="Indicateurs principaux">
    @foreach($metrics as $metric)
        @if($metric['href'])<a class="saas-metric" href="{{ $metric['href'] }}">@else<div class="saas-metric">@endif
            <div class="saas-metric-head"><span class="saas-metric-label">{{ $metric['label'] }}</span><span class="saas-metric-icon"><i class="bi {{ $metric['icon'] }}"></i></span></div>
            <strong class="saas-metric-value">{{ $metric['value'] }}</strong>
            <small class="saas-metric-note">{{ $metric['note'] }}</small>
        @if($metric['href'])</a>@else</div>@endif
    @endforeach
</section>

<section class="saas-dashboard-grid">
    <div>
        <article class="saas-panel">
            <div class="saas-panel-head">
                <div><h2>Produits les plus vendus</h2><p>Classement des quantités vendues sur la période choisie.</p></div>
                <div class="saas-chart-filters" id="dashboardChartFilters">
                    <div class="saas-daterangepicker-wrap">
                        <i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i>
                        <input type="text" id="dashboardDateRange" class="form-control" readonly>
                    </div>
                    <input type="hidden" id="dashboardStartDate">
                    <input type="hidden" id="dashboardEndDate">
                </div>
            </div>
            <div id="topProductsChart" class="saas-chart" aria-label="Graphique des produits les plus vendus"></div>
        </article>
    </div>

    <aside>
        <div class="saas-quota-grid" aria-label="Quotas de communication">
            <div class="saas-quota"><span><i class="bi bi-chat-text"></i>SMS disponibles</span><strong>{{ number_format($company->sms_count ?? 0, 0, ',', ' ') }}</strong></div>
            <div class="saas-quota"><span><i class="bi bi-whatsapp"></i>WhatsApp disponibles</span><strong>{{ number_format($company->whatsapp_count ?? 0, 0, ',', ' ') }}</strong></div>
        </div>
        <article class="saas-panel">
            <div class="saas-panel-head"><div><h2>Activité récente</h2><p>Actions enregistrées aujourd’hui.</p></div></div>
            <div class="saas-activity-list">
                @forelse($Action as $action)
                    <div class="saas-activity">
                        <span class="saas-activity-icon"><i class="bi bi-activity"></i></span>
                        <span class="saas-activity-copy"><strong>{{ $action->text ?: $action->function }}</strong><small>{{ $action->user?->name ?? 'Système' }}</small></span>
                        <time datetime="{{ $action->created_at->toIso8601String() }}">{{ $action->created_at->format('H:i') }}</time>
                    </div>
                @empty
                    <div class="saas-empty"><i class="bi bi-clock-history d-block fs-3 mb-2"></i>Aucune activité enregistrée aujourd’hui.</div>
                @endforelse
            </div>
        </article>
    </aside>
</section>
@endsection

@push('scripts')
<script src="{{ asset('hub/assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chartElement = document.getElementById('topProductsChart');
    const startInput = document.getElementById('dashboardStartDate');
    const endInput = document.getElementById('dashboardEndDate');
    let chart;

    // Init daterangepicker
    const drp = $('#dashboardDateRange').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        opens: 'right',
        alwaysShowCalendars: true,
        ranges: {
            "Aujourd'hui": [moment(), moment()],
            'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 derniers jours': [moment().subtract(6, 'days'), moment()],
            '30 derniers jours': [moment().subtract(29, 'days'), moment()],
            'Ce mois': [moment().startOf('month'), moment().endOf('month')],
            'Mois passé': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'DD-MM-YYYY',
            customRangeLabel: 'Plage personnalisée',
            applyLabel: 'Appliquer',
            cancelLabel: 'Annuler',
            fromLabel: 'Du',
            toLabel: 'Au',
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        }
    });
    // Sync hidden inputs on apply
    drp.on('apply.daterangepicker', function(ev, picker) {
        startInput.value = picker.startDate.format('YYYY-MM-DD');
        endInput.value = picker.endDate.format('YYYY-MM-DD');
        refreshChart().catch((error) => { chartElement.innerHTML = '<div class="saas-empty">' + error.message + '</div>'; });
    });
    // Set initial values
    startInput.value = moment().subtract(29, 'days').format('YYYY-MM-DD');
    endInput.value = moment().format('YYYY-MM-DD');

    const frenchDate = (value) => value.split('-').reverse().join('-');
    const chartColors = () => {
        const styles = getComputedStyle(document.documentElement);
        return {
            accent: styles.getPropertyValue('--ds-accent').trim(),
            text: styles.getPropertyValue('--ds-text-secondary').trim(),
            border: styles.getPropertyValue('--ds-border-soft').trim(),
        };
    };

    async function refreshChart() {
        if (!startInput.value || !endInput.value) throw new Error('Sélectionnez les deux dates.');
        if (startInput.value > endInput.value) throw new Error('La date de début doit précéder la date de fin.');

        const response = await fetch(@json(route('statistics.topProducts')), {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
            body: JSON.stringify({ daterange: `${frenchDate(startInput.value)} - ${frenchDate(endInput.value)}` }),
        });
        if (!response.ok) throw new Error('Impossible de charger le classement des produits.');
        const data = await response.json();
        const colors = chartColors();
        const options = {
            series: [{ name: 'Quantité vendue', data: data.map((item) => Number(item.total_quantity)) }],
            chart: { type: 'bar', height: 290, toolbar: { show: false }, animations: { enabled: !matchMedia('(prefers-reduced-motion: reduce)').matches } },
            colors: [colors.accent], plotOptions: { bar: { borderRadius: 6, columnWidth: '42%' } }, dataLabels: { enabled: false },
            grid: { borderColor: colors.border, strokeDashArray: 4 },
            xaxis: { categories: data.map((item) => item.name), labels: { style: { colors: colors.text } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: colors.text } } },
            tooltip: { theme: document.documentElement.dataset.dsTheme },
            noData: { text: 'Aucune vente sur cette période', style: { color: colors.text } },
        };
        if (chart) await chart.updateOptions(options, true, true);
        else { chart = new ApexCharts(chartElement, options); await chart.render(); }
    }


    window.addEventListener('designsystem:change', () => { if (chart) refreshChart().catch(() => {}); });
    refreshChart().catch((error) => { chartElement.innerHTML = `<div class="saas-empty">${error.message}</div>`; });
});
</script>
@endpush
