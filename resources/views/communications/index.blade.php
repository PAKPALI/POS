@extends('layouts.saas')
@section('title', 'Consommation des communications')
@push('styles')
<link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-20" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
@endpush

@section('content')
<div class="saas-page-heading">
    <div>
        <h1>Consommation SMS & WhatsApp</h1>
        <p>Suivez chaque envoi, son usage métier et les unités consommées sur la période.</p>
    </div>
    <a class="saas-btn saas-btn-ghost" href="{{ route('sms-quota.index') }}">
        <i class="bi bi-wallet2"></i> Gérer les quotas
    </a>
</div>

{{-- Statistiques par canal --}}
<div class="saas-metric-grid saas-metric-grid-2">
    <article class="saas-metric">
        <div class="saas-metric-head">
            <span class="saas-metric-label">SMS consommés</span>
            <span class="saas-metric-icon"><i class="bi bi-chat-text"></i></span>
        </div>
        <strong class="saas-metric-value">{{ number_format((int) ($totals['sms'] ?? 0), 0, ',', ' ') }}</strong>
        <small class="saas-metric-note">Selon les filtres actifs</small>
    </article>
    <article class="saas-metric">
        <div class="saas-metric-head">
            <span class="saas-metric-label">WhatsApp consommés</span>
            <span class="saas-metric-icon"><i class="bi bi-whatsapp"></i></span>
        </div>
        <strong class="saas-metric-value">{{ number_format((int) ($totals['whatsapp'] ?? 0), 0, ',', ' ') }}</strong>
        <small class="saas-metric-note">Selon les filtres actifs</small>
    </article>
</div>

{{-- Filtres --}}
<details class="saas-accordion" {{ collect($filters)->except('per_page')->filter()->isNotEmpty() ? 'open' : '' }}>
    <summary><i class="bi bi-funnel"></i> Filtrer la consommation</summary>
    <div class="saas-accordion-body">
        <form method="GET" class="saas-filter-row">
            <div class="saas-form-group communication-period-field">
                <label for="communication-period">Période</label>
                <div class="saas-daterangepicker-wrap">
                    <i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i>
                    <input id="communication-period" type="text" class="form-control" readonly aria-label="Période de consommation">
                </div>
                <input id="communication-from" type="hidden" name="from" value="{{ $filters['from'] ?? '' }}">
                <input id="communication-to" type="hidden" name="to" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="saas-form-group">
                <label for="communication-channel">Canal</label>
                <select id="communication-channel" name="channel">
                    <option value="">Tous</option>
                    <option value="sms" @selected(($filters['channel'] ?? '') === 'sms')">SMS</option>
                    <option value="whatsapp" @selected(($filters['channel'] ?? '') === 'whatsapp')">WhatsApp</option>
                </select>
            </div>
            <div class="saas-form-group">
                <label for="communication-function">Fonction</label>
                <select id="communication-function" name="function">
                    <option value="">Toutes</option>
                    <option value="sale" @selected(($filters['function'] ?? '') === 'sale')">Vente</option>
                    <option value="inventory" @selected(($filters['function'] ?? '') === 'inventory')">Inventaire</option>
                    <option value="invoice" @selected(($filters['function'] ?? '') === 'invoice')">Facture</option>
                    <option value="other" @selected(($filters['function'] ?? '') === 'other')">Autre</option>
                </select>
            </div>
            <div class="saas-form-group">
                <label for="communication-per-page">Par page</label>
                <select id="communication-per-page" name="per_page">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</details>

{{-- Journal des envois --}}
<section class="saas-card">
    <div class="saas-card-head">
        <div>
            <h2>Journal des envois</h2>
            <p class="saas-card-description">Traçabilité des communications facturées à l'entreprise.</p>
        </div>
        <span class="saas-count-badge">{{ $logs->total() }}</span>
    </div>
    <div class="table-responsive communication-log-table-wrap">
        <table class="saas-data-table communication-log-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Canal</th>
                    <th>Fonction</th>
                    <th>Pays</th>
                    <th>Destinataire</th>
                    <th class="text-end">Unités</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php($functionLabel = ['sale' => 'Vente', 'inventory' => 'Inventaire', 'invoice' => 'Facture', 'other' => 'Autre'][$log->function] ?? $log->function)
                    <tr>
                        <td class="communication-log-date">{{ $log->sent_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <span class="saas-status-badge {{ $log->channel === 'whatsapp' ? 'is-success' : 'is-info' }}">
                                <i class="bi {{ $log->channel === 'whatsapp' ? 'bi-whatsapp' : 'bi-chat-text' }}" aria-hidden="true"></i>
                                {{ strtoupper($log->channel) }}
                            </span>
                        </td>
                        <td><span class="saas-status-badge is-neutral">{{ $functionLabel }}</span></td>
                        <td class="communication-log-country">{{ $log->country_code ?: '—' }}</td>
                        <td class="communication-log-recipient">{{ $log->recipient }}</td>
                        <td class="text-end"><strong>{{ $log->units }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="saas-empty-state is-compact">
                                <i class="bi bi-chat-square-dots"></i>
                                <span>Aucun envoi pour ces filtres.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="saas-pagination-row">
        <span>Affichage de {{ $logs->firstItem() ?? 0 }} à {{ $logs->lastItem() ?? 0 }} sur {{ $logs->total() }} envoi(s)</span>
        <div class="communication-log-pagination dt-container">{{ $logs->onEachSide(1)->links('pagination::communication') }}</div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function () {
    moment.locale('fr');
    const initialStart = @json($filters['from'] ?? now()->subDays(29)->format('Y-m-d'));
    const initialEnd = @json($filters['to'] ?? now()->format('Y-m-d'));
    const period = $('#communication-period').daterangepicker({
        startDate: moment(initialStart, 'YYYY-MM-DD'),
        endDate: moment(initialEnd, 'YYYY-MM-DD'),
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
            format: 'DD/MM/YYYY',
            customRangeLabel: 'Plage personnalisée',
            applyLabel: 'Appliquer',
            cancelLabel: 'Effacer',
            fromLabel: 'Du',
            toLabel: 'Au',
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        }
    });
    function sync(picker) {
        $('#communication-from').val(picker.startDate.format('YYYY-MM-DD'));
        $('#communication-to').val(picker.endDate.format('YYYY-MM-DD'));
    }
    sync(period.data('daterangepicker'));
    var form = $('#communication-period').closest('form');
    period.on('apply.daterangepicker', function (event, picker) {
        sync(picker);
        form.submit();
    });
    period.on('cancel.daterangepicker', function () {
        $(this).val('');
        $('#communication-from, #communication-to').val('');
        form.submit();
    });
    form.on('change', 'select', function () { form.submit(); });
});
</script>
@endpush
