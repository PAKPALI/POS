@extends('layouts.platform')
@section('title', 'Communications globales')
@section('page-title', 'Communications globales')
@section('content')
@php
    $statusLabels = ['pending' => 'En attente', 'processing' => 'Traitement', 'sent' => 'Envoyée', 'failed' => 'Échouée'];
    $statusColors = ['pending' => 'warning', 'processing' => 'info', 'sent' => 'success', 'failed' => 'danger'];
    $statusBadgeClasses = ['pending' => 'is-pending', 'processing' => 'is-info', 'sent' => 'is-active', 'failed' => 'is-inactive'];
@endphp

{{-- Statistiques par canal --}}
<div class="row g-3 mb-4">
    @foreach(['email' => 'E-mails', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $channel => $label)
        <div class="col-md-4">
            <div class="platform-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong style="font-size: .9rem;">{{ $label }}</strong>
                    <span class="badge bg-{{ match($channel) { 'whatsapp' => 'success', 'sms' => 'info', default => 'primary' } }}">
                        {{ $stats->where('channel', $channel)->sum('total') }}
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['sent', 'failed', 'pending', 'processing'] as $status)
                        @php($total = (int) $stats->where('channel', $channel)->where('status', $status)->sum('total'))
                        @if($total > 0)
                            <span class="badge bg-{{ $statusColors[$status] }}">{{ $statusLabels[$status] }} : {{ $total }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Filtres et exports --}}
<div class="platform-card p-3 mb-4">
    <button class="btn btn-outline-light w-100 text-start" data-bs-toggle="collapse" data-bs-target="#communicationFilters">
        <i class="bi bi-funnel me-2"></i>Filtres et exports
    </button>
    <div class="collapse show mt-3" id="communicationFilters">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Entreprise ou événement">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="channel">
                    <option value="">Tous les canaux</option>
                    @foreach(['email' => 'E-mail', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $v => $l)
                        <option value="{{ $v }}" @selected(($filters['channel'] ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    @foreach($statusLabels as $v => $l)
                        <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <input class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-1">
                <button class="btn btn-warning w-100" data-loading-text="…"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <div class="d-flex gap-2 mt-3">
            <a class="btn btn-success" href="{{ route('platform.communications.export', ['format' => 'xlsx'] + request()->query()) }}">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a class="btn btn-warning" href="{{ route('platform.communications.export', ['format' => 'csv'] + request()->query()) }}">
                <i class="bi bi-filetype-csv me-1"></i>CSV
            </a>
        </div>
    </div>
</div>

{{-- Consommation par entreprise --}}
<div class="platform-card p-3 mb-4">
    <h2 class="h5 mb-3">Consommation par entreprise</h2>
    <div class="d-flex flex-wrap gap-2">
        @forelse($companies as $row)
            <span class="badge bg-secondary">
                {{ $row->company?->name ?? 'Entreprise supprimée' }} — {{ strtoupper($row->channel) }} : {{ $row->total }}
            </span>
        @empty
            <span class="text-secondary">Aucune consommation enregistrée.</span>
        @endforelse
    </div>
</div>

{{-- Tableau des livraisons --}}
<div class="platform-card p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entreprise</th>
                    <th>Canal</th>
                    <th>Catégorie</th>
                    <th>Destinataire</th>
                    <th>Statut</th>
                    <th>Erreur</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $delivery)
                    <tr>
                        <td>{{ $delivery->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $delivery->company?->name ?? 'Supprimée' }}</td>
                        <td>
                            <span class="badge bg-{{ match($delivery->channel) { 'whatsapp' => 'success', 'sms' => 'info', default => 'primary' } }}">
                                {{ strtoupper($delivery->channel) }}
                            </span>
                        </td>
                        <td>
                            {{ ucfirst($delivery->category) }}
                            <br><small class="text-secondary">{{ $delivery->event_type }} #{{ $delivery->event_key }}</small>
                        </td>
                        <td>
                            {{ $delivery->user?->name ?? '—' }}
                            <br><small class="text-secondary">
                                {{ $delivery->user?->email ? \Illuminate\Support\Str::mask($delivery->user->email, '*', 2, max(strlen($delivery->user->email) - 6, 1)) : \Illuminate\Support\Str::mask((string) $delivery->user?->phone, '*', 2, max(strlen((string) $delivery->user?->phone) - 4, 1)) }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$delivery->status] ?? 'secondary' }}">
                                {{ $statusLabels[$delivery->status] ?? $delivery->status }}
                            </span>
                            <br><small>{{ $delivery->attempts }} tentative(s)</small>
                        </td>
                        <td>
                            @if($delivery->last_error)
                                <span style="color: #ff7f89; font-size: .78rem;" title="{{ $delivery->last_error }}">
                                    {{ \Illuminate\Support\Str::limit($delivery->last_error, 40) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($delivery->status === 'failed' && (($delivery->event_type === 'sale' && in_array($delivery->channel, ['email', 'sms', 'whatsapp'])) || ($delivery->event_type === 'inventory' && in_array($delivery->channel, ['sms', 'whatsapp'])) || ($delivery->event_type === 'ecommerce_order' && $delivery->channel === 'email')))
                                <button class="btn btn-sm btn-outline-warning retry-delivery" data-url="{{ route('platform.communications.retry', $delivery) }}">
                                    Relancer
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">Aucune communication.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $deliveries->links() }}
</div>
@endsection

@push('scripts')
<script>
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
                var r = await fetch(b.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ reason: reason })
                });
                var d = await r.json().catch(function() { return {}; });
                if (!r.ok) return Swal.showValidationMessage(d.message || 'Relance impossible.');
                return d;
            }
        }).then(function(r) {
            if (r.isConfirmed) location.reload();
        });
    });
});
</script>
@endpush
