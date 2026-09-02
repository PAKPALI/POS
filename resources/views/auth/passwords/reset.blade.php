@extends('layouts.public-auth')

@section('title', 'Nouveau mot de passe')
@section('content')
<div class="login"><div class="login-content">
<form method="POST" action="{{ route('password.update') }}">@csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <h1 class="text-center">NOUVEAU MOT DE PASSE</h1>
    <p class="text-inverse text-opacity-50 text-center mb-4">Choisissez un nouveau mot de passe pour votre compte.</p>
    <div class="mb-3"><label for="email" class="form-label">Adresse e-mail</label><input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('email') is-invalid @enderror" required autocomplete="email">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label for="password" class="form-label">Nouveau mot de passe</label><input id="password" type="password" name="password" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('password') is-invalid @enderror" required autocomplete="new-password">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label for="password-confirm" class="form-label">Confirmer le mot de passe</label><input id="password-confirm" type="password" name="password_confirmation" class="form-control form-control-lg bg-inverse bg-opacity-5" required autocomplete="new-password"></div>
    <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mt-4" data-loading-text="Modification…">Modifier le mot de passe</button>
    <div class="text-center mt-4"><a href="{{ route('user_login') }}" class="text-theme fw-semibold text-decoration-none">Retour à la connexion</a></div>
</form></div></div>
@endsection
