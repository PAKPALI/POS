@extends('layouts.layout_admin')

@section('content')
    <div class="register">
        <div class="register-content p-2">
            <form  id="form">
                @csrf
                <h1 class="text-center">INSCRIPTION</h1>
                <p class="text-inverse text-opacity-50 text-center">{{ config('app.name') }}</p>
                <div class="mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg bg-inverse bg-opacity-5" placeholder="nom" name="name" value>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="email" placeholder="email" value>
                </div>
                <input type="hidden" name="user_type" value="2">
                <div class="mb-3">
                    <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password" name="password" placeholder="mot de passe">
                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmation Mot de passe <span class="text-danger">*</span></label>
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
                    <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100">S'inscrire</button>
                </div>
                <!-- <div class="text-inverse text-opacity-50 text-center">Already have an Admin ID? <a href="page_login.html">Sign In</a> -->
                </div>
            </form>
        </div>
    </div>
    <script>
        $(function() {
            $('#loader').hide();
            //ajax pour se connecter
            $('#form').submit(function(){
                event.preventDefault();
                $('#submit').hide();
                $('#loader').fadeIn();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin_register') }}",
                    data: $('#form').serialize(),
                    datatype: 'json',
                    success: function (data){
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
                            $('#loader').hide();
                            $('#submit').show();
                            Swal.fire({
                                title: data.title,
                                text:data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: 'blue',
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