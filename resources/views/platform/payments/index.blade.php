@extends('layouts.platform')

@section('title', 'Paiements et quotas')
@section('page-title', 'Paiements et quotas')

@section('content')
<div class="platform-page-stack">
    <section class="platform-card platform-finance-panel" aria-labelledby="payment-profit-title">
        <header class="platform-panel-head"><div><p class="platform-eyebrow">Suivi financier</p><h2 id="payment-profit-title">Rentabilité des paiements confirmés</h2><p>Les paiements en attente ou échoués sont exclus du calcul.</p></div><span class="platform-profit-chip" aria-label="Bénéfice total : {{ number_format($financials['total_profit'], 0, ',', ' ') }} XOF"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i><span><small>Bénéfice total</small><strong>{{ number_format($financials['total_profit'], 0, ',', ' ') }} XOF</strong></span></span></header>
        <div class="table-responsive"><table class="table table-dark align-middle mb-0"><thead><tr><th>Canal</th><th>Chiffre d’affaires</th><th>Coût fournisseur</th><th>Bénéfice</th></tr></thead><tbody><tr><td><i class="bi bi-chat-dots me-2 text-theme" aria-hidden="true"></i>SMS</td><td>{{ number_format($financials['sms_revenue'],0,',',' ') }} XOF</td><td>{{ number_format($financials['sms_cost'],0,',',' ') }} XOF</td><td class="text-success fw-bold">{{ number_format($financials['sms_profit'],0,',',' ') }} XOF</td></tr><tr><td><i class="bi bi-whatsapp me-2 text-success" aria-hidden="true"></i>WhatsApp</td><td>{{ number_format($financials['whatsapp_revenue'],0,',',' ') }} XOF</td><td>{{ number_format($financials['whatsapp_cost'],0,',',' ') }} XOF</td><td class="text-success fw-bold">{{ number_format($financials['whatsapp_profit'],0,',',' ') }} XOF</td></tr><tr class="fw-bold"><td>Total combiné</td><td>{{ number_format($financials['total_revenue'],0,',',' ') }} XOF</td><td>{{ number_format($financials['total_cost'],0,',',' ') }} XOF</td><td class="text-warning">{{ number_format($financials['total_profit'],0,',',' ') }} XOF</td></tr></tbody></table></div>
    </section>

    <section class="platform-summary-grid" aria-label="Résumé des paiements">
        @foreach([['Tous',$summary['total'],'bi-credit-card','accent'],['Confirmés',$summary['paid'],'bi-check2-circle','success'],['En attente',$summary['pending'],'bi-hourglass-split','warning'],['Échecs / expirés',$summary['failed'],'bi-exclamation-octagon','danger'],['Montant confirmé',number_format($summary['revenue'],0,',',' ').' XOF','bi-cash-stack','violet']] as [$label,$value,$icon,$tone])
            <article class="platform-summary-metric is-{{ $tone }}"><span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span><span>{{ $label }}</span><strong>{{ $value }}</strong></article>
        @endforeach
    </section>

    <section class="platform-card platform-filter-card">
        <header><p class="platform-eyebrow"><i class="bi bi-funnel" aria-hidden="true"></i> Recherche</p><h2 class="h5 mb-0">Filtrer l’historique</h2></header>
        <form method="GET" class="platform-filter-grid">
            <div class="platform-filter-field is-wide"><label class="form-label" for="q">Transaction ou entreprise</label><input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Transaction, référence ou entreprise"></div>
            <div class="platform-filter-field"><label class="form-label" for="status">Statut</label><select id="status" name="status" class="form-select"><option value="">Tous</option>@foreach(['created'=>'Créé','pending'=>'En attente','paid'=>'Payé','failed'=>'Échoué','expired'=>'Expiré'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select></div>
            <div class="platform-filter-field"><label class="form-label" for="from">Du</label><input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}"></div>
            <div class="platform-filter-field"><label class="form-label" for="to">Au</label><input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}"></div>
            <button class="btn btn-warning platform-filter-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i>Filtrer</button>
        </form>
    </section>

    <section class="platform-card platform-data-panel" aria-labelledby="payment-history-title">
        <header class="platform-panel-head"><div><p class="platform-eyebrow">Transactions</p><h2 id="payment-history-title">Historique des paiements</h2><p>{{ number_format($payments->total(), 0, ',', ' ') }} paiement(s) correspondant à votre recherche.</p></div>@if(request()->hasAny(['q','status','from','to']))<a href="{{ route('platform.payments.index') }}" class="platform-panel-link"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>Réinitialiser</a>@endif</header>
        <div class="table-responsive"><table class="table table-dark table-hover align-middle"><thead><tr><th>Transaction</th><th>Entreprise</th><th>Quotas</th><th>Prix appliqués</th><th>Montant</th><th>Statut</th><th>Date</th><th><span class="visually-hidden">Détails</span></th></tr></thead><tbody>@forelse($payments as $payment)<tr><td><small>{{ $payment->transaction_id }}</small><br><small class="text-secondary">{{ $payment->kpp_reference }}</small></td><td>{{ $payment->company?->name ?? 'Entreprise supprimée' }}</td><td>{{ $payment->sms_quantity }} SMS<br>{{ $payment->whatsapp_quantity }} WhatsApp</td><td><small>{{ $payment->sms_unit_price !== null ? $payment->sms_unit_price.' XOF/SMS' : 'Historique non disponible' }}<br>{{ $payment->whatsapp_unit_price !== null ? $payment->whatsapp_unit_price.' XOF/WhatsApp' : 'Historique non disponible' }}</small></td><td>{{ number_format($payment->amount,0,',',' ') }} {{ $payment->currency }}</td><td><span class="platform-status-chip {{ $payment->status==='paid'?'is-success':(in_array($payment->status,['failed','expired'])?'is-danger':'is-warning') }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $payment->status }}</span></td><td>{{ $payment->created_at?->format('d/m/Y H:i') }}</td><td><a class="platform-action-btn" href="{{ route('platform.payments.show',$payment) }}" aria-label="Voir le paiement"><i class="bi bi-eye" aria-hidden="true"></i></a></td></tr>@empty<tr><td colspan="8" class="text-center text-secondary py-5">Aucun paiement.</td></tr>@endforelse</tbody></table></div>
        <div class="platform-pagination">{{ $payments->links() }}</div>
    </section>
</div>
@endsection
