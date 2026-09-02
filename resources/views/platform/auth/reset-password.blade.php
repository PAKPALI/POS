@extends('layouts.platform-auth')
@section('title', 'Nouveau mot de passe')
@section('content')
<h1>Nouveau mot de passe</h1><p class="lead">Choisissez un mot de passe unique pour sécuriser votre accès plateforme.</p>
@if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
<form method="POST" action="{{ route('platform.password.reset.update') }}" class="platform-auth-form">@csrf
    <input type="hidden" name="token" value="{{ $token }}"><input type="hidden" name="email" value="{{ $email }}">
    <x-ui.password id="password" name="password" label="Nouveau mot de passe" hint="12 caractères minimum avec majuscule, minuscule, chiffre et symbole." minlength="12" autocomplete="new-password" required />
    <x-ui.password id="password_confirmation" name="password_confirmation" label="Confirmer le mot de passe" minlength="12" autocomplete="new-password" required />
    <x-ui.button type="submit" variant="primary" data-loading-text="Enregistrement…">Enregistrer le mot de passe</x-ui.button>
</form>
@endsection
