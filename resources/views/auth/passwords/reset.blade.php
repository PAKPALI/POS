@extends('layouts.public-auth')

@section('title', 'Nouveau mot de passe')

@section('content')
    <div class="auth-flow auth-login-flow">
        <div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-key" aria-hidden="true"></i> Nouveau mot de passe</span><h1>Sécurisez votre compte.</h1><p>Choisissez un mot de passe que vous seul connaissez.</p></div>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="$email ?? old('email')" required autocomplete="email" :error="$errors->first('email')" />
            <x-ui.password id="password" name="password" label="Nouveau mot de passe" required autocomplete="new-password" :error="$errors->first('password')" />
            <x-ui.password id="password-confirm" name="password_confirmation" label="Confirmer le mot de passe" required autocomplete="new-password" />
            <x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Modification…">Modifier le mot de passe</x-ui.button></x-ui.form-actions>
            <p class="auth-flow-link"><a href="{{ route('user_login') }}">Retour à la connexion</a></p>
        </form>
    </div>
@endsection
