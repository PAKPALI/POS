@extends('layouts.platform-auth')
@section('title', 'Mot de passe oublié')
@section('content')
<h1>Récupérer votre accès</h1><p class="lead">Indiquez l’adresse de votre compte administrateur. Le lien sera valable 60 minutes.</p>
@if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
@if(session('success'))<x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>@endif
<form method="POST" action="{{ route('platform.password.email') }}" class="platform-auth-form">@csrf
    <x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" autocomplete="email" required autofocus />
    <x-ui.button type="submit" variant="primary" data-loading-text="Envoi…">Envoyer le lien sécurisé</x-ui.button>
</form>
<nav class="platform-auth-links"><a href="{{ route('platform.login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour à la connexion</a></nav>
@endsection
