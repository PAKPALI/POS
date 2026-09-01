<header class="saas-topbar">
    <div class="saas-topbar-start">
        <button type="button" class="saas-icon-button saas-mobile-menu" data-saas-sidebar-open aria-label="Ouvrir le menu" aria-controls="saasSidebar"><i class="bi bi-list"></i></button>
        <div class="saas-page-context"><span>@yield('eyebrow', 'Espace de travail')</span><strong>@hasSection('page-title')@yield('page-title')@elseif(trim($__env->yieldContent('title'))){{ trim($__env->yieldContent('title')) }}@else Espace de travail @endif</strong></div>
    </div>
    <div class="saas-topbar-actions">
        <button type="button" class="saas-appearance-trigger" data-bs-toggle="modal" data-bs-target="#navbarAppearanceModal" aria-label="Personnaliser l’apparence">
            <span class="saas-appearance-trigger-icon"><i class="bi bi-palette"></i></span>
            <span class="saas-appearance-trigger-copy"><small>Affichage</small><strong>Apparence</strong></span>
            <span class="saas-appearance-trigger-color" aria-hidden="true"></span>
        </button>
        @isset($activeCompany)
            <a class="saas-company-switch" href="{{ route('companies.select') }}" title="Changer d’entreprise">
                <span class="saas-company-icon"><i class="bi bi-buildings"></i></span>
                <span><small>Entreprise active</small><strong>{{ $activeCompany->name }}</strong></span>
                <i class="bi bi-chevron-expand"></i>
            </a>
        @endisset
        <details class="saas-profile-menu">
            <summary aria-label="Menu du profil"><span class="saas-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="saas-profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ $currentMembership?->role?->name ?? 'Membre' }}</small></span><i class="bi bi-chevron-down"></i></summary>
            <div class="saas-profile-dropdown">
                <a href="{{ route('profil') }}"><i class="bi bi-person"></i>Mon profil</a>
                <a href="{{ route('profil') }}#pills-appearance"><i class="bi bi-palette"></i>Apparence</a>
                <a href="{{ route('companies.select') }}"><i class="bi bi-buildings"></i>Changer d’entreprise</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" data-loading-text="Déconnexion…"><i class="bi bi-box-arrow-right"></i>Se déconnecter</button></form>
            </div>
        </details>
    </div>
</header>

@php
    $navbarAppearanceMode = in_array(auth()->user()->appearance_mode, ['system', 'dark', 'light'], true)
        ? auth()->user()->appearance_mode
        : 'system';
    $navbarAccent = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) auth()->user()->accent_color)
        ? strtoupper(auth()->user()->accent_color)
        : '#FF9F43';
@endphp

<x-ui.modal id="navbarAppearanceModal" title="Personnaliser l’interface" eyebrow="Préférences personnelles" size="md">
    <form id="navbarAppearanceForm" action="{{ route('profile.appearance.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="navbar-appearance-section">
            <div class="navbar-appearance-heading">
                <div><strong>Mode d’affichage</strong><small>Choisissez le confort adapté à votre environnement.</small></div>
            </div>
            <div class="navbar-mode-grid">
                @foreach([
                    'system' => ['Selon l’appareil', 'bi-circle-half'],
                    'dark' => ['Sombre', 'bi-moon-stars'],
                    'light' => ['Clair', 'bi-sun'],
                ] as $value => [$label, $icon])
                    <label class="navbar-mode-choice {{ $navbarAppearanceMode === $value ? 'is-selected' : '' }}">
                        <input class="visually-hidden" type="radio" name="appearance_mode" value="{{ $value }}" @checked($navbarAppearanceMode === $value)>
                        <i class="bi {{ $icon }}" aria-hidden="true"></i>
                        <span>{{ $label }}</span>
                        <i class="bi bi-check-circle-fill navbar-mode-check" aria-hidden="true"></i>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="navbar-appearance-section">
            <div class="navbar-appearance-heading">
                <div><strong>Couleur dominante</strong><small>Elle s’applique aux actions, liens actifs et repères importants.</small></div>
                <output id="navbarAccentValue">{{ $navbarAccent }}</output>
            </div>
            <div class="navbar-accent-grid" id="navbarAccentSwatches">
                @foreach(['#FF9F43', '#20BFA9', '#3B82F6', '#7C5CFC', '#EC4899', '#84B547'] as $color)
                    <button type="button" class="navbar-accent-swatch {{ $navbarAccent === $color ? 'is-selected' : '' }}" style="--swatch:{{ $color }}" data-accent="{{ $color }}" aria-label="Choisir la couleur {{ $color }}" aria-pressed="{{ $navbarAccent === $color ? 'true' : 'false' }}"></button>
                @endforeach
                <label class="navbar-custom-color" title="Choisir une couleur personnalisée">
                    <i class="bi bi-eyedropper" aria-hidden="true"></i>
                    <input type="color" id="navbarAccentPicker" value="{{ $navbarAccent }}" aria-label="Couleur personnalisée">
                </label>
            </div>
            <input type="hidden" id="navbarAccentInput" name="accent_color" value="{{ $navbarAccent }}">
        </div>

        <div id="navbarAppearanceFeedback" class="navbar-appearance-feedback" role="status" aria-live="polite"></div>
        <div class="navbar-appearance-actions">
            <a href="{{ route('profil') }}#pills-appearance" class="saas-btn saas-btn-secondary"><i class="bi bi-sliders"></i> Réglages complets</a>
            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…"><i class="bi bi-check2"></i> Enregistrer</button>
        </div>
    </form>
</x-ui.modal>
