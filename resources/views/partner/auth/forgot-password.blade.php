@extends('layouts.public-auth')
@section('title', 'Mot de passe partenaire oublié')
@section('content')
<div class="auth-flow auth-login-flow"><div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-envelope" aria-hidden="true"></i> Sécurité partenaire</span><h1>Réinitialisez votre accès.</h1><p>Indiquez votre adresse pour recevoir un lien sécurisé.</p></div>@if(session('status'))<x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>@endif
<form method="POST" action="{{ route('partner.password.email') }}">@csrf<x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" required autocomplete="email" autofocus :error="$errors->first('email')" /><x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Envoi du lien…">Envoyer le lien</x-ui.button></x-ui.form-actions><p class="auth-flow-link"><a href="{{ route('partner.login') }}">Retour à la connexion</a></p></form></div>
@endsection
