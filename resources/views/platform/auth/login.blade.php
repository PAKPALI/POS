<!doctype html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Connexion — Administration SaaS</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/app.min.css') }}" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: radial-gradient(circle at top right, #24375e 0, #0b1220 45%, #060a12 100%); }
        .login-wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .login-card { width: 100%; max-width: 460px; padding: 34px; background: rgba(15,23,42,.94); border: 1px solid rgba(255,255,255,.11); border-radius: 20px; box-shadow: 0 24px 70px rgba(0,0,0,.42); }
        .shield { width: 58px; height: 58px; display: grid; place-items: center; border-radius: 16px; background: rgba(255,159,67,.16); color: #ff9f43; font-size: 26px; }
        .form-control { border-color: #46536a; background: #0b1220; }
    </style>
</head>
<body>
<div class="login-wrap">
    <section class="login-card">
        <div class="shield mb-4">@if(config('platform.identity.logo_url'))<img src="{{ config('platform.identity.logo_url') }}" alt="Logo" width="46" height="46" style="object-fit:contain;border-radius:10px">@else<i class="bi bi-shield-lock-fill"></i>@endif</div>
        <h1 class="h3">Administration SaaS</h1>
        <p class="text-secondary mb-4">Accès réservé aux administrateurs de la plateforme {{ config('app.name') }}.</p>
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('platform.login.submit') }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="email">Adresse e-mail</label><input class="form-control form-control-lg" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
            <div class="mb-4"><label class="form-label" for="password">Mot de passe</label><input class="form-control form-control-lg" id="password" name="password" type="password" autocomplete="current-password" required></div>
            <button class="btn btn-warning btn-lg w-100 fw-semibold" data-loading-text="Connexion…"><i class="bi bi-shield-check me-2"></i>Accéder à la console</button>
        </form>
        <div class="text-center mt-3"><a href="{{ route('platform.password.request') }}">Mot de passe oublié ?</a></div>
        <div class="text-center mt-4"><a class="text-secondary text-decoration-none" href="{{ route('user_login') }}"><i class="bi bi-arrow-left me-1"></i> Retour à l’application POS</a></div>
    </section>
</div>
<script src="{{ asset('hub/assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('hub/assets/js/app.min.js') }}"></script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
</body>
</html>
