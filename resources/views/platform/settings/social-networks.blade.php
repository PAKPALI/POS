@extends('layouts.platform')
@section('title', 'Réseaux sociaux')
@section('page-title', 'Réseaux sociaux')
@section('content')
<div class="platform-settings-page">
    <nav class="platform-settings-nav" aria-label="Paramètres plateforme">
        <a class="platform-settings-tab" href="{{ route('platform.settings.general') }}"><i class="bi bi-sliders2" aria-hidden="true"></i> Général</a>
        <a class="platform-settings-tab" href="{{ route('platform.settings.edit') }}"><i class="bi bi-tags" aria-hidden="true"></i> Tarifs et coûts</a>
        <a class="platform-settings-tab active" href="{{ route('platform.settings.social-networks.edit') }}" aria-current="page"><i class="bi bi-share" aria-hidden="true"></i> Réseaux sociaux</a>
        @if(auth('platform')->user()->hasPlatformPermission('platform.partners.manage'))<a class="platform-settings-tab" href="{{ route('platform.settings.partners.edit') }}"><i class="bi bi-people" aria-hidden="true"></i> Partenaires</a>@endif
        <a class="platform-settings-tab" href="{{ route('platform.subscriptions.preflight') }}"><i class="bi bi-check2-circle" aria-hidden="true"></i> Pré-contrôle abonnements</a>
    </nav>

    <header class="platform-settings-intro">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-share" aria-hidden="true"></i> Présence publique</p>
            <h2>Réseaux sociaux de Maxanou</h2>
            <p>Ajoutez seulement les liens officiels. Une icône sans lien valide reste invisible sur le site public.</p>
        </div>
        <span class="platform-settings-intro-badge"><i class="bi bi-eye" aria-hidden="true"></i> Visible sur le site</span>
    </header>

    <form method="POST" action="{{ route('platform.settings.social-networks.update') }}">
        @csrf @method('PUT')
        <div class="platform-settings-layout">
            <div class="platform-settings-column">
                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-link-45deg" aria-hidden="true"></i> Liens officiels</p>
                        <h2>Canaux à afficher</h2>
                        <p>Les liens doivent commencer par <code>https://</code>. Laissez un champ vide pour masquer le réseau concerné de la vitrine et de la fenêtre d’invitation.</p>
                    </header>
                    <div class="platform-settings-service-list">
                        @foreach($networks as $network)
                            <div class="platform-settings-service-row">
                                <span class="platform-settings-service-icon"><i class="bi {{ $network['icon'] }}" aria-hidden="true"></i></span>
                                <div class="platform-settings-service-copy"><strong>{{ $network['label'] }}</strong><span>{{ $network['hint'] }}</span></div>
                                <div class="platform-settings-field" style="min-width:min(100%, 360px)">
                                    <label class="visually-hidden" for="social-{{ $network['key'] }}">Lien {{ $network['label'] }}</label>
                                    <input id="social-{{ $network['key'] }}" type="url" name="{{ $network['key'] }}_url" class="form-control" inputmode="url" placeholder="https://…" value="{{ old($network['key'].'_url', $network['url']) }}">
                                    @error($network['key'].'_url')<small class="saas-field-error" role="alert">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
            <div class="platform-settings-column">
                <section class="platform-settings-section platform-settings-action-card">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> Validation</p>
                        <h2>Publier les liens</h2>
                        <p>Les changements sont journalisés. Ils s’appliquent immédiatement au site public après enregistrement.</p>
                    </header>
                    <div class="platform-settings-field"><label class="form-label" for="social-network-reason">Motif de la modification</label><textarea id="social-network-reason" class="form-control" name="reason" minlength="5" maxlength="500" rows="3" required>{{ old('reason') }}</textarea>@error('reason')<small class="saas-field-error" role="alert">{{ $message }}</small>@enderror</div>
                    <div class="platform-settings-field platform-settings-field-spaced"><label class="form-label" for="social-network-password">Mot de passe plateforme</label><input id="social-network-password" class="form-control" name="current_password" type="password" autocomplete="current-password" required>@error('current_password')<small class="saas-field-error" role="alert">{{ $message }}</small>@enderror</div>
                    <button class="btn btn-warning w-100" data-loading-text="Enregistrement…"><i class="bi bi-save2" aria-hidden="true"></i> Enregistrer les réseaux</button>
                </section>
            </div>
        </div>
    </form>
</div>
@endsection
