<div class="saas-detail-hero">
    <div class="saas-detail-media">
        @if ($Product->image)
            <img src="{{ asset('images/' . $Product->image) }}" alt="{{ $Product->name }}">
        @else
            <span class="saas-detail-placeholder"><i class="bi bi-image" aria-hidden="true"></i></span>
        @endif
    </div>
    <div class="saas-detail-summary">
        <span class="saas-modal-eyebrow">Produit</span>
        <h3>{{ $Product->name }}</h3>
        <p>{{ $Product->category->name }} · {{ $Product->supplier ? $Product->supplier->name : 'Sans fournisseur' }}</p>
        <span class="saas-status-badge {{ $Product->status ? 'is-active' : 'is-inactive' }}">{{ $Product->status ? 'Actif' : 'Archivé' }}</span>
    </div>
</div>

<dl class="saas-detail-list saas-detail-grid">
    <div><dt>Quantité en stock</dt><dd>{{ $Product->qte }}</dd></div>
    <div><dt>Marge de sécurité</dt><dd>{{ $Product->margin }}</dd></div>
    <div><dt>Prix de vente</dt><dd>{{ number_format((float) $Product->price, 0, ',', ' ') }} FCFA</dd></div>
    <div><dt>Prix d'achat</dt><dd>{{ number_format((float) $Product->purchase_price, 0, ',', ' ') }} FCFA</dd></div>
    <div><dt>Prix TTC</dt><dd>{{ $Product->price_ttc !== null ? number_format((float) $Product->price_ttc, 0, ',', ' ') . ' FCFA' : '—' }}</dd></div>
    <div><dt>Bénéfice unitaire</dt><dd>{{ number_format((float) $Product->profit, 0, ',', ' ') }} FCFA</dd></div>
    <div><dt>Créé par</dt><dd>{{ $Product->user->name }}</dd></div>
    <div><dt>Créé le</dt><dd>{{ $Product->created_at->format('d/m/Y à H:i') }}</dd></div>
</dl>
