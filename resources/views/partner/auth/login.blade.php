@extends('layouts.public-auth')
@section('title', 'Connexion partenaire')
@section('content')
<div class="auth-flow auth-login-flow"><div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-people" aria-hidden="true"></i> Partenaires Maxanou</span><h1>Bienvenue dans votre espace.</h1><p>Connectez-vous pour suivre vos recommandations et vos revenus.</p></div>
@if(session('status'))<x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>@endif
@if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
<form method="POST" action="{{ route('partner.login.submit') }}">@csrf
<x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" required autocomplete="email" autofocus :error="$errors->first('email')" />
<x-ui.password id="password" name="password" label="Mot de passe" required autocomplete="current-password" :error="$errors->first('password')" />
<x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Connexion en cours…">Se connecter <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button></x-ui.form-actions>
<p class="auth-flow-link"><a href="{{ route('partner.password.request') }}">Mot de passe oublié ?</a></p><p class="auth-flow-link">Vous débutez ? <a href="{{ route('partner.register') }}">Créer un compte partenaire</a></p>
</form></div>
@endsection
