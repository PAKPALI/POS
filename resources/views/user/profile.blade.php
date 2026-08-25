@extends('layouts.layout')
@push('css-scripts')
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="profile">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="desktop-sticky-top">
                        <div class="profile-img">
                            <img src="assets/img/user/profile.jpg" alt>
                        </div>

                        <h4>{{auth()->user()->name}}</h4>
                        <!-- <div class="mb-3 text-inverse text-opacity-50 fw-bold mt-n2">@johnsmith</div> -->
                        <p>
                            Email : {{auth()->user()->email}}
                        </p>
                        <div class="mb-1">
                            <i class="fa fa-map-marker-alt fa-fw text-inverse text-opacity-50"></i> Lomé , TOGO
                        </div>
                        <div class="mb-3">
                            <i class="fa fa-link fa-fw text-inverse text-opacity-50"></i>{{ config('app.name') }}
                        </div>
                        <hr class="mt-4 mb-4">
                    </div>
                </div>


                <div class="profile-content">
                    <div id="pills" class="mb-5">
                        <p class="p-1"><strong> Nom: </strong>{{auth()->user()->name}}<p>
                        <p class="p-1"><strong> Email: </strong>{{auth()->user()->email}}</p>
                        <!-- <p class="p-1"><strong> <i class="fa fa-link fa-fw text-inverse text-opacity-50"></i> </strong>{{auth()->user()->email}}</p> -->
                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-pills mb-3" id="pills-tab">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                            href="#pills-home">Modifier Email</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                            href="#pills-profile">Modifier Mot de passe</a>
                                    </li>
                                    <!-- <li class="nav-item">
                                        <a class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                                            href="#pills-contact">Contact</a>
                                    </li> -->
                                </ul>
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="pills-home">
                                        <form  id="updateEmail" class="p-3">
                                            @csrf
                                            <h1 class="text-center">Modifier Email</h1>
                                            <p class="text-inverse text-opacity-50 text-center"></p>
                                            <div class="mb-3">
                                                <label class="form-label">Ancien Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" value="{{auth()->user()->email}}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nouveau email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="NE" placeholder="nouveau email" value>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Confirmer email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="CE" placeholder="confirmer email" value>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" name="current_password" autocomplete="current-password" placeholder="Votre mot de passe actuel" required>
                                            </div>
                                            <div class="mt-5">
                                                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100" data-loading-text="Modification…">Modifier</button>
                                            </div>
                                            <!-- <div class="text-inverse text-opacity-50 text-center">Already have an Admin ID? <a href="page_login.html">Sign In</a> -->
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="pills-profile">
                                        <form  id="updatePassword" class="p-3">
                                            @csrf
                                            <h1 class="text-center">Modifier Mot de passe</h1>
                                            <p class="text-inverse text-opacity-50 text-center"></p>
                                            <div class="mb-3">
                                                <label class="form-label">Ancien Mot de passe <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="currentPassword" name="AM" autocomplete="current-password" placeholder="mot de passe" required>
                                                    <span class="input-group-text password-toggle" data-target="currentPassword" style="cursor: pointer;">
                                                        <i class="bi bi-eye"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nouveau Mot de passe <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="newPassword" name="NM" autocomplete="new-password" placeholder="nouveau mot de passe" required minlength="8">
                                                    <span class="input-group-text password-toggle" data-target="newPassword" style="cursor: pointer;">
                                                        <i class="bi bi-eye"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Confirmez Mot de passe <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="confirmPassword" name="CM" autocomplete="new-password" placeholder="confirmez mot de passe" required minlength="8">
                                                    <span class="input-group-text password-toggle" data-target="confirmPassword" style="cursor: pointer;">
                                                        <i class="bi bi-eye"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-5">
                                                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100" data-loading-text="Modification…">Modifier</button>
                                            </div>
                                            <!-- <div class="text-inverse text-opacity-50 text-center">Already have an Admin ID? <a href="page_login.html">Sign In</a> -->
                                            </div>
                                        </form>
                                    </div>
                                    <!-- <div class="tab-pane fade" id="pills-contact">
                                        Est quis nulla laborum officia ad nisi ex nostrud culpa Lorem
                                        excepteur aliquip dolor aliqua irure ex. Nulla ut duis ipsum nisi
                                        elit fugiat commodo sunt reprehenderit laborum veniam eu veniam.
                                        Eiusmod minim exercitation fugiat irure ex labore incididunt do
                                        fugiat commodo aliquip sit id deserunt reprehenderit aliquip
                                        nostrud. Amet ex cupidatat excepteur aute veniam incididunt mollit
                                        cupidatat esse irure officia elit do ipsum ullamco Lorem. Ullamco ut
                                        ad minim do mollit labore ipsum laboris ipsum commodo sunt tempor
                                        enim incididunt. Commodo quis sunt dolore aliquip aute tempor irure
                                        magna enim minim reprehenderit. Ullamco consectetur culpa veniam
                                        sint cillum aliqua incididunt velit ullamco sunt ullamco quis quis
                                        commodo voluptate. Mollit nulla nostrud adipisicing aliqua cupidatat
                                        aliqua pariatur mollit voluptate voluptate consequat non.
                                    </div> -->
                                </div>
                            </div>
                            <div class="card-arrow">
                                <div class="card-arrow-top-left"></div>
                                <div class="card-arrow-top-right"></div>
                                <div class="card-arrow-bottom-left"></div>
                                <div class="card-arrow-bottom-right"></div>
                            </div>
                            <div class="hljs-container">
                                <pre><code class="xml" data-url="assets/data/ui-tabs-accordions/code-4.json"></code></pre>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <div class="card-arrow">
        <div class="card-arrow-top-left"></div>
        <div class="card-arrow-top-right"></div>
        <div class="card-arrow-bottom-left"></div>
        <div class="card-arrow-bottom-right"></div>
    </div>
</div>

    <script src="{{asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.flash.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.html5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.print.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/bootstrap-table/dist/bootstrap-table.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/js/demo/table-plugins.demo.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/js/demo/sidebar-scrollspy.demo.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>

    <script>
        $(function() {
            $('.password-toggle').on('click', function() {
                const input = document.getElementById($(this).data('target'));
                const icon = $(this).find('i');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.toggleClass('bi-eye bi-eye-slash');
            });

            function showRequestError(xhr) {
                const data = xhr.responseJSON || {};
                Swal.fire({
                    icon: 'error',
                    title: data.title || 'Modification impossible',
                    text: data.msg || 'Impossible de communiquer avec le serveur.',
                    confirmButtonText: "D'accord",
                    confirmButtonColor: '#A40000',
                });
            }

            $('#updateEmail').submit(function(event) {
                event.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('profile.email.update') }}",
                    data: $('#updateEmail').serialize(),
                    dataType: 'json',
                    success: function(data) {
                        Swal.fire({ icon: 'success', title: data.title, text: data.msg })
                            .then(() => window.location.reload());
                    },
                    error: showRequestError,
                });
            });

            $('#updatePassword').submit(function(event) {
                event.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('profile.password.update') }}",
                    data: $('#updatePassword').serialize(),
                    dataType: 'json',
                    success: function(data) {
                        Swal.fire({ icon: 'success', title: data.title, text: data.msg })
                            .then(() => window.location.reload());
                    },
                    error: showRequestError,
                });
            });
        });
    </script>

    @endsection
