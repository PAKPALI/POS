@extends('layouts.layout_admin')

@section('content')
    <div class="register">
        <div class="register-content p-2">
            <form id="form">
                @csrf
                <h1 class="text-center">INSCRIPTION</h1>
                <p class="text-inverse text-opacity-50 text-center">{{ config('app.name') }}</p>

                <div class="mb-3">
                    <label class="form-label">Nom de l’entreprise <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg bg-inverse bg-opacity-5" name="company_name" placeholder="Ex. Boutique Horizon" required autofocus>
                    <div class="form-text text-inverse text-opacity-50">Vous pourrez ajouter le logo, l’adresse et les paramètres plus tard.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Votre nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg bg-inverse bg-opacity-5" placeholder="Votre nom complet" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Taxe sur les ventes (%) <span class="text-inverse text-opacity-50">— facultatif</span></label>
                    <input type="number" min="0" max="100" step="0.01" class="form-control form-control-lg bg-inverse bg-opacity-5" name="default_tax" placeholder="Ex. 18">
                    <div class="form-text text-inverse text-opacity-50">Les caisses principale et taxe seront créées automatiquement.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="email" placeholder="email" required>
                </div>

                <input type="hidden" name="user_type" value="2">

                <div class="mb-3">
                    <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password" name="password" placeholder="mot de passe" required>
                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmation du mot de passe <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password2" name="password_confirmation" placeholder="mot de passe" required>
                        <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                            <i class="bi bi-eye" id="togglePasswordIcon2"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100" data-loading-text="Création du compte…">
                        S'inscrire
                    </button>
                </div>

                <div class="text-center text-inverse text-opacity-75 mt-4">
                    Vous avez déjà un compte ?
                    <a href="{{ route('user_login') }}" class="text-theme fw-semibold text-decoration-none">
                        Se connecter
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            $('#form').submit(function(event) {
                event.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin_register') }}",
                    data: $('#form').serialize(),
                    dataType: 'json',
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: data.title,
                                text: data.msg,
                            }).then(function() {
                                if (data.redirect_to) {
                                    window.location.assign(data.redirect_to);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: data.title,
                                text: data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: 'blue',
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: response.msg || response.message || 'Impossible de communiquer avec le serveur.',
                        });
                    }
                });

                return false;
            });

            $('#togglePassword').on('click', function() {
                const passwordField = document.getElementById('password');
                const toggleIcon = document.getElementById('togglePasswordIcon');

                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    passwordField.type = 'password';
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            });

            $('#togglePassword2').on('click', function() {
                const passwordField = document.getElementById('password2');
                const toggleIcon = document.getElementById('togglePasswordIcon2');

                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    passwordField.type = 'password';
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            });
        });
    </script>
@endsection
