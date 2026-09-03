@extends('layouts.platform')
@section('title', 'Communications globales')
@section('page-title', 'Communications globales')
@push('styles')
<link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-20" rel="stylesheet">
<link href="{{ asset('hub/assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}?v=20260301-1" rel="stylesheet">
@endpush
@section('content')
@php
    $statusLabels = ['pending' => 'En attente', 'processing' => 'En traitement', 'sent' => 'Envoyée', 'failed' => 'Échouée'];
    $statusIcons = ['pending' => 'bi-hourglass-split', 'processing' => 'bi-gear', 'sent' => 'bi-check-circle', 'failed' => 'bi-exclamation-triangle'];
    $channelIcons = ['email' => 'bi-envelope', 'sms' => 'bi-chat-dots', 'whatsapp' => 'bi-whatsapp'];
    $channelTones = ['email' => 'info', 'sms' => 'info', 'whatsapp' => 'success'];
    $categoryLabels = ['sale' => 'Vente', 'inventory' => 'Inventaire', 'ecommerce_order' => 'Commande e-commerce'];
@endphp

<div class="platform-communication-page">
    <header class="platform-communication-hero">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-chat-square-dots" aria-hidden="true"></i> Centre de communication</p>
            <h2>Suivez les messages envoyés depuis MAXANOU</h2>
            <p>Contrôlez les canaux actifs, la consommation par entreprise et l’état de chaque livraison depuis une seule vue.</p>
        </div>
        <span class="platform-communication-hero-badge"><i class="bi bi-activity" aria-hidden="true"></i> Suivi en temps réel</span>
    </header>

    <section class="platform-summary-grid platform-communication-summary" aria-label="Résumé des communications">
        @foreach(['email' => ['label' => 'E-mails', 'icon' => 'bi-envelope', 'tone' => 'info'], 'sms' => ['label' => 'SMS', 'icon' => 'bi-chat-dots', 'tone' => 'info'], 'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp', 'tone' => 'success']] as $channel => $channelData)
            @php($total = $stats->where('channel', $channel)->sum('total'))
            <article class="platform-summary-metric is-{{ $channelData['tone'] }}">
                <span class="platform-summary-icon"><i class="bi {{ $channelData['icon'] }}" aria-hidden="true"></i></span>
                <span>{{ $channelData['label'] }}</span>
                <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
            </article>
        @endforeach
    </section>

    <section class="platform-communication-section platform-filter-card">
        <header class="platform-panel-head">
            <div><p class="platform-eyebrow"><i class="bi bi-funnel" aria-hidden="true"></i> Filtres et calendrier</p><h2>Filtrer les communications</h2><p>Recherchez une entreprise ou limitez la vue à un canal, un statut et une période.</p></div>
        </header>
        <form method="GET" class="platform-communication-filter-grid">
            <div class="platform-filter-field platform-filter-search"><label class="form-label" for="search">Entreprise ou événement</label><input id="search" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher une entreprise ou un événement"></div>
            <div class="platform-filter-field"><label class="form-label" for="channel">Canal</label><select id="channel" class="form-select" name="channel"><option value="">Tous les canaux</option>@foreach(['email' => 'E-mail', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $v => $l)<option value="{{ $v }}" @selected(($filters['channel'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
            <div class="platform-filter-field"><label class="form-label" for="status">Statut</label><select id="status" class="form-select" name="status"><option value="">Tous les statuts</option>@foreach($statusLabels as $v => $l)<option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
            <div class="saas-form-group communication-period-field"><label for="communication-period">Période</label><div class="saas-daterangepicker-wrap"><i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i><input id="communication-period" type="text" class="form-control" readonly aria-label="Période des communications" placeholder="Sélectionner une période"></div><input id="communication-from" type="hidden" name="from" value="{{ $filters['from'] ?? '' }}"><input id="communication-to" type="hidden" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <button class="btn btn-warning platform-filter-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Filtrer</button>
        </form>
        <div class="platform-filter-actions"><span><i class="bi bi-calendar-range" aria-hidden="true"></i> La période sélectionnée s’applique aux statistiques et aux livraisons.</span><div><a class="btn btn-outline-success" href="{{ route('platform.communications.export', ['format' => 'xlsx'] + request()->query()) }}"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Excel</a><a class="btn btn-outline-warning" href="{{ route('platform.communications.export', ['format' => 'csv'] + request()->query()) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i> CSV</a></div></div>
    </section>

    <section class="platform-communication-section platform-consumption-panel">
        <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-buildings" aria-hidden="true"></i> Par entreprise</p><h2>Consommation par entreprise</h2><p>Répartition des messages sur la période sélectionnée.</p></div><span class="platform-status-chip is-muted"><i class="bi bi-bar-chart" aria-hidden="true"></i> Vue agrégée</span></header>
        <div class="platform-consumption-list">
            @forelse($companies as $row)
                <span class="platform-consumption-item"><span class="platform-consumption-icon"><i class="bi {{ $channelIcons[$row->channel] ?? 'bi-chat-dots' }}" aria-hidden="true"></i></span><span><strong>{{ $row->company?->name ?? 'Entreprise supprimée' }}</strong><small>{{ strtoupper($row->channel) }}</small></span><b>{{ number_format($row->total, 0, ',', ' ') }}</b></span>
            @empty
                <span class="platform-status-chip is-muted"><i class="bi bi-dash-circle" aria-hidden="true"></i> Aucune consommation enregistrée.</span>
            @endforelse
        </div>
    </section>

    <section class="platform-communication-section platform-communication-data-panel">
        <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-chat-square-dots" aria-hidden="true"></i> Livraisons</p><h2>Historique des livraisons</h2><p>Consultez le canal, le destinataire, le statut et les éventuelles erreurs de chaque message.</p></div><span class="platform-status-chip is-muted"><i class="bi bi-list-check" aria-hidden="true"></i> {{ $deliveries->total() }} résultat(s)</span></header>
        <form method="GET" class="platform-communication-table-toolbar">
            <div class="platform-communication-table-toolbar-copy"><strong>Journal des communications</strong><small>Les destinataires sont masqués pour protéger les données personnelles.</small></div>
            <div class="platform-communication-table-toolbar-controls">
                @foreach(['company_id', 'channel', 'status', 'category', 'from', 'to', 'search'] as $key)<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] ?? '' }}">@endforeach
                <div class="platform-table-search-field"><label for="delivery-search">Rechercher dans l’historique</label><div class="platform-table-search-input"><i class="bi bi-search" aria-hidden="true"></i><input id="delivery-search" name="delivery_search" value="{{ $filters['delivery_search'] ?? '' }}" placeholder="Entreprise, événement, destinataire…"></div></div>
                <div class="platform-table-page-size"><label for="delivery-per-page">Lignes</label><select id="delivery-per-page" name="per_page"><option value="10" @selected((int) ($filters['per_page'] ?? 25) === 10)>10</option><option value="25" @selected((int) ($filters['per_page'] ?? 25) === 25)>25</option><option value="50" @selected((int) ($filters['per_page'] ?? 25) === 50)>50</option><option value="100" @selected((int) ($filters['per_page'] ?? 25) === 100)>100</option></select></div>
                <button class="btn btn-warning platform-table-search-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Rechercher</button>
                @if(!empty($filters['delivery_search']))<a class="platform-table-clear-search" href="{{ route('platform.communications.index', request()->except(['delivery_search', 'page'])) }}">Effacer</a>@endif
            </div>
        </form>
        <div class="platform-datatable"><div class="platform-datatable-meta"><span>Résultats filtrés</span><small>Affichage de {{ $deliveries->firstItem() ?? 0 }} à {{ $deliveries->lastItem() ?? 0 }} sur {{ $deliveries->total() }}</small></div><div class="table-responsive platform-table-scroll">
            <table class="table platform-data-table platform-communication-table">
                <thead><tr><th>Date</th><th>Entreprise</th><th>Canal</th><th>Catégorie</th><th>Destinataire</th><th>Statut</th><th>Erreur</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($deliveries as $delivery)
                    <tr>
                        <td><span class="platform-table-date">{{ $delivery->created_at->format('d/m/Y') }}</span><small class="platform-table-subtext">{{ $delivery->created_at->format('H:i') }}</small></td>
                        <td><strong>{{ $delivery->company?->name ?? 'Entreprise supprimée' }}</strong></td>
                        <td><span class="platform-status-chip is-{{ $channelTones[$delivery->channel] ?? 'info' }}"><i class="bi {{ $channelIcons[$delivery->channel] ?? 'bi-chat-dots' }}" aria-hidden="true"></i> {{ strtoupper($delivery->channel) }}</span></td>
                        <td><strong>{{ $categoryLabels[$delivery->category] ?? ucfirst($delivery->category) }}</strong><small class="platform-table-subtext">{{ $delivery->event_type }} #{{ $delivery->event_key }}</small></td>
                        <td><strong>{{ $delivery->user?->name ?? '—' }}</strong><small class="platform-table-subtext">{{ $delivery->user?->email ? \Illuminate\Support\Str::mask($delivery->user->email, '*', 2, max(strlen($delivery->user->email) - 6, 1)) : \Illuminate\Support\Str::mask((string) $delivery->user?->phone, '*', 2, max(strlen((string) $delivery->user?->phone) - 4, 1)) }}</small></td>
                        <td><span class="platform-status-chip is-{{ $delivery->status === 'sent' ? 'success' : ($delivery->status === 'failed' ? 'danger' : ($delivery->status === 'processing' ? 'info' : 'warning')) }}"><i class="bi {{ $statusIcons[$delivery->status] ?? 'bi-circle' }}" aria-hidden="true"></i> {{ $statusLabels[$delivery->status] ?? $delivery->status }}</span><small class="platform-table-subtext">{{ $delivery->attempts }} tentative(s)</small></td>
                        <td>@if($delivery->last_error)<span class="platform-error-text" title="{{ $delivery->last_error }}">{{ \Illuminate\Support\Str::limit($delivery->last_error, 40) }}</span>@else<span class="platform-table-muted">—</span>@endif</td>
                        <td>@if($delivery->status === 'failed' && (($delivery->event_type === 'sale' && in_array($delivery->channel, ['email', 'sms', 'whatsapp'])) || ($delivery->event_type === 'inventory' && in_array($delivery->channel, ['sms', 'whatsapp'])) || ($delivery->event_type === 'ecommerce_order' && $delivery->channel === 'email')))<button class="btn btn-sm btn-outline-warning retry-delivery platform-table-action" data-url="{{ route('platform.communications.retry', $delivery) }}"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Relancer</button>@else<span class="platform-table-muted">—</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="platform-table-empty"><i class="bi bi-chat-square-dots" aria-hidden="true"></i><span>Aucune communication.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
        <div class="platform-communication-pagination">{{ $deliveries->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('hub/assets/plugins/moment/min/moment.min.js') }}?v=20260301-1"></script>
<script src="{{ asset('hub/assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}?v=20260301-1"></script>
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
            "Aujourd’hui": [moment(), moment()],
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
    const form = $('#communication-period').closest('form');
    period.on('apply.daterangepicker', function (event, picker) { sync(picker); form.submit(); });
    period.on('cancel.daterangepicker', function () { $(this).val(''); $('#communication-from, #communication-to').val(''); form.submit(); });
    form.on('change', 'select', function () { form.submit(); });
});

document.querySelectorAll('.retry-delivery').forEach(function(b) {
    b.addEventListener('click', function() {
        Swal.fire({
            title: 'Relancer cette communication ?',
            input: 'text',
            inputPlaceholder: 'Motif obligatoire',
            showCancelButton: true,
            confirmButtonText: 'Oui, relancer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            inputValidator: function(v) { return !v || v.trim().length < 5 ? 'Motif de 5 caractères minimum.' : null; },
            preConfirm: async function(reason) {
                var r = await fetch(b.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ reason: reason }) });
                var d = await r.json().catch(function() { return {}; });
                if (!r.ok) return Swal.showValidationMessage(d.message || 'Relance impossible.');
                return d;
            }
        }).then(function(r) { if (r.isConfirmed) location.reload(); });
    });
});
</script>
@endpush
