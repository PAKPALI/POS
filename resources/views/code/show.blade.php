<section class="saas-detail-hero">
    <div class="saas-detail-media"><img src="{{ asset('storage/'.$CodePromo->qr_code) }}" alt="Code QR du code promotionnel {{ $CodePromo->code }}" width="180" height="180"></div>
    <dl class="saas-detail-list">
        <div><dt>Nom</dt><dd>{{ $CodePromo->name }}</dd></div>
        <div><dt>Code</dt><dd><code>{{ $CodePromo->code }}</code></dd></div>
        <div><dt>Pourcentage</dt><dd>{{ $CodePromo->percents }} %</dd></div>
        <div><dt>Description</dt><dd>{{ $CodePromo->comments ?: 'Aucune description' }}</dd></div>
        <div><dt>Créé par</dt><dd>{{ $CodePromo->user->name }}</dd></div>
    </dl>
</section>
