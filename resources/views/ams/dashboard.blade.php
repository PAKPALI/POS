@extends('layouts.layout')

@push('css-scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* bouton clignotant */
    .blink-btn {
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0% {opacity: 1;}
        50% {opacity: 0.4;}
        100% {opacity: 1;}
    }

    /* hover PRO */
    .border-color-change {
        border: 2px solid #0d6efd !important;
        transition: 0.3s;
    }
</style>
@endpush

@section('content')

<div class="row">

    {{-- CAISSES --}}
    <div class="col-xl-3 col-lg-6">
        <div class="card border-color mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">CAISSES</span>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-7">
                        <h4 class="mb-0">
                            {{ $cashAccounts->count() }}
                        </h4>
                    </div>

                    <div class="col-5 text-end">
                        <a href="{{ route('cash-account.index') }}" class="btn btn-sm btn-secondary blink-btn">
                            Voir +
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTIONS --}}
    <div class="col-xl-3 col-lg-6">
        <div class="card border-color mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">OPERATIONS</span>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-7">
                        <h4 class="mb-0">
                            {{ $transactions->count() }}
                        </h4>
                    </div>

                    <div class="col-5 text-end">
                        <a href="{{ route('transaction.index') }}" class="btn btn-sm btn-secondary blink-btn">
                            Voir +
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- VENTES --}}
    <div class="col-xl-3 col-lg-6">
        <div class="card border-color mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">VENTES ({{ $sales->count() }})</span>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-7">
                        <h4 class="mb-0">
                            {{ number_format($totalSalesAmount, 2, ',', ' ') }} FCFA
                        </h4>
                    </div>

                    <div class="col-5 text-end">
                        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-secondary blink-btn">
                            Voir +
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PROFIT --}}
    <div class="col-xl-3 col-lg-6">
        <div class="card border-color mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">BENEFICES</span>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-7">
                        <h4 class="mb-0 ">
                            {{ number_format($sale_total_profit, 2, ',', ' ') }} FCFA
                        </h4>
                    </div>

                    <div class="col-5 text-end">
                        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-secondary blink-btn">
                            Voir +
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card mb-3">
            <div class="card-body text-center">

            <div class="d-flex mb-3">
                <h5 class=" flex-grow-1 mb-3">PARAMETRES COMPTABILITE</h5>
            </div>

            @if(!$mainCash || !$settings || !$taxCash)
                <div class="alert alert-danger text-light">
                    ⚠️ Paramètres non configurés (caisse principale, caisse de taxe ou tax) !
                </div>

                <a href="{{ route('ams.settings') }}" class="btn btn-danger blink-btn">
                    Configurer maintenant
                </a>
            @else
                <table class="table table-bordered">
                    <tr>
                        <td>Caisse principale :</td>
                        <td>
                            <span class="badge bg-secondary">
                                <strong>{{ $mainCash->name }}</strong>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>Solde :</td>
                        <td>
                            <span class="badge bg-secondary">
                                <strong>{{ number_format($mainCash->balance, 2, ',', ' ') }} FCFA</strong>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>Taxe (%) :</td>
                        <td>
                            <span class="badge bg-secondary">
                                <strong>{{ $settings->default_tax ?? 0 }} %</strong>
                            </span>
                        </td>
                    </tr>
                </table>
                <table class="table table-bordered">
                    <tr>
                        <td>Caisse de taxe :</td>
                        <td>
                            <span class="badge bg-secondary">
                                <strong>{{ $taxCash->name }}</strong>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>Solde :</td>
                        <td>
                            <span class="badge bg-secondary">
                                <strong>{{ number_format($taxCash->balance, 2, ',', ' ') }} FCFA</strong>
                            </span>
                        </td>
                    </tr>
                </table>
            @endif

            </div>
        </div>
    </div>

    {{-- GRAPH --}}
    <div class="col-xl-8">
        <h5>FLUX DES OPERATIONS</h5>
        <div class="d-flex mb-3 gap-2">
            <input id="reportrange" class="form-control" style="width:250px;">
            <select id="group_by" class="form-select" style="width:200px;">
                <option value="day">Journalier</option>
                <option value="week">Hebdomadaire</option>
                <option value="month">Mensuel</option>
                <option value="year">Annuel</option>
            </select>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="chart"></div>
            </div>
        </div>
    </div>

    {{-- LAST TRANSACTIONS --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5>
                    20 DERNIÈRES OPERATIONS
                    <a href="{{ route('transaction.index') }}" class="btn btn-sm btn-secondary blink-btn">
                        Voir +
                    </a>
                </h5>
                <div style="max-height:400px; overflow:auto;">
                    <table class="table table-sm">
                        @php
                            $typeColors = [
                                'IN' => 'bg-success',
                                'OUT' => 'bg-danger',
                                'TRANSFER' => 'bg-primary',
                            ];
                            $typeName = [
                                'IN' => 'ENTREES',
                                'OUT' => 'SORTIES',
                                'TRANSFER' => 'TRANSFERTS',
                            ];
                        @endphp

                        <tr>
                            <td>
                                TYPE ET MONTANT
                            </td>

                            <td>
                                DESCRIPTION
                            </td>

                            <td>
                                DATE
                            </td>
                        </tr>
                        @foreach($latestTransactions as $t)
                        <tr>
                            <td>
                                <span class="badge {{ $typeColors[$t->type] ?? 'bg-secondary' }}">
                                    {{ $typeName[$t->type] ?? $t->type }}
                                </span>
                                <br>
                                <strong>{{ number_format($t->amount,2,',',' ') }} FCFA</strong>
                                <div class="small text-muted mt-1">
                                    @if($t->type == 'IN')
                                        ➜ Caisse :
                                        <strong>{{ $t->toCash->name ?? 'N/A' }}</strong>
                                    @elseif($t->type == 'OUT')
                                        De :
                                        <strong>{{ $t->fromCash->name ?? 'N/A' }}</strong>
                                    @elseif($t->type == 'TRANSFER')
                                        De :
                                        <strong>{{ $t->fromCash->name ?? 'N/A' }}</strong>
                                        <br>
                                        Vers :
                                        <strong>{{ $t->toCash->name ?? 'N/A' }}</strong>
                                    @endif
                                </div>
                            </td>

                            <td title="{{ $t->description }}">
                                {{ Str::limit($t->description,40) }}
                            </td>

                            <td>
                                {{ $t->created_at->format('d/m H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

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
                    foreColor: '#ffffff'
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: [
                    '#28a745',
                    '#dc3545',
                    '#0d6efd'
                ],
                xaxis: {
                    categories: periods,
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        colors: '#ffffff'
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(val){
                            return val + " FCFA";
                        }
                    }
                }
            };

            if(chart){
                chart.destroy();
            }

            chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();

        })
        .catch(err => {
            console.error("Erreur chart:", err);
        });
    }

    // Date range picker
    $('#reportrange').daterangepicker({
        locale:{ format:'DD-MM-YYYY' }
    });
    let start = moment().startOf('month');
    let end = moment().endOf('month');

    $('#reportrange').val(
        start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY')
    );

    $('#reportrange').on('apply.daterangepicker', function(ev, picker){
        const daterange = picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY');
        loadChart(daterange);
    });

    $('#group_by').on('change', function(){
        const daterange = $('#reportrange').val();
        loadChart(daterange);
    });

    // Initial load
    loadChart($('#reportrange').val());

});
</script>

@endsection