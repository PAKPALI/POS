@extends('layouts.public-auth')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="auth-flow auth-login-flow">
        <div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-envelope" aria-hidden="true"></i> Récupération</span><h1>Mot de passe oublié ?</h1><p>Indiquez votre adresse e-mail pour recevoir un lien sécurisé.</p></div>
        @if (session('status'))<x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>@endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" required autocomplete="email" autofocus :error="$errors->first('email')" />
            <x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Envoi du lien…">Envoyer le lien</x-ui.button></x-ui.form-actions>
            <p class="auth-flow-link"><a href="{{ route('user_login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour à la connexion</a></p>
        </form>
    </div>
@endsection
