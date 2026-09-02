<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#070B14">
    <meta name="description" content="@yield('meta-description', 'Maxanou : le POS simple pour vendre, suivre votre stock, piloter votre caisse et envoyer vos reçus par SMS ou WhatsApp.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="@yield('title', 'Maxanou — POS de vente et gestion')">
    <meta property="og:description" content="@yield('meta-description', 'Maxanou accompagne les commerces pour vendre, suivre le stock, piloter la caisse et envoyer les reçus par SMS ou WhatsApp.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('icons/icon-512.png') }}">
    <title>@yield('title', 'Maxanou — POS de vente et gestion')</title>
    @include('partials.design-system-head')
    <link href="{{ asset('hub/assets/css/marketing.css') }}?v=20260902-5" rel="stylesheet">
    @stack('styles')
</head>
<body class="marketing-body">
    <a class="marketing-skip-link" href="#main-content">Aller au contenu</a>
    @include('marketing.components.header')
    <main id="main-content">
        @yield('content')
    </main>
    @include('marketing.components.footer')
    <script src="{{ asset('hub/assets/js/marketing.js') }}?v=20260902-3" defer></script>
    @stack('scripts')
    @yield('structured-data')
</body>
</html>
