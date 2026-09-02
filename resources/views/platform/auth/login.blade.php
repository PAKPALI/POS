@extends('layouts.platform-auth')
@section('title', 'Connexion plateforme')
@section('content')
<h1>Administration SaaS</h1><p class="lead">Accès réservé aux administrateurs de la plateforme {{ config('app.name') }}.</p>
@if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
<form method="POST" action="{{ route('platform.login.submit') }}" class="platform-auth-form">@csrf
    <x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" autocomplete="username" required autofocus />
    <x-ui.password id="password" name="password" label="Mot de passe" autocomplete="current-password" required />
    <x-ui.button type="submit" variant="primary" data-loading-text="Connexion…"><i class="bi bi-shield-check" aria-hidden="true"></i> Accéder à la console</x-ui.button>
</form>
<nav class="platform-auth-links" aria-label="Liens d’accès"><a href="{{ route('platform.password.request') }}">Mot de passe oublié ?</a><a href="{{ route('user_login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour au POS</a></nav>
@endsection
