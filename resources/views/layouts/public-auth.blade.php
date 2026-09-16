<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F3F6FA">
    @include('partials.brand-head')
    <title>@yield('title', 'Accès sécurisé') — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-18" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/public-auth.css') }}?v=20260916-3" rel="stylesheet">
    @stack('styles')
</head>
<body class="saas-body public-auth-body">
<div class="public-auth-orbs" aria-hidden="true"><span class="public-auth-orb public-auth-orb-a"></span><span class="public-auth-orb public-auth-orb-b"></span><span class="public-auth-orb public-auth-orb-c"></span></div>
<main class="public-auth-shell">
    <header class="public-auth-header">
        <a class="public-auth-brand" href="{{ url('/') }}"><span class="public-auth-brand-mark"><img src="{{ asset('brand/maxanou-symbol.svg') }}" alt="" width="30" height="30"></span><span>{{ config('app.name') }}</span></a>
        <div class="public-appearance-controls">
            <div class="public-theme-picker" role="group" aria-label="Apparence">
                <button type="button" data-public-theme="light" aria-label="Thème clair"><i class="bi bi-sun" aria-hidden="true"></i></button>
                <button type="button" data-public-theme="system" aria-label="Selon l’appareil"><i class="bi bi-circle-half" aria-hidden="true"></i></button>
                <button type="button" data-public-theme="dark" aria-label="Thème sombre"><i class="bi bi-moon-stars" aria-hidden="true"></i></button>
            </div>
            <details class="public-accent-picker">
                <summary aria-label="Choisir votre couleur d’accent"><i class="bi bi-palette2" aria-hidden="true"></i><span>Couleur</span></summary>
                <div class="public-accent-menu">
                    <p>Votre couleur</p>
                    <div class="public-accent-swatches" role="group" aria-label="Couleurs proposées">
                        @foreach (['#3B82F6' => 'Bleu', '#20BFA9' => 'Turquoise', '#FF9F43' => 'Orange', '#7C5CFC' => 'Violet', '#EC4899' => 'Rose', '#84B547' => 'Vert'] as $color => $name)
                            <button type="button" class="public-accent-swatch" data-public-accent="{{ $color }}" style="--public-accent-preview: {{ $color }}" aria-label="{{ $name }}"></button>
                        @endforeach
                    </div>
                    <label class="public-custom-accent" for="publicCustomAccent"><span>Personnaliser</span><input id="publicCustomAccent" type="color" value="#3B82F6" aria-label="Couleur personnalisée"></label>
                </div>
            </details>
        </div>
    </header>
    <section class="public-auth-panel">@yield('content')</section>
</main>
<script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
<script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260916-1"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="{{ asset('pwa-register.js') }}" defer></script>
<script>
(() => {
    const modeKey = 'maxanou_public_auth_appearance_v2';
    const accentKey = 'maxanou_public_auth_accent_v2';
    const allowedAccent = /^#[0-9A-Fa-f]{6}$/;
    let mode = localStorage.getItem(modeKey) || 'light';
    let accent = (localStorage.getItem(accentKey) || '#3B82F6').toUpperCase();
    if (!allowedAccent.test(accent)) accent = '#3B82F6';
    const themeButtons = document.querySelectorAll('[data-public-theme]');
    const accentButtons = document.querySelectorAll('[data-public-accent]');
    const customAccent = document.getElementById('publicCustomAccent');
    const syncRegistrationAppearance = () => document.querySelectorAll('[data-appearance-mode]').forEach(input => { input.value = mode; });
    const syncRegistrationAccent = () => document.querySelectorAll('[data-accent-color]').forEach(input => { input.value = accent; });
    const apply = () => {
        window.DesignSystem.apply({ mode, accent });
        localStorage.setItem(modeKey, mode);
        localStorage.setItem(accentKey, accent);
        themeButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.publicTheme === mode)));
        accentButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.publicAccent === accent)));
        if (customAccent) customAccent.value = accent;
        syncRegistrationAppearance();
        syncRegistrationAccent();
    };
    apply();
    themeButtons.forEach(button => button.addEventListener('click', () => { mode = button.dataset.publicTheme; apply(); }));
    accentButtons.forEach(button => button.addEventListener('click', () => { accent = button.dataset.publicAccent; apply(); }));
    customAccent?.addEventListener('input', event => { accent = event.target.value.toUpperCase(); apply(); });
    if (window.jQuery && jQuery.fn.select2) jQuery('select.country-select').select2({ width: '100%', placeholder: 'Rechercher un pays', minimumResultsForSearch: 0 });
})();
</script>
@stack('scripts')
</body>
</html>
