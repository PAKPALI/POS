@extends('layouts.layout_admin')

@section('content')
    <div class="login">
        <div class="login-content">
            <form id="form_login">
                @csrf
                <h1 class="text-center">SE CONNECTER</h1>
                <div class="text-inverse text-opacity-50 text-center mb-4">
                {{ config('app.name') }}
                </div>
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-lg bg-inverse bg-opacity-5"
                        autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" required>
                </div>
                <div class="mb-3">
                    <div class="d-flex">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <a href="{{ route('password.request') }}" class="ms-auto text-inverse text-decoration-none text-opacity-50">Mot de passe oublié ?</a>
                    </div>
                    <input type="password" name="password" class="form-control form-control-lg bg-inverse bg-opacity-5" autocomplete="current-password" required>
                </div>
                <!-- <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value id="customCheck1">
                        <label class="form-check-label" for="customCheck1">Remember me</label>
                    </div>
                </div> -->
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mb-3 mt-5" data-loading-text="Connexion…">
                    Se connecter
                </button>
                <div class="text-center text-inverse text-opacity-75 mt-4">
                    Vous n'avez pas encore de compte ?
                    <a href="{{ route('register') }}" class="text-theme fw-semibold text-decoration-none">
                        Créer votre compte SaaS
                    </a>
                </div>
                <div class="border-top border-secondary border-opacity-25 mt-4 pt-4 text-center">
                    <div class="small text-inverse text-opacity-50 mb-2">Accès réservé au concepteur de la plateforme</div>
                    <a href="{{ route('platform.entry') }}" class="btn btn-outline-warning w-100">
                        <i class="bi bi-shield-lock-fill me-2"></i>Administration SaaS
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            //ajax pour se connecter
            $('#form_login').submit(function(event){
                event.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: @json(route('login', [], false)),
                    data: $('#form_login').serialize(),
                    datatype: 'json',
                    success: function (data){
                        console.log(data)
                        if (data.status) {
                            $('#form_login').slideUp(3000);
                            // Swal.fire({
                            //     icon: "success",
                            //     title: data.title,
                            //     text: data.msg,
                            // }).then(() => {
                            //     if (data.redirect_to != null){
                            //         window.location.assign(data.redirect_to);
                            //     }
                            // });
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: "success",
                                title: data.title,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                text: data.msg,
                                didClose: () => {
                                    // Redirection vers une route après la fermeture de l'alerte
                                    const redirect = new URL(data.redirect_to, window.location.origin);
                                    window.location.assign(redirect.pathname + redirect.search + redirect.hash);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: data.title,
                                text:data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: 'red',
                            });
                        }
                    },
                    error: function (xhr){
                        const response = xhr.responseJSON || {};
                        let message = response.msg || response.message || "Impossible de communiquer avec le serveur.";
                        if (xhr.status === 419) message = "Votre session de connexion a expiré. Rechargez l’application puis réessayez.";
                        if (xhr.status === 429 && response.msg) message = response.msg;
                        Swal.fire({
                            icon: "error",
                            title: response.title || "Connexion impossible",
                            text: message,
                            timer: 3600,
                        });
                    }
                });
                return false;
            });

            $('#togglePassword').on('click', function() {
                const passwordField = document.getElementById("password");
                const toggleIcon = document.getElementById("togglePasswordIcon");

                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    toggleIcon.classList.remove("bi-eye");
                    toggleIcon.classList.add("bi-eye-slash");
                } else {
                    passwordField.type = "password";
                    toggleIcon.classList.remove("bi-eye-slash");
                    toggleIcon.classList.add("bi-eye");
                }
            });
            $('#togglePassword2').on('click', function() {
                const passwordField = document.getElementById("password2");
                const toggleIcon = document.getElementById("togglePasswordIcon2");

                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    toggleIcon.classList.remove("bi-eye");
                    toggleIcon.classList.add("bi-eye-slash");
                } else {
                    passwordField.type = "password";
                    toggleIcon.classList.remove("bi-eye-slash");
                    toggleIcon.classList.add("bi-eye");
                }
            });
        });
    </script>
@endsection
