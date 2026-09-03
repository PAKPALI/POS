@extends('layouts.platform')
@section('title', 'Paiement '.$payment->transaction_id)
@section('page-title', 'Detail du paiement')
@section('content')
<div class="d-flex justify-content-between gap-2 mb-4">
    <a href="{{ route('platform.payments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Paiements</a>
    @if($payment->status !== 'paid')
        <button class="btn btn-outline-warning" id="reconcilePayment" data-url="{{ route('platform.payments.reconcile',$payment) }}"><i class="bi bi-arrow-repeat me-1"></i> Verifier chez KPrimePay</button>
    @endif
</div>
<div class="row g-4">
    <div class="col-xl-7">
        <div class="platform-card p-4">
            <header class="platform-panel-head">
                <div>
                    <p class="platform-eyebrow"><i class="bi bi-receipt" aria-hidden="true"></i> Transaction</p>
                    <h2 class="h5 mb-0">{{ $payment->transaction_id }}</h2>
                </div>
                <span class="platform-status-chip {{ $payment->status==='paid'?'is-success':(in_array($payment->status,['failed','expired'])?'is-danger':'is-warning') }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $payment->status }}</span>
            </header>
            <dl class="row mb-0">
                <dt class="col-5">Reference interne</dt><dd class="col-7 text-break">{{ $payment->transaction_id }}</dd>
                <dt class="col-5">Reference KPrimePay</dt><dd class="col-7 text-break">{{ $payment->kpp_reference ?: 'Non recue' }}</dd>
                <dt class="col-5">Montant</dt><dd class="col-7">{{ number_format($payment->amount,0,',',' ') }} {{ $payment->currency }}</dd>
                <dt class="col-5">Creation</dt><dd class="col-7">{{ $payment->created_at?->format('d/m/Y H:i:s') }}</dd>
                <dt class="col-5">Confirmation</dt><dd class="col-7">{{ $payment->paid_at?->format('d/m/Y H:i:s') ?? 'Non confirmee' }}</dd>
                <dt class="col-5">Erreur</dt><dd class="col-7">{{ $payment->failure_reason ?: 'Aucune' }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="platform-card p-4">
            <header class="platform-panel-head">
                <div>
                    <p class="platform-eyebrow"><i class="bi bi-buildings" aria-hidden="true"></i> Entreprise et quotas</p>
                    <h2 class="h5 mb-0">{{ $payment->company?->name ?? 'Entreprise supprimee' }}</h2>
                </div>
                @if($payment->company)
                    <a href="{{ route('platform.companies.show',$payment->company) }}" class="platform-panel-link"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> Voir</a>
                @endif
            </header>
            <div class="row g-3">
                <div class="col-6">
                    <div class="platform-summary-metric is-info">
                        <span class="platform-summary-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                        <span>SMS a crediter</span>
                        <strong>{{ $payment->sms_quantity }}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="platform-summary-metric is-success">
                        <span class="platform-summary-icon"><i class="bi bi-whatsapp" aria-hidden="true"></i></span>
                        <span>WhatsApp a crediter</span>
                        <strong>{{ $payment->whatsapp_quantity }}</strong>
                    </div>
                </div>
            </div>
            <div class="row g-2 mt-3">
                <div class="col-6"><div class="text-secondary small">Tarif SMS : {{ $payment->sms_unit_price !== null ? $payment->sms_unit_price.' XOF/unite' : 'Ancien tarif' }}</div></div>
                <div class="col-6"><div class="text-secondary small">Tarif WhatsApp : {{ $payment->whatsapp_unit_price !== null ? $payment->whatsapp_unit_price.' XOF/unite' : 'Ancien tarif' }}</div></div>
            </div>
            <p class="text-secondary small mt-3 mb-0">Demande initiée par {{ $payment->user?->name ?? 'Utilisateur supprime' }} ({{ $payment->user?->email }}).</p>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@if($payment->status !== 'paid')
<script>
document.getElementById('reconcilePayment')?.addEventListener('click', function() {
    var button = this;
    Swal.fire({
        title: 'Verifier ce paiement ?',
        text: 'Le statut sera demande directement a KPrimePay. Aucun credit manuel ne sera effectue.',
        icon: 'question',
        input: 'textarea',
        inputLabel: 'Motif obligatoire',
        inputPlaceholder: 'Ex. : paiement signale par le client...',
        showCancelButton: true,
        confirmButtonText: 'Oui, verifier',
        cancelButtonText: 'Annuler',
        showLoaderOnConfirm: true,
        allowOutsideClick: function() { return !Swal.isLoading(); },
        allowEscapeKey: function() { return !Swal.isLoading(); },
        preConfirm: async function(reason) {
            if (!reason || reason.trim().length < 5) return Swal.showValidationMessage('Indiquez un motif d\'au moins 5 caracteres.');
            var response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ reason: reason.trim() })
            });
            var data = await response.json().catch(function() { return {}; });
            if (!response.ok) return Swal.showValidationMessage(data.message || 'Verification impossible.');
            return data;
        }
    }).then(function(result) {
        if (result.isConfirmed) Swal.fire({ icon: 'success', title: 'Verification terminee', text: result.value.message }).then(function() { location.reload(); });
    });
});
</script>
@endif
@endpush
