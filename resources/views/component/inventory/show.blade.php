<div class="saas-detail-list">
    <div>
        <dt>Type</dt>
        <dd><span class="saas-status-badge {{ $Inventory->type === 1 ? 'is-active' : 'is-inactive' }}">{{ $Inventory->type === 1 ? 'Entrée' : 'Sortie' }}</span></dd>
    </div>
    <div>
        <dt>Produit</dt>
        <dd>{{ $Inventory->product->name }}</dd>
    </div>
    <div>
        <dt>Fournisseur</dt>
        <dd>{{ $Inventory->supplier ? $Inventory->supplier->name : '—' }}</dd>
    </div>
    <div>
        <dt>Quantité avant</dt>
        <dd>{{ $Inventory->qte_before }}</dd>
    </div>
    <div>
        <dt>Quantité saisie</dt>
        <dd>{{ $Inventory->qte_added }}</dd>
    </div>
    <div>
        <dt>Quantité après</dt>
        <dd>{{ $Inventory->qte_after }}</dd>
    </div>
    <div>
        <dt>Note</dt>
        <dd>{{ $Inventory->note ?: '—' }}</dd>
    </div>
    <div>
        <dt>Créé par</dt>
        <dd>{{ $Inventory->user->name }}</dd>
    </div>
    <div>
        <dt>Créé le</dt>
        <dd>{{ $Inventory->created_at }}</dd>
    </div>
</div>
