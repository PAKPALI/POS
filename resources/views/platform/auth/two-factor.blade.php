@extends('layouts.platform-auth')
@section('title', 'Vérification de sécurité')
@section('content')
<h1>Vérification de sécurité</h1><p class="lead">Un code à six chiffres a été envoyé à votre adresse e-mail. Il reste valable 10 minutes.</p>
@if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
@if(session('success'))<x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>@endif
<div class="platform-auth-actions">
    <form method="POST" action="{{ route('platform.two-factor.verify') }}" class="platform-auth-form">@csrf
        <x-ui.input id="code" name="code" label="Code de connexion" class="platform-auth-code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus />
        <x-ui.button type="submit" variant="primary" data-loading-text="Vérification…">Vérifier et me connecter</x-ui.button>
    </form>
    <form method="POST" action="{{ route('platform.two-factor.resend') }}">@csrf<x-ui.button type="submit" variant="secondary" data-loading-text="Envoi…">Renvoyer un code</x-ui.button></form>
</div>
<nav class="platform-auth-links"><a href="{{ route('platform.login') }}">Annuler</a></nav>
@endsection
