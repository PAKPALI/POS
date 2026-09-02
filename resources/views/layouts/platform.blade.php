<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration SaaS') — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/platform.css') }}?v=20260902-2" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/platform-components.css') }}?v=20260902-1" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/navigation-loader.css') }}?v=20260902-1" rel="stylesheet">
</head>
<body>
@include('partials.navigation-loader')
<div class="platform-shell">
    <aside class="platform-sidebar">
        <a class="platform-brand" href="{{ route('platform.dashboard') }}">@if(config('platform.identity.logo_url'))<img src="{{ config('platform.identity.logo_url') }}" alt="Logo" width="32" height="32" style="object-fit:contain;border-radius:8px">@else<i class="bi bi-shield-lock-fill"></i>@endif Administration SaaS</a>
        <nav class="platform-nav">
            @if(auth('platform')->user()->hasPlatformPermission('platform.dashboard.view'))<a class="{{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Vue générale</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.companies.view'))<a class="{{ request()->routeIs('platform.companies.*') ? 'active' : '' }}" href="{{ route('platform.companies.index') }}"><i class="bi bi-buildings"></i> Entreprises</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.users.view'))<a class="{{ request()->routeIs('platform.users.*') ? 'active' : '' }}" href="{{ route('platform.users.index') }}"><i class="bi bi-people"></i> Utilisateurs</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.payments.view'))<a class="{{ request()->routeIs('platform.payments.*') ? 'active' : '' }}" href="{{ route('platform.payments.index') }}"><i class="bi bi-credit-card"></i> Paiements & quotas</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.pricing.manage'))<a class="{{ request()->routeIs('platform.settings.*') ? 'active' : '' }}" href="{{ route('platform.settings.general') }}"><i class="bi bi-gear"></i> Paramètres</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.audit.view'))<a class="{{ request()->routeIs('platform.audit.*') ? 'active' : '' }}" href="{{ route('platform.audit.index') }}"><i class="bi bi-journal-check"></i> Journal d’audit</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.health.view'))<a class="{{ request()->routeIs('platform.health.*') ? 'active' : '' }}" href="{{ route('platform.health.index') }}"><i class="bi bi-heart-pulse"></i> Santé du système</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.communications.view'))<a class="{{ request()->routeIs('platform.communications.*') ? 'active' : '' }}" href="{{ route('platform.communications.index') }}"><i class="bi bi-chat-square-dots-fill"></i> Communications</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.health.view'))<a class="{{ request()->routeIs('platform.alerts.*') ? 'active' : '' }}" href="{{ route('platform.alerts.index') }}"><i class="bi bi-bell-fill"></i> Alertes</a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.admins.manage'))<a class="{{ request()->routeIs('platform.admins.*') ? 'active' : '' }}" href="{{ route('platform.admins.index') }}"><i class="bi bi-person-gear"></i> Administrateurs</a>@endif
        </nav>
    </aside>
    <main class="platform-main">
        <header class="platform-topbar">
            <div><div class="text-secondary small">Console plateforme</div><h1 class="h3 mb-0">@yield('page-title', 'Vue générale')</h1></div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block"><div class="fw-semibold">{{ auth('platform')->user()->name }}</div><small class="text-secondary">{{ auth('platform')->user()->roleLabel() }}</small></div>
                <form method="POST" action="{{ route('platform.logout') }}">@csrf<button class="btn btn-outline-danger" data-loading-text="Déconnexion…"><i class="bi bi-box-arrow-right me-1"></i> Déconnexion</button></form>
            </div>
        </header>
        @if(session('success'))<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>@endif
        @if(session('info'))<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ session('info') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
<script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260902-6"></script>
<script src="{{ asset('hub/assets/js/navigation-loader.js') }}?v=20260902-2"></script>
@stack('scripts')
</body>
</html>
