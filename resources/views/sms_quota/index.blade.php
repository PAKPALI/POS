@extends('layouts.saas')
@section('title', 'Quotas SMS et WhatsApp')
@push('styles')
<link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
@endpush

@section('content')
<div class="saas-page-heading"><div><h1>Quotas SMS &amp; WhatsApp</h1><p>Contrôlez les unités disponibles et rechargez les canaux de communication.</p></div><a class="saas-btn saas-btn-ghost" href="{{ route('communications.index') }}"><i class="bi bi-clock-history"></i> Voir la consommation</a></div>
<div class="saas-metric-grid saas-metric-grid-2">
    <article class="saas-metric"><div class="saas-metric-icon"><i class="bi bi-chat-text"></i></div><span>SMS disponibles</span><strong>{{ number_format($company->sms_count ?? 0, 0, ',', ' ') }}</strong><small>{{ $smsUnitPrice }} FCFA par SMS</small></article>
    <article class="saas-metric"><div class="saas-metric-icon"><i class="bi bi-whatsapp"></i></div><span>WhatsApp disponibles</span><strong>{{ number_format($company->whatsapp_count ?? 0, 0, ',', ' ') }}</strong><small>{{ $whatsappUnitPrice }} FCFA par message</small></article>
</div>
<section class="saas-card">
    <div class="saas-card-head"><div><h2>Acheter des quotas</h2><p class="saas-card-description">Le solde est crédité uniquement après confirmation sécurisée du paiement.</p></div><i class="bi bi-shield-lock"></i></div>
    <form id="quota-checkout-form">@csrf
        <div class="saas-quota-form-grid"><div class="saas-form-group"><label for="sms_quantity"><i class="bi bi-chat-text"></i> Nombre de SMS</label><input type="number" name="sms_quantity" id="sms_quantity" value="0" min="0" max="100000" class="quota-quantity"><small>Prix unitaire : {{ $smsUnitPrice }} FCFA</small></div><div class="saas-form-group"><label for="whatsapp_quantity"><i class="bi bi-whatsapp"></i> Nombre de messages WhatsApp</label><input type="number" name="whatsapp_quantity" id="whatsapp_quantity" value="0" min="0" max="100000" class="quota-quantity"><small>Prix unitaire : {{ $whatsappUnitPrice }} FCFA</small></div></div>
        <div class="saas-alert saas-alert-info d-flex justify-content-between align-items-center"><span>Montant du paiement</span><strong><span id="quota-total">0</span> FCFA</strong></div>
        <div class="saas-modal-actions"><button type="submit" class="saas-btn saas-btn-primary" id="quota-checkout-button" data-loading-text="Paiement en cours…"><i class="bi bi-credit-card"></i> Payer avec KPrimePay</button></div>
    </form>
</section>
<section class="saas-card">
    <div class="saas-card-head"><div><h2>Historique des achats</h2><p class="saas-card-description">État de chaque transaction de recharge.</p></div><span class="saas-count-badge">{{ $payments->total() }}</span></div>
    <div class="table-responsive"><table class="saas-data-table"><thead><tr><th>Référence</th><th>SMS</th><th>WhatsApp</th><th>Montant</th><th>Statut</th><th>Date</th></tr></thead><tbody>
    @forelse($payments as $payment)
        @php $statusClass = match($payment->status) {'paid'=>'is-success','failed'=>'is-danger','pending'=>'is-pending',default=>'is-neutral'}; $statusLabel = match($payment->status) {'paid'=>'Payé','failed'=>'Échoué','expired'=>'Expiré','pending'=>'En attente',default=>'Créé'}; @endphp
        <tr><td><small>{{ $payment->transaction_id }}</small></td><td>{{ $payment->sms_quantity }}</td><td>{{ $payment->whatsapp_quantity }}</td><td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td><td><span class="saas-status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td><td>{{ $payment->created_at->format('d/m/Y H:i') }}</td></tr>
    @empty<tr><td colspan="6"><div class="saas-empty-state is-compact"><i class="bi bi-receipt"></i><span>Aucun achat enregistré.</span></div></td></tr>@endforelse
    </tbody></table></div><div class="saas-pagination-row"><span>{{ $payments->total() }} transaction(s)</span><div>{{ $payments->links('pagination::bootstrap-5') }}</div></div>
</section>

@push('scripts')
<script>
$(function () {
    const smsPrice = {{ $smsUnitPrice }}, whatsappPrice = {{ $whatsappUnitPrice }};
    const paymentStatusUrl = @json(route('sms-quota.status', ['transactionId' => '__TRANSACTION__']));
    function updateTotal() { const sms = Math.max(0, parseInt($('#sms_quantity').val(), 10) || 0); const whatsapp = Math.max(0, parseInt($('#whatsapp_quantity').val(), 10) || 0); $('#quota-total').text(new Intl.NumberFormat('fr-FR').format((sms * smsPrice) + (whatsapp * whatsappPrice))); }
    function waitForPayment(transactionId, paymentWindow) { const startedAt = Date.now(); return new Promise(function (resolve, reject) { function check() { $.getJSON(paymentStatusUrl.replace('__TRANSACTION__', encodeURIComponent(transactionId))).done(function (response) { if (response.payment_status === 'paid') { if (paymentWindow && !paymentWindow.closed) paymentWindow.close(); resolve(response); return; } if (response.payment_status === 'failed' || response.payment_status === 'expired') { if (paymentWindow && !paymentWindow.closed) paymentWindow.close(); reject(new Error(response.payment_status === 'expired' ? 'Le délai de paiement a expiré.' : 'Le paiement a échoué ou a été annulé.')); return; } if (Date.now() - startedAt >= 300000) { reject(new Error('La confirmation prend plus de temps que prévu. Consultez de nouveau l’historique dans quelques instants.')); return; } window.setTimeout(check, 3000); }).fail(function (xhr) { if ([401,403,404].includes(xhr.status)) { reject(new Error('Le suivi sécurisé du paiement n’est plus disponible.')); return; } if (Date.now() - startedAt >= 300000) { reject(new Error('Impossible de confirmer le paiement pour le moment.')); return; } window.setTimeout(check, 5000); }); } check(); }); }
    $('.quota-quantity').on('input change', updateTotal); updateTotal();
    $('#quota-checkout-form').on('submit', function (event) { event.preventDefault(); const button = document.getElementById('quota-checkout-button'); const paymentWindow = window.open('', 'kprimepay-checkout', 'popup=yes,width=520,height=760,resizable=yes,scrollbars=yes'); window.ServerButtonLoader.withLoader(button, function () { return Promise.resolve($.ajax({url:@json(route('sms-quota.checkout')),method:'POST',data:$('#quota-checkout-form').serialize(),dataType:'json'})).then(function (response) { if (!response.status || !response.checkout_url || !response.transaction_id) throw new Error(response.msg || 'Le checkout ne peut pas être lancé.'); if (!paymentWindow) { window.location.assign(response.checkout_url); return new Promise(function () {}); } paymentWindow.location.replace(response.checkout_url); paymentWindow.focus(); return waitForPayment(response.transaction_id, paymentWindow); }); }, 'Paiement en cours…').then(function () { window.location.reload(); }).catch(function (error) { if (paymentWindow && !paymentWindow.closed) paymentWindow.close(); const response = error.responseJSON || {}; Swal.fire({icon:'error',title:response.title || 'Paiement impossible',text:response.msg || error.message || 'Impossible de contacter le service de paiement.'}); }); });
});
</script>
@endpush
@endsection
