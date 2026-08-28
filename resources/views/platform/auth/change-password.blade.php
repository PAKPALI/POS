@extends('layouts.platform')
@section('title', 'Sécuriser le compte')
@section('page-title', 'Sécuriser votre compte plateforme')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="platform-card p-4 p-md-5">
            <div class="alert alert-warning"><i class="bi bi-shield-exclamation me-2"></i>Le mot de passe initial doit être remplacé avant l’accès aux données du SaaS.</div>
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('platform.password.update') }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="current_password">Mot de passe actuel</label>
                    <div class="input-group">
                        <input class="form-control form-control-lg" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="current_password" aria-label="Afficher le mot de passe actuel" aria-pressed="false"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Nouveau mot de passe</label>
                    <div class="input-group">
                        <input class="form-control form-control-lg" type="password" id="password" name="password" autocomplete="new-password" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" aria-label="Afficher le nouveau mot de passe" aria-pressed="false"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="form-text">12 caractères minimum, avec une majuscule, une minuscule, un chiffre et un symbole comme !, @, # ou $.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <div class="input-group">
                        <input class="form-control form-control-lg" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password_confirmation" aria-label="Afficher la confirmation du mot de passe" aria-pressed="false"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button class="btn btn-warning btn-lg w-100 fw-semibold" data-loading-text="Sécurisation…"><i class="bi bi-key me-2"></i>Enregistrer et continuer</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.password-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
        const input = document.getElementById(button.dataset.target);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.setAttribute('aria-pressed', visible ? 'false' : 'true');
        button.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        button.querySelector('i').className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
});
</script>
@endpush
