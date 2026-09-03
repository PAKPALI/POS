@php($platformAdmin = auth('platform')->user())
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
    <link href="{{ asset('hub/assets/css/platform.css') }}?v=20260903-22" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/platform-components.css') }}?v=20260903-10" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/navigation-loader.css') }}?v=20260902-1" rel="stylesheet">
    @stack('styles')
</head>
<body class="saas-body platform-body">
@include('partials.navigation-loader')
<div class="platform-shell">
    <button type="button" class="platform-sidebar-backdrop" data-platform-sidebar-close aria-label="Fermer le menu"></button>
    <aside class="platform-sidebar" id="platformSidebar" aria-label="Navigation Administration">
        <div class="platform-sidebar-head">
            <a class="platform-brand" href="{{ route('platform.dashboard') }}">
                <span class="platform-brand-mark">
                    @if(config('platform.identity.logo_url'))
                        <img src="{{ config('platform.identity.logo_url') }}" alt="Logo" width="38" height="38">
                    @else
                        <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                    @endif
                </span>
                <span class="platform-brand-copy"><strong>{{ config('app.name') }}</strong><small>Console plateforme</small></span>
            </a>
            <button type="button" class="platform-icon-button platform-sidebar-close" data-platform-sidebar-close aria-label="Fermer le menu"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>

        <div class="platform-console-note"><i class="bi bi-stars" aria-hidden="true"></i><span><strong>Centre de pilotage</strong><small>Supervision de votre SaaS</small></span></div>

        <nav class="platform-nav">
            @if(auth('platform')->user()->hasPlatformPermission('platform.dashboard.view'))
                <div class="platform-nav-section">Pilotage</div>
                <a class="platform-nav-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}" @if(request()->routeIs('platform.dashboard')) aria-current="page" @endif><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span>Vue générale</span></a>
            @endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.companies.view'))<a class="platform-nav-link {{ request()->routeIs('platform.companies.*') ? 'active' : '' }}" href="{{ route('platform.companies.index') }}" @if(request()->routeIs('platform.companies.*')) aria-current="page" @endif><i class="bi bi-buildings" aria-hidden="true"></i><span>Entreprises</span></a>@endif
            @if(auth('platform')->user()->hasPlatformPermission('platform.users.view'))<a class="platform-nav-link {{ request()->routeIs('platform.users.*') ? 'active' : '' }}" href="{{ route('platform.users.index') }}" @if(request()->routeIs('platform.users.*')) aria-current="page" @endif><i class="bi bi-people" aria-hidden="true"></i><span>Utilisateurs</span></a>@endif

            @if(auth('platform')->user()->hasPlatformPermission('platform.payments.view') || auth('platform')->user()->hasPlatformPermission('platform.pricing.manage'))
                <div class="platform-nav-section">Monétisation</div>
                @if(auth('platform')->user()->hasPlatformPermission('platform.payments.view'))<a class="platform-nav-link {{ request()->routeIs('platform.payments.*') ? 'active' : '' }}" href="{{ route('platform.payments.index') }}" @if(request()->routeIs('platform.payments.*')) aria-current="page" @endif><i class="bi bi-credit-card" aria-hidden="true"></i><span>Paiements & quotas</span></a>@endif
                @if(auth('platform')->user()->hasPlatformPermission('platform.pricing.manage'))<a class="platform-nav-link {{ request()->routeIs('platform.settings.*') ? 'active' : '' }}" href="{{ route('platform.settings.general') }}" @if(request()->routeIs('platform.settings.*')) aria-current="page" @endif><i class="bi bi-sliders2" aria-hidden="true"></i><span>Paramètres</span></a>@endif
                @if(auth('platform')->user()->hasPlatformPermission('platform.admins.manage'))<a class="platform-nav-link {{ request()->routeIs('platform.subscriptions.*') ? 'active' : '' }}" href="{{ route('platform.subscriptions.preflight') }}" @if(request()->routeIs('platform.subscriptions.*')) aria-current="page" @endif><i class="bi bi-shield-check" aria-hidden="true"></i><span>Abonnements</span></a>@endif
            @endif

            @if(auth('platform')->user()->hasPlatformPermission('platform.audit.view') || auth('platform')->user()->hasPlatformPermission('platform.health.view') || auth('platform')->user()->hasPlatformPermission('platform.communications.view'))
                <div class="platform-nav-section">Surveillance</div>
                @if(auth('platform')->user()->hasPlatformPermission('platform.audit.view'))<a class="platform-nav-link {{ request()->routeIs('platform.audit.*') ? 'active' : '' }}" href="{{ route('platform.audit.index') }}" @if(request()->routeIs('platform.audit.*')) aria-current="page" @endif><i class="bi bi-journal-check" aria-hidden="true"></i><span>Journal d’audit</span></a>@endif
                @if(auth('platform')->user()->hasPlatformPermission('platform.health.view'))<a class="platform-nav-link {{ request()->routeIs('platform.health.*') ? 'active' : '' }}" href="{{ route('platform.health.index') }}" @if(request()->routeIs('platform.health.*')) aria-current="page" @endif><i class="bi bi-heart-pulse" aria-hidden="true"></i><span>Santé du système</span></a>@endif
                @if(auth('platform')->user()->hasPlatformPermission('platform.health.view'))<a class="platform-nav-link {{ request()->routeIs('platform.alerts.*') ? 'active' : '' }}" href="{{ route('platform.alerts.index') }}" @if(request()->routeIs('platform.alerts.*')) aria-current="page" @endif><i class="bi bi-bell-fill" aria-hidden="true"></i><span>Alertes</span></a>@endif
                @if(auth('platform')->user()->hasPlatformPermission('platform.communications.view'))<a class="platform-nav-link {{ request()->routeIs('platform.communications.*') ? 'active' : '' }}" href="{{ route('platform.communications.index') }}" @if(request()->routeIs('platform.communications.*')) aria-current="page" @endif><i class="bi bi-chat-square-dots-fill" aria-hidden="true"></i><span>Communications</span></a>@endif
            @endif

            @if(auth('platform')->user()->hasPlatformPermission('platform.admins.manage'))
                <div class="platform-nav-section">Accès</div>
                <a class="platform-nav-link {{ request()->routeIs('platform.admins.*') ? 'active' : '' }}" href="{{ route('platform.admins.index') }}" @if(request()->routeIs('platform.admins.*')) aria-current="page" @endif><i class="bi bi-person-gear" aria-hidden="true"></i><span>Administrateurs</span></a>
            @endif
        </nav>

        <div class="platform-sidebar-foot">
            <div class="platform-admin-card">
                <span class="platform-admin-avatar">{{ strtoupper(substr($platformAdmin->name, 0, 1)) }}</span>
                <span class="platform-admin-copy"><strong>{{ $platformAdmin->name }}</strong><small>{{ $platformAdmin->roleLabel() }}</small></span>
            </div>
            <form method="POST" action="{{ route('platform.logout') }}">
                @csrf
                <button class="platform-logout" data-loading-text="Déconnexion…"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Déconnexion</span></button>
            </form>
        </div>
    </aside>

    <main class="platform-main">
        <header class="platform-topbar">
            <div class="platform-topbar-start">
                <button type="button" class="platform-icon-button platform-mobile-menu" data-platform-sidebar-open aria-label="Ouvrir le menu" aria-controls="platformSidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button>
                <div class="platform-page-context"><span>Administration / Console SaaS</span><h1>@yield('page-title', 'Vue générale')</h1></div>
            </div>
            <div class="platform-topbar-actions"><span class="platform-secure-state"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Session sécurisée</span></span></div>
        </header>
        @if(session('success'))<div class="alert alert-success"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ session('success') }}</div>@endif
        @if(session('info'))<div class="alert alert-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>{{ session('info') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
<script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260902-6"></script>
<script src="{{ asset('hub/assets/js/navigation-loader.js') }}?v=20260902-2"></script>
<script>
    (() => {
        const shell = document.querySelector('.platform-shell');
        const menu = document.querySelector('[data-platform-sidebar-open]');
        const closeButtons = document.querySelectorAll('[data-platform-sidebar-close]');
        if (!shell || !menu) return;
        const close = () => { shell.classList.remove('is-sidebar-open'); menu.setAttribute('aria-expanded', 'false'); };
        menu.addEventListener('click', () => { shell.classList.add('is-sidebar-open'); menu.setAttribute('aria-expanded', 'true'); });
        closeButtons.forEach((button) => button.addEventListener('click', close));
        document.querySelectorAll('.platform-nav-link').forEach((link) => link.addEventListener('click', close));
    })();
</script>
@stack('scripts')
</body>
</html>
