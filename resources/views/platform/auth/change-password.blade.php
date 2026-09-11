@extends('layouts.platform')
@section('title', 'Sécuriser le compte')
@section('page-title', 'Sécuriser votre compte plateforme')
@section('content')
<div class="platform-password-page">
    <section class="platform-card platform-password-card" aria-labelledby="platform-password-heading">
        <div class="platform-password-intro">
            <span class="platform-password-mark" aria-hidden="true"><i class="bi bi-shield-lock-fill"></i></span>
            <div>
                <p class="platform-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Première connexion</p>
                <h2 id="platform-password-heading">Finalisez la sécurité de votre compte</h2>
                <p>Un dernier réglage est nécessaire avant d’ouvrir la console d’administration.</p>
            </div>
        </div>

        <div class="platform-password-notice" role="status">
            <span class="platform-password-notice-icon" aria-hidden="true"><i class="bi bi-shield-exclamation"></i></span>
            <div>
                <strong>Votre mot de passe initial est temporaire</strong>
                <p>Remplacez-le par un mot de passe personnel pour protéger l’accès aux données du SaaS.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger platform-password-errors" role="alert">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('platform.password.update') }}" class="platform-password-form">
            @csrf @method('PUT')
            <div class="platform-password-field">
                <label class="form-label" for="current_password">Mot de passe actuel</label>
                <div class="input-group">
                    <input class="form-control form-control-lg" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="current_password" aria-label="Afficher le mot de passe actuel" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="platform-password-field">
                <label class="form-label" for="password">Nouveau mot de passe</label>
                <div class="input-group">
                    <input class="form-control form-control-lg" type="password" id="password" name="password" autocomplete="new-password" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="password" aria-label="Afficher le nouveau mot de passe" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>
                <div class="platform-password-help"><i class="bi bi-info-circle" aria-hidden="true"></i><span>12 caractères minimum, avec majuscule, minuscule, chiffre et symbole.</span></div>
            </div>
            <div class="platform-password-field">
                <label class="form-label" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                <div class="input-group">
                    <input class="form-control form-control-lg" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Afficher la confirmation du mot de passe" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="platform-password-actions">
                <span><i class="bi bi-lock-fill" aria-hidden="true"></i> Vos données restent protégées.</span>
                <button type="submit" class="btn btn-warning btn-lg fw-semibold" data-loading-text="Sécurisation…"><i class="bi bi-key-fill" aria-hidden="true"></i>Enregistrer et continuer</button>
            </div>
        </form>
    </section>
</div>
@endsection
