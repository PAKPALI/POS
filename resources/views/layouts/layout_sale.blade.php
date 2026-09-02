<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Point de Vente — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Point de vente — {{ config('app.name') }}">
    <meta name="author" content="{{ config('app.name') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#111111">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon-180.png">

    {{-- Feuilles de style --}}
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/app.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/saas-pos.css') }}?v=20260831-3" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/navigation-loader.css') }}?v=20260902-1" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    {{-- jQuery DOIT être dans le <head> pour être dispo avant les scripts inline du contenu --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @stack('styles')
</head>

<body class="pace-top pos-saas-body">
    @include('partials.navigation-loader')

    <div id="app" class="app app-content-full-height app-without-sidebar app-without-header">
        @yield('content')
    </div>

    {{-- Scripts fondamentaux --}}
    <script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('hub/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
    <script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260902-6"></script>
    <script src="{{ asset('hub/assets/js/navigation-loader.js') }}?v=20260902-2"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script defer src="{{ asset('pwa-register.js') }}"></script>

    @stack('scripts')
</body>
</html>
