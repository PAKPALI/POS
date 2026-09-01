<dl class="saas-detail-list">
    <div><dt>Nom</dt><dd>{{ $Category->name }}</dd></div>
    <div><dt>Créée par</dt><dd>{{ $Category->user?->name ?? '—' }}</dd></div>
    <div><dt>Créée le</dt><dd>{{ $Category->created_at?->format('d/m/Y à H:i') ?? '—' }}</dd></div>
    <div><dt>Disponibilité</dt><dd><span class="saas-status-badge {{ $Category->status ? 'is-active' : 'is-inactive' }}">{{ $Category->status ? 'Active' : 'Archivée' }}</span></dd></div>
</dl>
