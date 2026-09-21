<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#F3F6FA">
    @include('partials.brand-head')
    <title>@yield('title', 'Espace partenaire') — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/saas-shell.css') }}?v=20260916-1" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260915-1" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/navigation-loader.css') }}?v=20260916-3" rel="stylesheet">
    @stack('styles')
</head>
<body class="saas-body partner-body @yield('body-class')">
@php($partner = auth('partner')->user())
<div class="saas-shell" id="saasShell">
    <aside class="saas-sidebar" id="saasSidebar" aria-label="Navigation partenaire">
        <div class="saas-sidebar-head">
            <a class="saas-brand" href="{{ route('partner.dashboard') }}"><span class="saas-brand-mark"><img src="{{ asset('brand/maxanou-symbol.svg') }}" alt="" width="32" height="32"></span><span class="saas-brand-copy"><strong>{{ config('app.name') }}</strong><small>Partenaires</small></span></a>
            <button type="button" class="saas-icon-button saas-collapse-button" data-saas-sidebar-collapse aria-label="Réduire le menu"><i class="bi bi-layout-sidebar-inset"></i></button>
        </div>
        <nav class="saas-nav">
            <div class="saas-nav-section"><span>Espace partenaire</span></div>
            <a class="saas-nav-link {{ request()->routeIs('partner.dashboard') ? 'is-active' : '' }}" href="{{ route('partner.dashboard') }}"><i class="bi bi-grid-1x2"></i><span>Tableau de bord</span></a>
            <a class="saas-nav-link {{ request()->routeIs('partner.profile') ? 'is-active' : '' }}" href="{{ route('partner.profile') }}"><i class="bi bi-person-circle"></i><span>Mon profil</span></a>
            <div class="saas-nav-section"><span>Acquisition</span></div>
            <a class="saas-nav-link {{ request()->routeIs('partner.code*') ? 'is-active' : '' }}" href="{{ route('partner.code') }}"><i class="bi bi-ticket-perforated"></i><span>Mon code partenaire</span></a>
            <a class="saas-nav-link {{ request()->routeIs('partner.clients') ? 'is-active' : '' }}" href="{{ route('partner.clients') }}"><i class="bi bi-people"></i><span>Mes clients</span></a>
            <a class="saas-nav-link {{ request()->routeIs('partner.commissions*') ? 'is-active' : '' }}" href="{{ route('partner.commissions') }}"><i class="bi bi-wallet2"></i><span>Mes commissions</span></a>
            <a class="saas-nav-link {{ request()->routeIs('partner.withdrawals*') ? 'is-active' : '' }}" href="{{ route('partner.withdrawals') }}"><i class="bi bi-send-check"></i><span>Mes retraits</span></a>
            <div class="saas-nav-section"><span>Aide</span></div>
            <a class="saas-nav-link {{ request()->routeIs('partner.guide') ? 'is-active' : '' }}" href="{{ route('partner.guide') }}"><i class="bi bi-journal-richtext"></i><span>Guide partenaire</span></a>
        </nav>
        <div class="saas-sidebar-foot"><div class="saas-user-compact"><span class="saas-avatar">{{ strtoupper(substr($partner?->name ?? 'P', 0, 1)) }}</span><span><strong>{{ $partner?->name }}</strong><small>Compte partenaire</small></span></div></div>
    </aside>
    <button class="saas-sidebar-backdrop" type="button" data-saas-sidebar-close aria-label="Fermer le menu"></button>
    <div class="saas-workspace">
        <header class="saas-topbar">
            <div class="saas-topbar-start"><button type="button" class="saas-icon-button saas-mobile-menu" data-saas-sidebar-open aria-label="Ouvrir le menu" aria-controls="saasSidebar"><i class="bi bi-list"></i></button><div class="saas-page-context"><span>Partenaires Maxanou</span><strong>@yield('page-title', 'Espace partenaire')</strong></div></div>
            <div class="saas-topbar-actions"><button type="button" class="saas-appearance-trigger" data-bs-toggle="modal" data-bs-target="#navbarAppearanceModal" aria-label="Personnaliser l’apparence"><span class="saas-appearance-trigger-icon"><i class="bi bi-palette"></i></span><span class="saas-appearance-trigger-copy"><small>Affichage</small><strong>Apparence</strong></span><span class="saas-appearance-trigger-color" aria-hidden="true"></span></button><span class="saas-company-switch" aria-label="Portail Partenaires Maxanou"><span class="saas-company-icon"><i class="bi bi-people"></i></span><span><small>Portail</small><strong>Partenaires</strong></span></span><details class="saas-profile-menu"><summary aria-label="Menu du profil"><span class="saas-avatar">{{ strtoupper(substr($partner?->name ?? 'P', 0, 1)) }}</span><span class="saas-profile-copy"><strong>{{ $partner?->name }}</strong><small>Partenaire</small></span><i class="bi bi-chevron-down"></i></summary><div class="saas-profile-dropdown"><a href="{{ route('partner.profile') }}"><i class="bi bi-person"></i>Mon profil</a><a href="{{ route('partner.profile') }}#appearance"><i class="bi bi-palette"></i>Apparence</a><form method="POST" action="{{ route('partner.logout') }}">@csrf<button type="submit" data-loading-text="Déconnexion…"><i class="bi bi-box-arrow-right"></i>Se déconnecter</button></form></div></details></div>
        </header>
        <main class="saas-content" id="mainContent" tabindex="-1">
            @if(session('success'))<div class="saas-alert saas-alert-success" role="status"><i class="bi bi-check-circle"></i><span>{{ session('success') }}</span></div>@endif
            @if(session('status'))<div class="saas-alert saas-alert-info" role="status"><i class="bi bi-info-circle"></i><span>{{ session('status') }}</span></div>@endif
            @if($errors->any())<div class="saas-alert saas-alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i><span>{{ $errors->first() }}</span></div>@endif
            @yield('content')
        </main>
    </div>
