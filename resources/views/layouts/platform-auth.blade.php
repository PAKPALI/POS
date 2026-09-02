<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#070B14">
    <title>@yield('title', 'Accès plateforme') — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-18" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/platform-auth.css') }}?v=20260902-2" rel="stylesheet">
</head>
<body class="platform-auth-body">
<main class="platform-auth-shell">
    <a class="platform-auth-brand" href="{{ route('user_login') }}"><i class="bi bi-boxes" aria-hidden="true"></i><span>{{ config('app.name') }}</span></a>
    <section class="platform-auth-card">
        <div class="platform-auth-mark">@if(config('platform.identity.logo_url'))<img src="{{ config('platform.identity.logo_url') }}" alt="Logo de la plateforme" width="48" height="48">@else<i class="bi bi-shield-lock-fill" aria-hidden="true"></i>@endif</div>
        @yield('content')
    </section>
</main>
<script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
<script src="{{ asset('hub/assets/js/design-system.js') }}?v=20260902-6"></script>
@stack('scripts')
</body>
</html>
