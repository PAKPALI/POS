@extends('layouts.public-auth')

@section('title', 'Connexion')

@section('content')
    <div class="auth-flow auth-login-flow">
        <div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-shield-check" aria-hidden="true"></i> Espace sécurisé</span><h1>Bon retour.</h1><p>Connectez-vous pour retrouver votre espace de travail.</p></div>
        <form id="form_login" class="auth-flow-form">@csrf
            <div class="auth-field"><label for="email">Adresse e-mail</label><div class="auth-control"><i class="bi bi-envelope" aria-hidden="true"></i><input id="email" type="email" name="email" placeholder="vous@exemple.com" autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" required autofocus></div></div>
            <div class="auth-field"><div class="auth-label-row"><label for="password">Mot de passe</label><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></div><div class="auth-control"><i class="bi bi-lock" aria-hidden="true"></i><input id="password" type="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required><button id="togglePassword" type="button" aria-label="Afficher le mot de passe"><i id="togglePasswordIcon" class="bi bi-eye" aria-hidden="true"></i></button></div></div>
            <button type="submit" class="saas-btn saas-btn-primary auth-submit" data-loading-text="Connexion…">Se connecter <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>
        <p class="auth-flow-switch">Vous débutez ? <a href="{{ route('register') }}">Créer votre espace</a></p>
    </div>

    @push('scripts')
    <script>
        $(function() {
            //ajax pour se connecter
            $('#form_login').submit(function(event){
                event.preventDefault();
                const button = this.querySelector('[type="submit"]');
                window.ServerButtonLoader.withLoader(button, $.ajax({
                    type: 'POST',
                    url: @json(route('login', [], false)),
                    data: $('#form_login').serialize(),
                    dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                })).then(function (data) {
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
                                buttonsStyling: false,
                                customClass: { confirmButton: 'saas-btn saas-btn-danger' },
                            });
                        }
                }).catch(function (xhr) {
                        // Une session peut être créée avant qu'un middleware ne renvoie
                        // une page HTML. Vérifier alors la session au lieu d'afficher
                        // une alerte vide ou de demander une seconde connexion.
                        if (xhr.status >= 200 && xhr.status < 300 && typeof xhr.responseText === 'string'
                            && /<html[\s>]/i.test(xhr.responseText)) {
                            window.location.assign(@json(route('dashboard', [], false)));
                            return;
                        }

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
    @endpush
@endsection