</div>
@php($navbarMode = in_array($partner?->appearance_mode, ['system', 'dark', 'light'], true) ? $partner->appearance_mode : 'light')
@php($navbarAccent = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $partner?->accent_color) ? strtoupper($partner->accent_color) : '#3B82F6')
<x-ui.modal id="navbarAppearanceModal" title="Personnaliser l’interface" eyebrow="Préférences personnelles" size="md"><form id="navbarAppearanceForm" action="{{ route('partner.profile.appearance.update') }}" method="POST">@csrf @method('PUT')<div class="navbar-appearance-section"><div class="navbar-appearance-heading"><div><strong>Mode d’affichage</strong><small>Choisissez le confort adapté à votre environnement.</small></div></div><div class="navbar-mode-grid">@foreach(['system'=>['Selon l’appareil','bi-circle-half'],'dark'=>['Sombre','bi-moon-stars'],'light'=>['Clair','bi-sun']] as $value=>[$label,$icon])<label class="navbar-mode-choice {{ $navbarMode === $value ? 'is-selected' : '' }}"><input class="visually-hidden" type="radio" name="appearance_mode" value="{{ $value }}" @checked($navbarMode === $value)><i class="bi {{ $icon }}"></i><span>{{ $label }}</span><i class="bi bi-check-circle-fill navbar-mode-check"></i></label>@endforeach</div></div><div class="navbar-appearance-section"><div class="navbar-appearance-heading"><div><strong>Couleur dominante</strong><small>Elle s’applique aux actions et repères importants.</small></div><output id="navbarAccentValue">{{ $navbarAccent }}</output></div><div class="navbar-accent-grid" id="navbarAccentSwatches">@foreach(['#3B82F6','#20BFA9','#FF9F43','#7C5CFC','#EC4899','#84B547'] as $color)<button type="button" class="navbar-accent-swatch {{ $navbarAccent === $color ? 'is-selected' : '' }}" style="--swatch:{{ $color }}" data-accent="{{ $color }}" aria-label="Choisir la couleur {{ $color }}"></button>@endforeach<label class="navbar-custom-color"><i class="bi bi-eyedropper"></i><input type="color" id="navbarAccentPicker" value="{{ $navbarAccent }}" aria-label="Couleur personnalisée"></label></div><input type="hidden" id="navbarAccentInput" name="accent_color" value="{{ $navbarAccent }}"></div><div id="navbarAppearanceFeedback" class="navbar-appearance-feedback" role="status" aria-live="polite"></div><div class="navbar-appearance-actions"><a href="{{ route('partner.profile') }}#appearance" class="saas-btn saas-btn-secondary"><i class="bi bi-sliders"></i> Réglages complets</a><button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…"><i class="bi bi-check2"></i> Enregistrer</button></div></form></x-ui.modal>
<script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
<script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260916-1"></script>
<script src="{{ asset('hub/assets/js/saas-shell.js') }}?v=20260901-3"></script>
<script src="{{ asset('hub/assets/js/navigation-loader.js') }}?v=20260902-2"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="{{ asset('pwa-register.js') }}" defer></script>
@stack('scripts')
</body>
</html>
