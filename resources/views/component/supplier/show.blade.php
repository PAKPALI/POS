<div class="saas-detail-hero is-compact">
    <div class="saas-detail-icon"><i class="bi bi-truck" aria-hidden="true"></i></div>
    <div class="saas-detail-summary">
        <span class="saas-modal-eyebrow">Fournisseur</span>
        <h3>{{ $Supplier->name }}</h3>
        <p>{{ $Supplier->products->count() }} produit{{ $Supplier->products->count() > 1 ? 's' : '' }} associé{{ $Supplier->products->count() > 1 ? 's' : '' }}</p>
        <span class="saas-status-badge {{ $Supplier->status ? 'is-active' : 'is-inactive' }}">{{ $Supplier->status ? 'Actif' : 'Archivé' }}</span>
    </div>
</div>

<dl class="saas-detail-list saas-detail-grid">
    <div><dt>Contact / Adresse</dt><dd>{{ $Supplier->contact ?? '—' }}</dd></div>
    <div><dt>Téléphone</dt><dd>{{ $Supplier->phone ?? '—' }}</dd></div>
    <div><dt>WhatsApp</dt><dd>{{ $Supplier->whatsapp ?? '—' }}</dd></div>
    <div><dt>Produits associés</dt><dd>{{ $Supplier->products->count() }}</dd></div>
    <div><dt>Créé par</dt><dd>{{ $Supplier->user->name }}</dd></div>
    <div><dt>Créé le</dt><dd>{{ $Supplier->created_at->format('d/m/Y à H:i') }}</dd></div>
</dl>
