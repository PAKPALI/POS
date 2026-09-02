@extends('layouts.public-auth')

@section('title', 'Créer votre compte')

@section('content')
    <div class="auth-flow auth-register-flow">
        <div class="register-content">
            <form  id="form">
                @csrf
                <input type="hidden" name="appearance_mode" data-appearance-mode value="dark">
                <input type="hidden" name="accent_color" data-accent-color value="#3B82F6">
                <div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-stars" aria-hidden="true"></i> Démarrage rapide</span><h1>Créez votre espace.</h1><p>Les réglages plus avancés resteront accessibles après l’inscription.</p></div>
                <div class="mb-3">
                    <label class="form-label">Nom de l’entreprise</label>
                    <input type="text" class="form-control form-control-lg bg-inverse bg-opacity-5" name="company_name" placeholder="Ex. Boutique Horizon" required autofocus>
                    <div class="form-text text-inverse text-opacity-50">Vous pourrez ajouter le logo, l’adresse et les paramètres plus tard.</div>
                </div>
                <!-- <p class="text-inverse text-opacity-50 text-center">PRO-SELLER</p> -->
                <div class="mb-3">
                    <label class="form-label">Votre nom</label>
                    <input type="text" class="form-control form-control-lg bg-inverse bg-opacity-5" placeholder="Votre nom complet" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pays de l’entreprise</label>
                    <select name="country_code" class="form-select form-select-lg country-select mb-3" data-placeholder="Rechercher un pays" required><option value="">Pays de l’entreprise</option>@foreach(config('african_countries') as $iso => $countryName)<option value="{{ $iso }}" @selected($iso === 'TG')>{{ $countryName }} ({{ $iso }})</option>@endforeach</select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse e-mail</label>
                    <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="email" placeholder="email" value>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password" name="password" placeholder="mot de passe">
                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password2" name="password_confirmation" placeholder="mot de passe">
                        <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                            <i class="bi bi-eye" id="togglePasswordIcon2"></i>
                        </span>
                    </div>
                </div>
                <!-- <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value id="customCheck1">
                        <label class="form-check-label" for="customCheck1">I have read and agree to the <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.</label>
                    </div>
                </div> -->
                <div class="mt-5">
                    <button type="submit" class="saas-btn saas-btn-primary auth-submit" data-loading-text="Création du compte…">Créer mon espace <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
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
    @push('scripts')
    <script>
        $(function() {
            // Inscription SaaS
            $('#form').submit(function(event){
                event.preventDefault();
                const button = this.querySelector('[type="submit"]');
                window.ServerButtonLoader.withLoader(button, $.ajax({
                    type: 'POST',
                    url: "{{ route('admin_register') }}",
                    data: $('#form').serialize(),
                    datatype: 'json',
                })).then(function (data) {
                        console.log(data)
                        if (data.status) {
                            Swal.fire({
                                icon: "success",
                                title: data.title,
                                text: data.msg,
                            }).then(() => {
                                if (data.redirect_to != null){
                                    window.location.assign(data.redirect_to);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: data.title,
                                text:data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                buttonsStyling: false,
                                customClass: { confirmButton: 'saas-btn saas-btn-primary' },
                            });
                        }
                }).catch(function () {
                    Swal.fire({icon:"error",title:"Erreur",text:"Impossible de communiquer avec le serveur.",timer:3600});
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
