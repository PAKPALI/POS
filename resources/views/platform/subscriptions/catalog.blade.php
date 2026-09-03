@extends('layouts.platform')
@section('title', 'Catalogue abonnements')
@section('page-title', 'Catalogue abonnements')
@section('content')
<div class="platform-subscription-page">
    <header class="platform-subscription-hero">
        <div class="platform-subscription-hero-copy">
            <p class="platform-eyebrow"><i class="bi bi-layers" aria-hidden="true"></i> Opérations tarifaires sécurisées</p>
            <h2>Gérer les versions des plans</h2>
            <p>Créez une nouvelle version en brouillon, contrôlez ses valeurs, puis publiez-la avec votre mot de passe. Les plans déjà souscrits restent inchangés.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('platform.subscriptions.preflight') }}"><i class="bi bi-clipboard2-check" aria-hidden="true"></i> Revenir au pré-contrôle</a>
    </header>

    <div class="platform-subscription-notice"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Chaque nouvelle version est indépendante : les snapshots des paiements et des abonnements existants ne sont jamais réécrits.</span></div>

    @foreach($plans as $family => $versions)
        @php($current = $versions->firstWhere('is_active', true))
        @php($source = $current ?? $versions->sortByDesc('version')->first())
        <section class="platform-subscription-section platform-subscription-family">
            <header class="platform-panel-head">
                <div><p class="platform-eyebrow"><i class="bi bi-layers" aria-hidden="true"></i> Famille {{ $family }}</p><h2>{{ $source->name }}</h2><p>{{ $versions->count() }} version(s) conservée(s) dans l’historique.</p></div>
                <span class="platform-status-chip {{ $current ? 'is-success' : 'is-warning' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $current ? 'Publié : v'.$current->version : 'Aucune version publiée' }}</span>
            </header>
            <div class="platform-datatable"><div class="platform-datatable-meta"><span>Versions du plan</span><small>{{ $versions->count() }} entrée(s)</small></div><div class="table-responsive platform-table-scroll">
                <table class="table platform-data-table">
                    <thead><tr><th>Version</th><th>Prix</th><th>Limites</th><th>Fonctionnalités</th><th>État</th><th>Action</th></tr></thead>
                    <tbody>
                    @foreach($versions->sortByDesc('version') as $version)
                        <tr>
                            <td><span class="platform-table-code">v{{ $version->version }}</span><small class="platform-table-subtext">{{ $version->key }}</small></td>
                            <td><strong>{{ number_format($version->monthly_price,0,',',' ') }} XOF</strong><small class="platform-table-subtext">{{ number_format($version->annual_price,0,',',' ') }} XOF / an</small></td>
                            <td><span class="platform-table-stack">{{ $version->company_limit }} entreprise(s) · {{ $version->user_limit }} utilisateur(s)<br>{{ $version->product_limit }} produit(s) · {{ $version->sms_quota }} SMS · {{ $version->whatsapp_quota }} WhatsApp</span></td>
                            <td><div class="platform-table-chips">@foreach($version->features as $feature)<span class="platform-status-chip {{ $feature->enabled ? 'is-success' : 'is-muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $feature->feature_key }}</span>@endforeach</div></td>
                            <td><span class="platform-status-chip {{ $version->is_active ? 'is-success' : 'is-muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $version->is_active ? 'Publié' : 'Brouillon' }}</span></td>
                            <td>@if(!$version->is_active)<button class="btn btn-sm btn-warning platform-table-action" type="button" data-bs-toggle="collapse" data-bs-target="#publish-{{ $version->id }}" aria-expanded="false"><i class="bi bi-upload" aria-hidden="true"></i> Publier</button>@else<span class="platform-table-muted">Version active</span>@endif</td>
                        </tr>
                        @if(!$version->is_active)
                            <tr class="collapse" id="publish-{{ $version->id }}">
                                <td colspan="6"><div class="platform-subscription-inline-form"><div class="platform-subscription-inline-head"><strong>Publier la version v{{ $version->version }}</strong><small>Confirmation sécurisée requise</small></div><form method="POST" action="{{ route('platform.subscriptions.plans.publish', $version) }}" class="platform-settings-form-grid platform-subscription-publish-grid">@csrf<div class="platform-settings-field"><label class="form-label">Motif de publication</label><input name="reason" class="form-control" minlength="5" maxlength="500" required placeholder="Ex. nouvelle grille tarifaire validée"></div><div class="platform-settings-field"><label class="form-label">Mot de passe plateforme</label><input type="password" name="current_password" class="form-control" required></div><div class="platform-subscription-inline-submit"><button class="btn btn-warning" data-loading-text="Publication…"><i class="bi bi-check2-circle" aria-hidden="true"></i> Confirmer la publication</button></div><p class="platform-subscription-inline-note"><i class="bi bi-info-circle" aria-hidden="true"></i> La publication retire uniquement les anciennes versions de cette famille des nouveaux checkouts. Les abonnements existants restent inchangés.</p></form></div></td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div></div>

            @if($source->rank > 0)
                <details class="platform-subscription-create">
                    <summary class="btn btn-outline-secondary"><i class="bi bi-plus-circle" aria-hidden="true"></i> Créer une nouvelle version</summary>
                    <form method="POST" action="{{ route('platform.subscriptions.plans.versions.store', $source) }}" class="platform-subscription-create-form">@csrf
                        <div class="platform-settings-form-grid">
                            <div class="platform-settings-field"><label class="form-label">Nom commercial</label><input name="name" class="form-control" maxlength="80" value="{{ old('name', $source->name) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Prix mensuel <span>(XOF)</span></label><input name="monthly_price" type="number" min="1" max="10000000" class="form-control" value="{{ old('monthly_price', $source->monthly_price) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Prix annuel <span>(11 mois, XOF)</span></label><input name="annual_price" type="number" min="11" max="110000000" class="form-control" value="{{ old('annual_price', $source->annual_price) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Limite entreprises</label><input name="company_limit" type="number" min="1" class="form-control" value="{{ old('company_limit', $source->company_limit) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Limite utilisateurs</label><input name="user_limit" type="number" min="1" class="form-control" value="{{ old('user_limit', $source->user_limit) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Limite produits</label><input name="product_limit" type="number" min="1" class="form-control" value="{{ old('product_limit', $source->product_limit) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Quota SMS mensuel</label><input name="sms_quota" type="number" min="0" class="form-control" value="{{ old('sms_quota', $source->sms_quota) }}" required></div>
                            <div class="platform-settings-field"><label class="form-label">Quota WhatsApp mensuel</label><input name="whatsapp_quota" type="number" min="0" class="form-control" value="{{ old('whatsapp_quota', $source->whatsapp_quota) }}" required></div>
                        </div>
                        <div class="platform-subscription-feature-grid"><label class="platform-settings-toggle-card"><span class="saas-switch-line"><input type="checkbox" name="features[suppliers]" value="1" id="suppliers-{{ $source->id }}" class="saas-switch-input" @checked(old('features.suppliers', $source->features->firstWhere('feature_key','suppliers')?->enabled))><span class="saas-switch-control"></span></span><span><strong>Fonction fournisseurs</strong><small>Activer la gestion des fournisseurs.</small></span></label><label class="platform-settings-toggle-card"><span class="saas-switch-line"><input type="checkbox" name="features[ecommerce]" value="1" id="ecommerce-{{ $source->id }}" class="saas-switch-input" @checked(old('features.ecommerce', $source->features->firstWhere('feature_key','ecommerce')?->enabled))><span class="saas-switch-control"></span></span><span><strong>Fonction e-commerce</strong><small>Activer les ventes en ligne.</small></span></label></div>
                        <div class="platform-settings-form-grid platform-subscription-create-security"><div class="platform-settings-field"><label class="form-label">Motif de création</label><input name="reason" class="form-control" minlength="5" maxlength="500" required></div><div class="platform-settings-field"><label class="form-label">Mot de passe plateforme</label><input name="current_password" type="password" class="form-control" required></div></div>
                        <div class="platform-subscription-create-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Le plan sera créé en brouillon. Le serveur refuse tout tarif annuel différent de 11 mensualités.</div>
                        <div class="platform-settings-action"><button class="btn btn-warning" data-loading-text="Création…"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i> Créer le brouillon v{{ $source->version + 1 }}</button></div>
                    </form>
                </details>
            @endif
        </section>
    @endforeach
</div>
@endsection
