@extends('layouts.partner')

@section('title', 'Mes commissions')
@section('page-title', 'Mes commissions')

@push('styles')
<link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260909-6" rel="stylesheet">
<link href="{{ asset('hub/assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}?v=20260301-1" rel="stylesheet">
@endpush

@section('content')
@php
    $xof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF';
    $labels = ['acquisition' => 'Acquisition', 'renewal' => 'Renouvellement', 'upgrade' => 'Montée de plan'];
    $statusLabels = ['pending' => 'En attente', 'available' => 'Disponible', 'reserved' => 'Réservée', 'paid' => 'Payée', 'reversed' => 'Annulée'];
    $periodLabel = !empty($filters['from']) && !empty($filters['to'])
        ? \Carbon\Carbon::parse($filters['from'])->format('d/m/Y').' – '.\Carbon\Carbon::parse($filters['to'])->format('d/m/Y')
        : '';
@endphp
<x-ui.page-header title="Historique des commissions" eyebrow="Portefeuille" icon="bi-wallet2" description="Chaque montant est issu d’un paiement confirmé et reste traçable par son calcul. Aucun solde ne peut être modifié depuis cette page." />

<x-ui.filter-panel title="Filtrer les commissions" open>
    <form method="GET" class="partner-filter-grid partner-filter-grid-commissions">
        <div class="saas-form-group partner-period-field">
            <label for="commissionPeriod">Période</label>
            <div class="saas-daterangepicker-wrap">
                <i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i>
                <input id="commissionPeriod" type="text" class="form-control" readonly aria-label="Période des commissions" placeholder="Sélectionner une période" value="{{ $periodLabel }}">
            </div>
            <input id="commissionFrom" type="hidden" name="from" value="{{ $filters['from'] ?? '' }}">
            <input id="commissionTo" type="hidden" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <x-ui.select id="commissionStatus" name="status" label="Statut"><option value="">Tous les statuts</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</x-ui.select>
        <div class="partner-filter-actions"><x-ui.button type="submit" loading-text="Filtrage…"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrer</x-ui.button><x-ui.button :href="route('partner.commissions')" variant="ghost">Réinitialiser</x-ui.button></div>
    </form>
</x-ui.filter-panel>

<x-ui.export-panel title="Exporter les commissions">
    <p class="partner-export-copy">Le CSV reprend uniquement les données affichables dans votre portefeuille. Il est préparé en arrière-plan et reste disponible 7 jours.</p>
    <form method="POST" action="{{ route('partner.commissions.export') }}" class="partner-export-form">@csrf
        @foreach(['from', 'to', 'status'] as $field)<input type="hidden" name="{{ $field }}" value="{{ $filters[$field] ?? '' }}">@endforeach
        <x-ui.button type="submit" loading-text="Préparation…"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Préparer le CSV</x-ui.button>
    </form>
    @if($exports->isNotEmpty())<div class="partner-export-history">@foreach($exports as $export)<div><span><strong>Export du {{ $export->requested_at->format('d/m/Y H:i') }}</strong><small>{{ ['queued' => 'En attente', 'processing' => 'Préparation', 'completed' => 'Prêt', 'failed' => 'Échec'][$export->status] ?? $export->status }}</small></span>@if($export->status === 'completed' && $export->expires_at?->isFuture())<x-ui.button :href="route('partner.exports.download', $export)" variant="secondary" size="sm"><i class="bi bi-download" aria-hidden="true"></i> Télécharger</x-ui.button>@endif</div>@endforeach</div>@endif
</x-ui.export-panel>

<x-ui.table-shell class="partner-list-table">
    <x-slot:toolbar><span class="partner-table-caption"><i class="bi bi-journal-check" aria-hidden="true"></i> {{ $commissions->total() }} mouvement(s) comptable(s)</span></x-slot:toolbar>
    <x-slot:table>
        <thead><tr><th>Date</th><th>Type</th><th>Client attribué</th><th>Plan</th><th>Calcul</th><th>Statut</th><th class="is-numeric">Commission</th></tr></thead>
        <tbody>@forelse($commissions as $commission)
            @php($company = $commission->attribution?->subscriptionAccount?->billingCompany)
            <tr><td>{{ $commission->created_at->format('d/m/Y H:i') }}</td><td>{{ $labels[$commission->type] ?? ucfirst($commission->type) }}</td><td>{{ $company?->name ?? 'Entreprise non disponible' }}<small class="partner-table-muted">{{ $company?->country_code ?: '—' }}</small></td><td>{{ $commission->subscriptionPayment?->plan?->name ?? '—' }}</td><td><details class="partner-calculation"><summary>Voir le calcul</summary><dl><div><dt>Brut</dt><dd>{{ $xof($commission->gross_amount) }}</dd></div><div><dt>Remise</dt><dd>{{ $xof($commission->discount_amount) }}</dd></div><div><dt>Taux acquis</dt><dd>{{ number_format($commission->commission_rate_bps / 100, 0, ',', ' ') }} %</dd></div><div><dt>Net encaissé</dt><dd>{{ $xof($commission->net_paid_amount) }}</dd></div></dl></details></td><td><x-ui.status :variant="$commission->status === 'available' ? 'success' : ($commission->status === 'reversed' ? 'danger' : 'neutral')">{{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}</x-ui.status></td><td class="is-numeric"><strong>{{ $xof($commission->commission_amount) }}</strong></td></tr>
        @empty
            <tr><td colspan="7"><x-ui.empty-state icon="bi-wallet2" title="Aucune commission trouvée" description="Les commissions apparaîtront après la confirmation d’un abonnement attribué." /></td></tr>
        @endforelse</tbody>
    </x-slot:table>
    <x-slot:footer><span>Page {{ $commissions->currentPage() }} sur {{ $commissions->lastPage() }}</span>{{ $commissions->links() }}</x-slot:footer>
</x-ui.table-shell>
@endsection

@push('scripts')
<script src="{{ asset('hub/assets/plugins/moment/min/moment.min.js') }}?v=20260301-1"></script>
<script src="{{ asset('hub/assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}?v=20260301-1"></script>
<script>
$(function () {
    moment.locale('fr');
    const initialStart = @json($filters['from'] ?? null);
    const initialEnd = @json($filters['to'] ?? null);
    const frenchDays = ['di', 'lu', 'ma', 'me', 'je', 've', 'sa'];
    const frenchMonths = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    const period = $('#commissionPeriod').daterangepicker({
        startDate: initialStart ? moment(initialStart, 'YYYY-MM-DD') : moment(),
        endDate: initialEnd ? moment(initialEnd, 'YYYY-MM-DD') : moment(),
        opens: 'right',
        alwaysShowCalendars: true,
        autoUpdateInput: false,
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
            daysOfWeek: frenchDays,
            monthNames: frenchMonths,
            firstDay: 1
        }
    });
    const picker = period.data('daterangepicker');
    const form = $('#commissionPeriod').closest('form');
    function sync(p) {
        $('#commissionFrom').val(p.startDate.format('YYYY-MM-DD'));
        $('#commissionTo').val(p.endDate.format('YYYY-MM-DD'));
        $('#commissionPeriod').val(p.startDate.format('DD/MM/YYYY') + ' – ' + p.endDate.format('DD/MM/YYYY'));
    }
    if (initialStart && initialEnd) sync(picker);
    period.on('apply.daterangepicker', function (event, p) {
        sync(p);
        form.trigger('submit');
    });
    period.on('cancel.daterangepicker', function () {
        $('#commissionPeriod, #commissionFrom, #commissionTo').val('');
        form.trigger('submit');
    });
});
</script>
@endpush
