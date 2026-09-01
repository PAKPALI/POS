<div class="saas-detail-hero">
    <div class="saas-detail-media">
        @if ($MenuProduct->image)
            <img src="{{ asset('images/' . $MenuProduct->image) }}" alt="{{ $MenuProduct->name }}">
        @else
            <span class="saas-detail-placeholder"><i class="bi bi-grid" aria-hidden="true"></i></span>
        @endif
    </div>
    <div class="saas-detail-summary">
        <span class="saas-modal-eyebrow">Menu composé</span>
        <h3>{{ $MenuProduct->name }}</h3>
        <p>{{ $MenuProduct->category->name }} · {{ $MenuProduct->MenuProducts->count() }} composant{{ $MenuProduct->MenuProducts->count() > 1 ? 's' : '' }}</p>
        <span class="saas-status-badge {{ $MenuProduct->status ? 'is-active' : 'is-inactive' }}">{{ $MenuProduct->status ? 'Actif' : 'Archivé' }}</span>
    </div>
</div>

<dl class="saas-detail-list saas-detail-grid">
    <div><dt>Quantité disponible</dt><dd>{{ $MenuProduct->qte }}</dd></div>
    <div><dt>Marge de sécurité</dt><dd>{{ $MenuProduct->margin }}</dd></div>
    <div><dt>Prix unitaire</dt><dd>{{ number_format((float) $MenuProduct->price, 0, ',', ' ') }} FCFA</dd></div>
    <div><dt>Prix d'achat</dt><dd>{{ number_format((float) $MenuProduct->purchase_price, 0, ',', ' ') }} FCFA</dd></div>
    <div><dt>Bénéfice unitaire</dt><dd>{{ number_format((float) $MenuProduct->profit, 0, ',', ' ') }} FCFA</dd></div>
    <div><dt>Créé par</dt><dd>{{ $MenuProduct->user->name }}</dd></div>
    <div><dt>Créé le</dt><dd>{{ $MenuProduct->created_at->format('d/m/Y à H:i') }}</dd></div>
</dl>

<section class="saas-detail-section" aria-labelledby="menu-products-title">
    <div class="saas-detail-section-head">
        <div><span class="saas-modal-eyebrow">Composition</span><h4 id="menu-products-title">Produits du menu</h4></div>
        <span class="saas-count-badge">{{ $MenuProduct->MenuProducts->count() }}</span>
    </div>
    <div class="saas-composition-list">
        @forelse ($MenuProduct->MenuProducts as $item)
            <article class="saas-composition-item">
                <div class="saas-composition-image">
                    @if ($item->product->image)<img src="{{ asset('images/' . $item->product->image) }}" alt="">@else<i class="bi bi-image" aria-hidden="true"></i>@endif
                </div>
                <div><strong>{{ $item->product->name }}</strong><span>Stock actuel : {{ $item->product->qte }}</span></div>
                <span class="saas-composition-quantity">× {{ $item->quantity }}</span>
            </article>
        @empty
            <div class="saas-empty-state is-compact"><i class="bi bi-inbox"></i><strong>Aucun produit associé</strong></div>
        @endforelse
    </div>
</section>
