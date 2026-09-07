@extends('layouts.public-auth')

@section('title', 'Confirmer votre mot de passe')

@section('content')
    <div class="auth-flow auth-login-flow">
        <div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-lock" aria-hidden="true"></i> Vérification</span><h1>Confirmez votre identité.</h1><p>Pour continuer, renseignez à nouveau votre mot de passe.</p></div>
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <x-ui.password id="password" name="password" label="Mot de passe" required autocomplete="current-password" autofocus :error="$errors->first('password')" />
            <x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Vérification…">Confirmer</x-ui.button></x-ui.form-actions>
            @if (Route::has('password.request'))<p class="auth-flow-link"><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></p>@endif
        </form>
    </div>
@endsection
