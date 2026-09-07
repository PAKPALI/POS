<section class="pos-sale-detail">
    <div class="pos-datatable-shell"><div class="table-responsive"><table class="table align-middle"><thead><tr><th scope="col">#</th><th scope="col">Image</th><th scope="col">Produit</th><th scope="col">Quantité</th><th scope="col">Prix unitaire</th><th scope="col">Prix total</th></tr></thead><tbody>
        @foreach($Sale->saleDetails as $detail)
            <tr><th scope="row">{{ $loop->iteration }}</th><td>@if($detail->product->image)<img class="pos-detail-product-image" src="{{ asset('images/'.$detail->product->image) }}" alt="Image de {{ $detail->product->name }}" width="64" height="64">@else<span class="saas-status-badge is-neutral">Sans image</span>@endif</td><td>{{ $detail->product->name }}</td><td>{{ $detail->quantity }}</td><td>@money($detail->unit_price)</td><td>@money($detail->total_price)</td></tr>
        @endforeach
    </tbody></table></div></div>
    <dl class="saas-detail-list pos-sale-summary">
        <div><dt>Client</dt><dd>{{ $Sale->client->name ?? 'Aucun' }}</dd></div><div><dt>Montant initial</dt><dd>@money($Sale->amount_init)</dd></div><div><dt>Remise</dt><dd>@money($Sale->discount)</dd></div><div><dt>Montant payé</dt><dd>@money($Sale->total_amount)</dd></div><div><dt>Montant reçu</dt><dd>@money($Sale->received_amount)</dd></div><div><dt>Monnaie rendue</dt><dd>@money($Sale->remaining_amount)</dd></div>
    </dl>
</section>
