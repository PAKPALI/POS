@extends('layouts.public-auth')

@section('title', 'Connexion')

@section('content')
    <div class="auth-flow auth-login-flow">
        <div class="auth-flow-heading">
            <span class="auth-flow-kicker"><i class="bi bi-shield-check" aria-hidden="true"></i> Accès sécurisé</span>
            <h1>Bon retour.</h1>
            <p>Connectez-vous pour retrouver votre espace de travail.</p>
        </div>
        @if ($errors->any())<x-ui.alert variant="danger">Veuillez corriger les informations indiquées ci-dessous.</x-ui.alert>@endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" required autocomplete="email" autofocus :error="$errors->first('email')" />
            <x-ui.password id="password" name="password" label="Mot de passe" required autocomplete="current-password" :error="$errors->first('password')" />
            <label class="saas-check-control" for="remember"><input type="checkbox" name="remember" id="remember" @checked(old('remember'))><span>Rester connecté</span></label>
            <x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Connexion en cours…">Se connecter <i class="bi bi-arrow-right" aria-hidden="true"></i></x-ui.button></x-ui.form-actions>
            @if (Route::has('password.request'))<p class="auth-flow-link"><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></p>@endif
            @if (Route::has('register'))<p class="auth-flow-link">Vous n’avez pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a></p>@endif
        </form>
        <a class="auth-cross-cta" href="{{ route('partner.login') }}">
            <span class="auth-cross-cta-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
            <span><strong>Devenir partenaire</strong><small>Rejoignez le programme Maxanou</small></span>
            <i class="bi bi-arrow-up-right auth-cross-cta-arrow" aria-hidden="true"></i>
        </a>
    </div>
@endsection
