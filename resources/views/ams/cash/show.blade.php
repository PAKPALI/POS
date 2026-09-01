<div class="saas-detail-list">
    <div>
        <dt>Code</dt>
        <dd>{{ $cashAccount->code }}</dd>
    </div>
    <div>
        <dt>Nom</dt>
        <dd>{{ $cashAccount->name }}</dd>
    </div>
    <div>
        <dt>Solde</dt>
        <dd style="font-weight: 800; font-size: 1rem;">{{ number_format($cashAccount->balance, 0, ',', ' ') }} {{ $cashAccount->currency }}</dd>
    </div>
    <div>
        <dt>Devise</dt>
        <dd>{{ $cashAccount->currency }}</dd>
    </div>
    <div>
        <dt>Caisse principale</dt>
        <dd><span class="saas-status-badge {{ $cashAccount->is_default ? 'is-active' : 'is-inactive' }}">{{ $cashAccount->is_default ? 'Oui' : 'Non' }}</span></dd>
    </div>
    <div>
        <dt>Caisse de taxe</dt>
        <dd><span class="saas-status-badge {{ $cashAccount->is_tax ? 'is-active' : 'is-inactive' }}">{{ $cashAccount->is_tax ? 'Oui' : 'Non' }}</span></dd>
    </div>
    <div>
        <dt>Statut</dt>
        <dd><span class="saas-status-badge {{ $cashAccount->status ? 'is-active' : 'is-inactive' }}">{{ $cashAccount->status ? 'Active' : 'Inactive' }}</span></dd>
    </div>
    <div>
        <dt>Description</dt>
        <dd>{{ $cashAccount->description ?: '—' }}</dd>
    </div>
    <div>
        <dt>Créé le</dt>
        <dd>{{ $cashAccount->created_at->format('d-m-Y H:i:s') }}</dd>
    </div>
</div>
