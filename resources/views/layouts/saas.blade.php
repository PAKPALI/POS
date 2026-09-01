<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#070B14">
    <meta name="description" content="{{ config('app.name') }} — gestion commerciale">
    <title>@yield('title', 'Espace de travail') — {{ config('app.name') }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon-180.png">
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/saas-shell.css') }}?v=20260901-9" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('styles')
</head>
<body class="saas-body @yield('body-class')">
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
    <script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260901-3"></script>
    <script src="{{ asset('hub/assets/js/saas-shell.js') }}?v=20260901-3"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="{{ asset('pwa-register.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
