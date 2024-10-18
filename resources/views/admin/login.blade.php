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
                    <input type="text" name="email" class="form-control form-control-lg bg-inverse bg-opacity-5" value
                        placeholder>
                </div>
                <div class="mb-3">
                    <div class="d-flex">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <a href="#" class="ms-auto text-inverse text-decoration-none text-opacity-50">Mot de passe oublié?</a>
                    </div>
                    <input type="password" name="password" class="form-control form-control-lg bg-inverse bg-opacity-5" value placeholder>
                </div>
                <!-- <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value id="customCheck1">
                        <label class="form-check-label" for="customCheck1">Remember me</label>
                    </div>
                </div> -->
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mb-3 mt-5">
                    <div id="loader" class="spinner-grow"></div>
                    <div id="submitText">Se connecter</div>
                </button>
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
            $('#form_login').submit(function(){
                event.preventDefault();
                $('#submitText').hide();
                $('#loader').fadeIn();
                $.ajax({
                    type: 'POST',
                    url: 'login',
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
                                    window.location.assign(data.redirect_to);
                                }
                            });
                        } else {
                            $('#loader').hide();
                            $('#submitText').show();
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
                        $('#submitText').show();
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