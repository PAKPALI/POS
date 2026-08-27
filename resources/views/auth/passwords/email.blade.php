@extends('layouts.layout_admin')
@section('content')
<div class="login"><div class="login-content">
<form method="POST" action="{{ route('password.email') }}">@csrf
    <h1 class="text-center">MOT DE PASSE OUBLIÉ</h1>
    <p class="text-inverse text-opacity-50 text-center mb-4">Indiquez votre adresse e-mail pour recevoir un lien sécurisé.</p>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="mb-3">
        <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('email') is-invalid @enderror" required autocomplete="email" autofocus>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mt-4" data-loading-text="Envoi du lien…">Envoyer le lien</button>
    <div class="text-center mt-4"><a href="{{ route('user_login') }}" class="text-theme fw-semibold text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Retour à la connexion</a></div>
</form></div></div>
@endsection
