<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F3F6FA">
    <meta name="description" content="{{ config('app.name') }} — gestion commerciale">
    <title>@yield('title', 'Espace de travail') — {{ config('app.name') }}</title>
    @include('partials.brand-head')
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/saas-shell.css') }}?v=20260916-1" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260909-24" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/navigation-loader.css') }}?v=20260916-3" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('styles')
</head>
<body class="saas-body @yield('body-class')">
    @include('partials.navigation-loader')
    <div class="saas-shell" id="saasShell">
        @include('partials.saas-sidebar')
        <button class="saas-sidebar-backdrop" type="button" data-saas-sidebar-close aria-label="Fermer le menu"></button>

        <div class="saas-workspace">
            @include('partials.saas-topbar')
            <main class="saas-content" id="mainContent" tabindex="-1">
                @if(session('success'))
                    <div class="saas-alert saas-alert-success" role="status"><i class="bi bi-check-circle"></i><span>{{ session('success') }}</span></div>
                @endif
                @if(session('info'))
                    <div class="saas-alert saas-alert-info" role="status"><i class="bi bi-info-circle"></i><span>{{ session('info') }}</span></div>
                @endif
                @if($errors->any())
                    <div class="saas-alert saas-alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i><span>{{ $errors->first() }}</span></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
    <script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260916-1"></script>
    <script src="{{ asset('hub/assets/js/saas-shell.js') }}?v=20260901-3"></script>
    <script src="{{ asset('hub/assets/js/navigation-loader.js') }}?v=20260902-2"></script>
    <script>
        // Responsive DataTables : réserver la flèche aux listes assez riches
        // et conserver l’identifiant, le libellé principal et les actions visibles.
        if (window.jQuery) {
            jQuery(document).on('preInit.dt.saasResponsive', function(event, settings) {
                if (!settings || !settings.oInit || !settings.oInit.responsive) return;
                var headers = settings.nTHead ? settings.nTHead.querySelectorAll('th') : [];
                if (headers.length < 4) return;
                var last = headers.length - 1;
                headers.forEach(function(header, index) {
                    if (header.hasAttribute('data-priority')) return;
                    var priority = index === 0 || index === last ? 1 : (index === 1 || index === last - 1 ? 2 : index + 2);
                    header.setAttribute('data-priority', priority);
                });
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    @php($appSocialNetworks = app(\App\Services\PlatformConfigurationService::class)->socialNetworks())
    @include('partials.social-network-invite', ['socialNetworks' => $appSocialNetworks])
    <script src="{{ asset('pwa-register.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
