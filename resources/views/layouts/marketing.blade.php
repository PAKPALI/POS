<!doctype html>
<html lang="fr">
<head>
    @php($marketingIndexable = config('app.env') === 'production' && config('seo.indexing_enabled', false))
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F3F6FA">
    @include('partials.brand-head')
    <meta name="description" content="@yield('meta-description', 'Maxanou : le POS simple pour vendre, suivre votre stock, piloter votre caisse et envoyer vos reçus par SMS ou WhatsApp.')">
    @if($marketingIndexable)
        <meta name="robots" content="index,follow">
        <link rel="canonical" href="{{ url()->current() }}">
    @else
        <meta name="robots" content="noindex,nofollow,noarchive">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="@yield('title', 'Maxanou — POS de vente et gestion')">
    <meta property="og:description" content="@yield('meta-description', 'Maxanou accompagne les commerces pour vendre, suivre le stock, piloter la caisse et envoyer les reçus par SMS ou WhatsApp.')">
    @if($marketingIndexable)
        <meta property="og:url" content="{{ url()->current() }}">
    @endif
    <meta property="og:image" content="{{ asset('icons/maxanou-social.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Logo Maxanou — vente, stock et gestion">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Maxanou — POS de vente et gestion')">
    <meta name="twitter:description" content="@yield('meta-description', 'Maxanou accompagne les commerces pour vendre, suivre le stock et piloter leur activité.')">
    <meta name="twitter:image" content="{{ asset('icons/maxanou-social.png') }}">
    <title>@yield('title', 'Maxanou — POS de vente et gestion')</title>
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/marketing.css') }}?v=20260902-6" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/marketing-enhancements.css') }}?v=20260915-2" rel="stylesheet">
    @stack('styles')
</head>
<body class="marketing-body">
    <a class="marketing-skip-link" href="#main-content">Aller au contenu</a>
    @include('marketing.components.header')
    <main id="main-content">
        @yield('content')
    </main>
    @include('marketing.components.footer')
    @include('marketing.components.social-modals')
    <script src="{{ asset('hub/assets/js/marketing.js') }}?v=20260916-1" defer></script>
    <script src="{{ asset('pwa-register.js') }}" defer></script>
    @stack('scripts')
    @if($marketingIndexable)
        @yield('structured-data')
    @endif
</body>
</html>
