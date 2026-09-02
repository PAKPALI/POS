@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Comptabilité</h1>
            <p>Vue d'ensemble des caisses, opérations et flux financiers de l'entreprise.</p>
        </div>
    </div>

    {{-- Statistiques --}}
    <section class="saas-metric-grid mb-4" aria-label="Indicateurs comptables">
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Caisse principale</span><span class="saas-metric-icon"><i class="bi bi-wallet2"></i></span></div>
            <strong class="saas-metric-value">{{ $mainCash ? number_format($mainCash->balance, 0, ',', ' ') : '0' }} <small style="font-size:.55em;font-weight:600;color:var(--ds-text-muted)">FCFA</small></strong>
            <a href="{{ route('cash-account.index') }}" class="saas-btn saas-btn-outline saas-btn-sm mt-2">Voir les caisses</a>
        </div>

        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Opérations</span><span class="saas-metric-icon"><i class="bi bi-arrow-left-right"></i></span></div>
            <strong class="saas-metric-value">{{ $transactionCount }}</strong>
            <a href="{{ route('transaction.index') }}" class="saas-btn saas-btn-outline saas-btn-sm mt-2">Voir les opérations</a>
        </div>

        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Ventes</span><span class="saas-metric-icon"><i class="bi bi-receipt"></i></span></div>
            <strong class="saas-metric-value">{{ number_format($totalSalesAmount, 0, ',', ' ') }} <small style="font-size:.55em;font-weight:600;color:var(--ds-text-muted)">FCFA</small></strong>
            <a href="{{ route('sale.index') }}" class="saas-btn saas-btn-outline saas-btn-sm mt-2">Voir les ventes</a>
        </div>

        @if ($canViewFinancials)
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Bénéfices</span><span class="saas-metric-icon"><i class="bi bi-piggy-bank"></i></span></div>
            <strong class="saas-metric-value">{{ number_format($sale_total_profit, 0, ',', ' ') }} <small style="font-size:.55em;font-weight:600;color:var(--ds-text-muted)">FCFA</small></strong>
            <a href="{{ route('sale.index') }}" class="saas-btn saas-btn-outline saas-btn-sm mt-2">Voir les ventes</a>
        </div>
        @endif
    </section>

    {{-- Paramètres comptabilité --}}
    <div class="saas-card mb-4">
        <div class="saas-card-head">
            <div>
                <h2>Paramètres comptabilité</h2>
                <p class="saas-card-description">Configuration des caisses et du taux de taxe par défaut.</p>
            </div>
            <a href="{{ route('ams.settings') }}" class="saas-btn saas-btn-secondary saas-btn-sm">
                <i class="bi bi-gear" aria-hidden="true"></i> Configurer
            </a>
        </div>

        @if(!$mainCash || !$settings || !$taxCash)
            <div class="saas-card" style="background: rgba(255, 98, 110, .08); border-color: rgba(255, 98, 110, .25);">
                <p style="margin: 0; color: var(--ds-danger, #FF626E); font-weight: 700; font-size: .88rem;">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    Paramètres non configurés — caisse principale, caisse de taxe ou taux de taxe manquant.
                </p>
            </div>
        @else
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="saas-detail-list">
                        <div>
                            <dt>Caisse principale</dt>
                            <dd>{{ $mainCash->name }}</dd>
                        </div>
                        <div>
                            <dt>Solde</dt>
                            <dd>{{ number_format($mainCash->balance, 0, ',', ' ') }} FCFA</dd>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="saas-detail-list">
                        <div>
                            <dt>Caisse de taxe</dt>
                            <dd>{{ $taxCash->name }}</dd>
                        </div>
                        <div>
                            <dt>Solde</dt>
                            <dd>{{ number_format($taxCash->balance, 0, ',', ' ') }} FCFA</dd>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="saas-detail-list">
                        <div>
                            <dt>Taxe par défaut</dt>
                            <dd>{{ $settings->default_tax ?? 0 }} %</dd>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Graphique et dernières opérations --}}
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>Flux des opérations</h2>
                        <p class="saas-card-description">Évolution des entrées, sorties et transferts sur la période.</p>
                    </div>
                </div>
                <div class="saas-flux-toolbar">
                    <div class="saas-daterangepicker-wrap">
                        <i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i>
                        <input id="reportrange" class="form-control" readonly>
                    </div>
                    <div class="saas-select-wrap">
                        <i class="bi bi-bar-chart-line saas-select-icon" aria-hidden="true"></i>
                        <select id="group_by">
                            <option value="day">Journalier</option>
                            <option value="week">Hebdomadaire</option>
                            <option value="month" selected>Mensuel</option>
                            <option value="year">Annuel</option>
                        </select>
                    </div>
                </div>
                <div id="chart" style="min-height: 320px;"></div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>20 dernières opérations</h2>
                        <p class="saas-card-description">Aperçu rapide des mouvements récents.</p>
                    </div>
                    <a href="{{ route('transaction.index') }}" class="saas-btn saas-btn-outline saas-btn-sm">Tout voir</a>
                </div>
                <div style="max-height: 400px; overflow: auto;">
                    @php
                        $typeColors = [
                            'IN' => 'is-active',
                            'OUT' => 'is-inactive',
                            'TRANSFER' => 'is-active',
                        ];
                        $typeName = [
                            'IN' => 'Entrée',
                            'OUT' => 'Sortie',
                            'TRANSFER' => 'Transfert',
                        ];
                    @endphp
                    @forelse($latestTransactions as $t)
                        <div style="display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--ds-border-soft);">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span class="saas-status-badge {{ $typeColors[$t->type] ?? 'is-inactive' }}">{{ $typeName[$t->type] ?? $t->type }}</span>
                                    <strong style="color: var(--ds-text-primary); font-size: .84rem;">{{ number_format($t->amount, 0, ',', ' ') }} FCFA</strong>
                                </div>
                                <div style="color: var(--ds-text-muted); font-size: .76rem;">
                                    @if($t->type == 'IN')
                                        Vers <strong>{{ $t->toCash->name ?? '—' }}</strong>
                                    @elseif($t->type == 'OUT')
                                        Depuis <strong>{{ $t->fromCash->name ?? '—' }}</strong>
                                    @else
                                        De <strong>{{ $t->fromCash->name ?? '—' }}</strong> vers <strong>{{ $t->toCash->name ?? '—' }}</strong>
                                    @endif
                                </div>
                                @if($t->description)
                                    <div style="color: var(--ds-text-muted); font-size: .72rem; margin-top: 2px;" title="{{ $t->description }}">{{ Str::limit($t->description, 40) }}</div>
                                @endif
                            </div>
                            <div style="color: var(--ds-text-muted); font-size: .72rem; white-space: nowrap;">{{ $t->created_at->format('d/m H:i') }}</div>
                        </div>
                    @empty
                        <p style="color: var(--ds-text-muted); font-size: .82rem; text-align: center; padding: 20px 0;">Aucune opération enregistrée.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker"></script>
    <script>
        $(function(){
            let chart;

            function loadChart(daterange = null){
                const group_by = $('#group_by').val();

                fetch("{{ route('ams.stats') }}", {
                    method:"POST",
                    headers:{
                        "Content-Type":"application/json",
                        "X-CSRF-TOKEN":"{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ daterange, group_by })
                })
                .then(res=>res.json())
                .then(data=>{
                    let periods = data.map(d=>d.period);
                    let total_in = data.map(d=>d.total_in);
                    let total_out = data.map(d=>d.total_out);
                    let total_transfer = data.map(d=>d.total_transfer);

                    const computedStyle = getComputedStyle(document.documentElement);
                    const accentColor = computedStyle.getPropertyValue('--ds-accent').trim() || '#3B82F6';
                    const textColor = computedStyle.getPropertyValue('--ds-text-secondary').trim() || '#A9B5C8';
                    const mutedColor = computedStyle.getPropertyValue('--ds-text-muted').trim() || '#74839A';

                    let options = {
                        series: [
                            { name:"Entrées", data: total_in },
                            { name:"Sorties", data: total_out },
                            { name:"Transferts", data: total_transfer }
                        ],
                        chart: {
                            type: 'line',
                            height: 320,
                            zoom: { enabled: true },
                            foreColor: textColor,
                            background: 'transparent'
                        },
                        stroke: { curve: 'smooth', width: 3 },
                        colors: ['#35C98B', '#FF626E', accentColor],
                        xaxis: {
                            categories: periods,
                            labels: { style: { colors: mutedColor } }
                        },
                        yaxis: {
                            labels: { style: { colors: mutedColor }, formatter: function(val){ return val.toLocaleString('fr-FR') + ' FCFA'; } }
                        },
                        legend: {
                            position: 'top',
                            labels: { colors: textColor }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: { formatter: function(val){ return val.toLocaleString('fr-FR') + " FCFA"; } }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,.06)'
                        }
                    };

                    if(chart){ chart.destroy(); }
                    chart = new ApexCharts(document.querySelector("#chart"), options);
                    chart.render();
                })
                .catch(err => console.error("Erreur chart:", err));
            }

            let start = moment().startOf('month');
            let end = moment().endOf('month');

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
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
            $('#reportrange').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));

            $('#reportrange').on('apply.daterangepicker', function(ev, picker){
                const daterange = picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY');
                loadChart(daterange);
            });

            $('#group_by').on('change', function(){ loadChart($('#reportrange').val()); });
            loadChart($('#reportrange').val());
        });
    </script>
    @endpush
@endsection
