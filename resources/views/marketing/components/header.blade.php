<header class="marketing-header" data-marketing-header>
    <div class="marketing-container marketing-header-inner">
        <a class="marketing-brand" href="{{ route('marketing.home') }}" aria-label="POS SaaS Afrique, accueil">
            <span class="marketing-brand-mark">P</span>
            <span><strong>POS</strong><small>SaaS Afrique</small></span>
        </a>
        <div class="marketing-appearance" data-marketing-appearance @auth data-save-url="{{ route('profile.appearance.update') }}" @endauth>
            <button class="marketing-appearance-trigger" type="button" aria-expanded="false" aria-controls="marketing-appearance-panel">
                @include('marketing.components.icon', ['name' => 'palette'])
                <span>Apparence</span>
            </button>
            <div class="marketing-appearance-panel" id="marketing-appearance-panel" role="dialog" aria-label="Personnaliser l’apparence" hidden>
                <div class="marketing-appearance-heading"><strong>Personnaliser le site</strong><small>Choisissez votre confort de lecture.</small></div>
                <div class="marketing-appearance-group">
                    <span class="marketing-appearance-label">Mode</span>
                    <div class="marketing-appearance-modes" role="group" aria-label="Mode d’affichage">
                        <button type="button" data-marketing-mode="dark" aria-pressed="false">Sombre</button>
                        <button type="button" data-marketing-mode="light" aria-pressed="false">Clair</button>
                    </div>
                </div>
                <div class="marketing-appearance-group">
                    <span class="marketing-appearance-label">Couleur primaire</span>
                    <div class="marketing-appearance-colors" role="group" aria-label="Couleur primaire">
                        @foreach (['#3B82F6' => 'Bleu', '#20BFA9' => 'Turquoise', '#7C5CFC' => 'Violet', '#EC4899' => 'Rose', '#84B547' => 'Vert', '#FF9F43' => 'Orange'] as $color => $name)
                            <button type="button" class="marketing-appearance-swatch" data-marketing-accent="{{ $color }}" style="--marketing-swatch: {{ $color }}" aria-label="{{ $name }}" aria-pressed="false"><span aria-hidden="true"></span></button>
                        @endforeach
                    </div>
                    <label class="marketing-appearance-custom">Personnaliser <input type="color" data-marketing-accent-custom value="#3B82F6" aria-label="Choisir une couleur primaire personnalisée"></label>
                </div>
                <small class="marketing-appearance-status" data-marketing-appearance-status role="status">Votre préférence est mémorisée sur cet appareil.</small>
            </div>
        </div>
        <button class="marketing-menu-toggle" type="button" aria-expanded="false" aria-controls="marketing-nav">
            @include('marketing.components.icon', ['name' => 'menu'])
            <span>Menu</span>
        </button>
        <nav class="marketing-nav" id="marketing-nav" aria-label="Navigation principale">
            <a href="{{ route('marketing.features') }}">Fonctionnalités</a>
            <a href="{{ route('marketing.sectors') }}">Solutions</a>
            <a href="{{ route('marketing.pricing') }}">Tarifs</a>
            <a href="{{ route('marketing.security') }}">Sécurité</a>
            <a href="{{ route('marketing.help') }}">Aide</a>
            <div class="marketing-nav-actions">
                <a class="marketing-button marketing-button-ghost" href="{{ route('marketing.login') }}">Se connecter</a>
                <a class="marketing-button marketing-button-primary" data-event="hero_try" href="{{ route('marketing.register') }}">Essayer gratuitement</a>
            </div>
        </nav>
    </div>
</header>
