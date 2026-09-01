<div class="saas-detail-list">
    <div>
        <dt>Type</dt>
        <dd>
            @if($transaction->type == 'IN')
                <span class="saas-status-badge is-active">Entrée</span>
            @elseif($transaction->type == 'OUT')
                <span class="saas-status-badge is-inactive">Sortie</span>
            @else
                <span class="saas-status-badge is-active">Transfert</span>
            @endif
        </dd>
    </div>
    <div>
        <dt>Montant</dt>
        <dd style="font-weight: 800; font-size: 1rem;">{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA</dd>
    </div>
    <div>
        <dt>Caisse source</dt>
        <dd>{{ $transaction->fromCash->name ?? '—' }}</dd>
    </div>
    <div>
        <dt>Caisse destination</dt>
        <dd>{{ $transaction->toCash->name ?? '—' }}</dd>
    </div>
    <div>
        <dt>Utilisateur</dt>
        <dd>{{ $transaction->user->name }}</dd>
    </div>
    <div>
        <dt>Date</dt>
        <dd>{{ $transaction->created_at->format('d-m-Y H:i:s') }}</dd>
    </div>
    <div>
        <dt>Description</dt>
        <dd>{{ $transaction->description ?: '—' }}</dd>
    </div>
</div>
