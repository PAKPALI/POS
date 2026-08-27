@extends('layouts.layout')
@section('content')
<div class="container"><div class="row justify-content-center"><div class="col-xl-10">
    <h1 class="page-header">Quotas SMS et WhatsApp</h1>
    @if(session('info'))<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ session('info') }}</div>@endif
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6"><div class="card h-100 border-primary"><div class="card-body text-center">
            <i class="bi bi-chat-text fs-2 text-primary"></i><h5 class="mt-2">SMS disponibles</h5>
            <p class="display-5 mb-1">{{ number_format($company->sms_count ?? 0, 0, ',', ' ') }}</p>
            <small class="text-muted">{{ $smsUnitPrice }} FCFA par SMS</small>
        </div></div></div>
        <div class="col-12 col-md-6"><div class="card h-100 border-success"><div class="card-body text-center">
            <i class="bi bi-whatsapp fs-2 text-success"></i><h5 class="mt-2">WhatsApp disponibles</h5>
            <p class="display-5 mb-1">{{ number_format($company->whatsapp_count ?? 0, 0, ',', ' ') }}</p>
            <small class="text-muted">{{ $whatsappUnitPrice }} FCFA par message</small>
        </div></div></div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Acheter des quotas</h5></div>
        <div class="card-body"><form id="quota-checkout-form">@csrf
            <div class="row g-3">
                <div class="col-12 col-md-6"><label for="sms_quantity" class="form-label">Nombre de SMS</label>
                    <input type="number" name="sms_quantity" id="sms_quantity" value="0" min="0" max="100000" class="form-control quota-quantity">
                    <small class="text-muted">Prix unitaire : {{ $smsUnitPrice }} FCFA</small></div>
                <div class="col-12 col-md-6"><label for="whatsapp_quantity" class="form-label">Nombre de messages WhatsApp</label>
                    <input type="number" name="whatsapp_quantity" id="whatsapp_quantity" value="0" min="0" max="100000" class="form-control quota-quantity">
                    <small class="text-muted">Prix unitaire : {{ $whatsappUnitPrice }} FCFA</small></div>
            </div>
            <div class="alert alert-secondary d-flex justify-content-between align-items-center mt-4 mb-3">
                <span>Montant du paiement</span><strong><span id="quota-total">0</span> FCFA</strong>
            </div>
            <button type="submit" class="btn btn-primary" id="quota-checkout-button" data-loading-text="Paiement en cours…"><i class="bi bi-shield-lock me-1"></i>Payer avec KPrimePay</button>
            <p class="small text-muted mt-2 mb-0">Les quotas sont crédités uniquement après confirmation du paiement par KPrimePay.</p>
        </form></div>
    </div>
    <div class="card"><div class="card-header"><h5 class="mb-0">Historique des achats</h5></div><div class="card-body">
        <div class="table-responsive"><table class="table align-middle">
            <thead><tr><th>Référence</th><th>SMS</th><th>WhatsApp</th><th>Montant</th><th>Statut</th><th>Date</th></tr></thead><tbody>
            @forelse($payments as $payment)
                @php
                    $statusClass = match($payment->status) {'paid' => 'success', 'failed' => 'danger', 'expired' => 'secondary', 'pending' => 'warning', default => 'secondary'};
                    $statusLabel = match($payment->status) {'paid' => 'Payé', 'failed' => 'Échoué', 'expired' => 'Expiré', 'pending' => 'En attente', default => 'Créé'};
                @endphp
                <tr><td><small>{{ $payment->transaction_id }}</small></td><td>{{ $payment->sms_quantity }}</td><td>{{ $payment->whatsapp_quantity }}</td>
                    <td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td><td><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-4">Aucun achat enregistré.</td></tr>@endforelse
            </tbody></table></div>{{ $payments->links('pagination::bootstrap-5') }}
    </div></div>
</div></div></div>
<script>
$(function () {
    const smsPrice = {{ $smsUnitPrice }}, whatsappPrice = {{ $whatsappUnitPrice }};
    const paymentStatusUrl = @json(route('sms-quota.status', ['transactionId' => '__TRANSACTION__']));

    function updateTotal() {
        const sms = Math.max(0, parseInt($('#sms_quantity').val(), 10) || 0);
        const whatsapp = Math.max(0, parseInt($('#whatsapp_quantity').val(), 10) || 0);
        $('#quota-total').text(new Intl.NumberFormat('fr-FR').format((sms * smsPrice) + (whatsapp * whatsappPrice)));
    }

    function waitForPayment(transactionId, paymentWindow) {
        const startedAt = Date.now();
        return new Promise(function (resolve, reject) {
            function check() {
                $.getJSON(paymentStatusUrl.replace('__TRANSACTION__', encodeURIComponent(transactionId)))
                    .done(function (response) {
                        if (response.payment_status === 'paid') {
                            if (paymentWindow && !paymentWindow.closed) paymentWindow.close();
                            resolve(response);
                            return;
                        }
                        if (response.payment_status === 'failed' || response.payment_status === 'expired') {
                            if (paymentWindow && !paymentWindow.closed) paymentWindow.close();
                            reject(new Error(response.payment_status === 'expired' ? 'Le délai de paiement a expiré.' : 'Le paiement a échoué ou a été annulé.'));
                            return;
                        }
                        if (Date.now() - startedAt >= 300000) {
                            reject(new Error('La confirmation prend plus de temps que prévu. Consultez de nouveau l’historique dans quelques instants.'));
                            return;
                        }
                        window.setTimeout(check, 3000);
                    })
                    .fail(function (xhr) {
                        if (xhr.status === 401 || xhr.status === 403 || xhr.status === 404) {
                            reject(new Error('Le suivi sécurisé du paiement n’est plus disponible.'));
                            return;
                        }
                        if (Date.now() - startedAt >= 300000) {
                            reject(new Error('Impossible de confirmer le paiement pour le moment.'));
                            return;
                        }
                        window.setTimeout(check, 5000);
                    });
            }
            check();
        });
    }

    $('.quota-quantity').on('input change', updateTotal);
    updateTotal();

    $('#quota-checkout-form').on('submit', function (event) {
        event.preventDefault();
        const button = document.getElementById('quota-checkout-button');
        const paymentWindow = window.open('', 'kprimepay-checkout', 'popup=yes,width=520,height=760,resizable=yes,scrollbars=yes');

        window.ServerButtonLoader.withLoader(button, function () {
            return Promise.resolve($.ajax({
                url: @json(route('sms-quota.checkout')),
                method: 'POST',
                data: $('#quota-checkout-form').serialize(),
                dataType: 'json'
            })).then(function (response) {
                if (!response.status || !response.checkout_url || !response.transaction_id) {
                    throw new Error(response.msg || 'Le checkout ne peut pas être lancé.');
                }
                if (!paymentWindow) {
                    window.location.assign(response.checkout_url);
                    return new Promise(function () {});
                }
                paymentWindow.location.replace(response.checkout_url);
                paymentWindow.focus();
                return waitForPayment(response.transaction_id, paymentWindow);
            });
        }, 'Paiement en cours…').then(function () {
            window.location.reload();
        }).catch(function (error) {
            if (paymentWindow && !paymentWindow.closed) paymentWindow.close();
            const response = error.responseJSON || {};
            Swal.fire({icon:'error', title:response.title || 'Paiement impossible', text:response.msg || error.message || 'Impossible de contacter le service de paiement.'});
        });
    });
});
</script>
@endsection
