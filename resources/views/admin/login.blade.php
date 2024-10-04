@extends('layouts.layout_admin')

@section('content')
    <div class="login">
        <div class="login-content">
            <form action="https://seantheme.com/hud/index.html" method="POST" name="login_form">
                <h1 class="text-center">SE CONNECTER</h1>
                <div class="text-inverse text-opacity-50 text-center mb-4">
                    APP-NAME
                </div>
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg bg-inverse bg-opacity-5" value
                        placeholder>
                </div>
                <div class="mb-3">
                    <div class="d-flex">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <a href="#" class="ms-auto text-inverse text-decoration-none text-opacity-50">Mot de passe oublié?</a>
                    </div>
                    <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" value placeholder>
                </div>
                <!-- <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value id="customCheck1">
                        <label class="form-check-label" for="customCheck1">Remember me</label>
                    </div>
                </div> -->
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mb-3 mt-5">Se connecter</button>
                <div class="text-center text-inverse text-opacity-50">
                    <!-- Don't have an account yet? <a href="page_register.html">Sign up</a>. -->
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            $('#loader').hide();
            //ajax pour se connecter
            $('#form-login').submit(function(){
                event.preventDefault();
                $('#submit').hide();
                $('#loader').fadeIn();
                $.ajax({
                    type: 'POST',
                    url: 'login',
                    data: $('#form-login').serialize(),
                    datatype: 'json',
                    success: function (data){
                        console.log(data)
                        if (data.status) {
                            Swal.fire({
                                icon: "success",
                                title: data.title,
                                text: "Connexion réussie!",
                            }).then(() => {
                                if (data.redirect_to != null){
                                    window.location.assign(data.redirect_to);
                                }
                            });
                        } else {
                            $('#loader').hide();
                            $('#submit').show();
                            Swal.fire({
                                title: data.title,
                                text:data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: 'red',
                            });
                        }
                    },
                    error: function (data){
                        $('#loader').hide();
                        $('#submit').show();
                        Swal.fire({
                            icon: "error",
                            title: "Erreur",
                            text: "Impossible de communiquer avec le serveur.",
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