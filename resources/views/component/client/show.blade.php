<div class="saas-detail-list">
    <div>
        <dt>Nom</dt>
        <dd>{{ $Client->name }}</dd>
    </div>
    <div>
        <dt>Téléphone</dt>
        <dd>{{ $Client->phone ?: 'Non renseigné' }}</dd>
    </div>
    <div>
        <dt>Créé par</dt>
        <dd>{{ $Client->user->name }}</dd>
    </div>
    <div>
        <dt>Créé le</dt>
        <dd>{{ $Client->created_at }}</dd>
    </div>
</div>
