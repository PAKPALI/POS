<section class="pos-sale-detail">
    <div class="pos-datatable-shell"><div class="table-responsive"><table class="table align-middle"><thead><tr><th scope="col">#</th><th scope="col">Image</th><th scope="col">Produit</th><th scope="col">Quantité</th><th scope="col">Prix unitaire</th><th scope="col">Prix total</th></tr></thead><tbody>
        @foreach($Sale->saleDetails as $detail)
            <tr><th scope="row">{{ $loop->iteration }}</th><td>@if($detail->product->image)<img class="pos-detail-product-image" src="{{ asset('images/'.$detail->product->image) }}" alt="Image de {{ $detail->product->name }}" width="64" height="64">@else<span class="saas-status-badge is-neutral">Sans image</span>@endif</td><td>{{ $detail->product->name }}</td><td>{{ $detail->quantity }}</td><td>@money($detail->unit_price)</td><td>@money($detail->total_price)</td></tr>
        @endforeach
    </tbody></table></div></div>
    <dl class="saas-detail-list pos-sale-summary">
        <div><dt>Client</dt><dd>{{ $Sale->client->name ?? 'Aucun' }}</dd></div><div><dt>Montant initial</dt><dd>@money($Sale->amount_init)</dd></div><div><dt>Remise</dt><dd>@money($Sale->discount)</dd></div><div><dt>Montant payé</dt><dd>@money($Sale->total_amount)</dd></div><div><dt>Montant reçu</dt><dd>@money($Sale->received_amount)</dd></div><div><dt>Monnaie rendue</dt><dd>@money($Sale->remaining_amount)</dd></div>
    </dl>

    <div class="saas-detail-section mt-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div>
                <span class="saas-modal-eyebrow">Communication</span>
                <h4 class="h6 mb-1">Historique des envois de facture</h4>
                <p class="text-muted small mb-0">Canal et numéro utilisés pour chaque facture envoyée.</p>
            </div>
            @if($Sale->communicationLogs->isNotEmpty())
                <span class="saas-status-badge is-info">{{ $Sale->communicationLogs->count() }} envoi(s)</span>
            @endif
        </div>
        @if($Sale->communicationLogs->isNotEmpty())
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr><th>Date</th><th>Canal</th><th>Numéro</th><th>Pays</th><th>Unités</th></tr>
                    </thead>
                    <tbody>
                    @foreach($Sale->communicationLogs as $log)
                        @php($channel = strtolower((string) $log->channel))
                        <tr>
                            <td>{{ $log->sent_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                            <td><span class="saas-status-badge is-{{ $channel === 'whatsapp' ? 'success' : 'info' }}"><i class="bi bi-{{ $channel === 'whatsapp' ? 'whatsapp' : 'chat-text' }} me-1" aria-hidden="true"></i>{{ strtoupper($channel) }}</span></td>
                            <td class="font-monospace">{{ $log->recipient }}</td>
                            <td>{{ $log->country_code }}</td>
                            <td>{{ number_format((int) $log->units) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="saas-empty-state py-4"><i class="bi bi-send-x" aria-hidden="true"></i><span>Aucun envoi de facture enregistré pour cette vente.</span></div>
        @endif
    </div>
</section>
