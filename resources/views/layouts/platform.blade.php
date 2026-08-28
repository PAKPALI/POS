<!doctype html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration SaaS') — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/app.min.css') }}" rel="stylesheet">
    <style>
        body { background: #080d18; }
        .platform-shell { min-height: 100vh; }
        .platform-sidebar { width: 260px; background: linear-gradient(180deg, #111b31, #0b1221); border-right: 1px solid rgba(255,255,255,.08); position: fixed; inset: 0 auto 0 0; padding: 24px 18px; z-index: 10; }
        .platform-brand { color: #fff; font-size: 1.05rem; font-weight: 700; text-decoration: none; display: flex; gap: 10px; align-items: center; }
        .platform-brand i { color: #ff9f43; }
        .platform-nav { margin-top: 34px; }
        .platform-nav a { display: flex; gap: 10px; align-items: center; color: #b8c2d8; text-decoration: none; padding: 11px 13px; border-radius: 10px; margin-bottom: 6px; }
        .platform-nav a.active, .platform-nav a:hover { color: #fff; background: rgba(255,159,67,.14); }
        .platform-main { margin-left: 260px; padding: 28px; }
        .platform-topbar { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 28px; }
        .platform-card { background: #111827; border: 1px solid rgba(255,255,255,.08); border-radius: 14px; }
        .metric-value { font-size: 1.75rem; font-weight: 750; color: #fff; }
        .metric-label { color: #93a4bf; }
        @media(max-width: 800px) {
            .platform-sidebar { position: static; width: 100%; min-height: auto; }
            .platform-nav { display: flex; margin-top: 18px; overflow-x: auto; }
            .platform-nav a { white-space: nowrap; }
            .platform-main { margin-left: 0; padding: 18px; }
        }
    </style>
</head>
<body>
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
<script src="{{ asset('hub/assets/js/app.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
@stack('scripts')
</body>
</html>
