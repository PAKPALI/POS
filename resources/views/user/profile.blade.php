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
										<i class="fa fa-link fa-fw text-inverse text-opacity-50"></i> LUX GRILL
									</div>
									<hr class="mt-4 mb-4">
								</div>
							</div>


							<div class="profile-content">
								<ul class="profile-tab nav nav-tabs nav-tabs-v2">
									<li class="nav-item">
										<a href="#profile-email" class="nav-link active" data-bs-toggle="tab">
											<div class="nav-field">Modifier Email</div>
											<!-- <div class="nav-value">382</div> -->
										</a>
									</li>
									<li class="nav-item">
										<a href="#profile-pass" class="nav-link" data-bs-toggle="tab">
											<div class="nav-field">Modifier Mot de passe</div>
											<!-- <div class="nav-value">1.3m</div> -->
										</a>
									</li>
								</ul>
								<div class="profile-content-container">
									<div class="row gx-6">
										<div class="col-xl-12">
											<div class="tab-content p-0">
                                                <!-- email -->
												<div class="tab-pane fade show active" id="profile-email">
                                                    <div class="col-xl-12">
                                                        <div class="desktop-sticky-top d-none d-lg-block">
                                                            <div class="card mb-3">
                                                                <div class="list-group list-group-flush">
                                                                    <!-- <div class="list-group-item fw-bold px-3"> -->
                                                                        <span class="flex-fill">Email</span>
                                                                        <form  id="updateEmail" class="p-3">
                                                                            @csrf
                                                                            <input type="hidden" name="user_id" value="{{auth()->user()->id}}">
                                                                            <h1 class="text-center">Modifier Email</h1>
                                                                            <p class="text-inverse text-opacity-50 text-center"></p>
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Ancien Email <span class="text-danger">*</span></label>
                                                                                <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="AE" placeholder="email" value="{{auth()->user()->email}}">
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Nouveau email <span class="text-danger">*</span></label>
                                                                                <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="NE" placeholder="nouveau email" value>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Confirmer email <span class="text-danger">*</span></label>
                                                                                <input type="email" class="form-control form-control-lg bg-inverse bg-opacity-5" name="CE" placeholder="confirmer email" value>
                                                                            </div>
                                                                            <div class="mt-5">
                                                                                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100">Modifier</button>
                                                                            </div>
                                                                            <!-- <div class="text-inverse text-opacity-50 text-center">Already have an Admin ID? <a href="page_login.html">Sign In</a> -->
                                                                            </div>
                                                                        </form>
                                                                        <a href="#" class="text-inverse text-opacity-50"><i class="fa fa-cog"></i></a>
                                                                    <!-- </div> -->
                                                                    <a href="#"class="list-group-item list-group-action bg-light text-center">
                                                                    </a>
                                                                </div>
                                                                <div class="card-arrow">
                                                                    <div class="card-arrow-top-left"></div>
                                                                    <div class="card-arrow-top-right"></div>
                                                                    <div class="card-arrow-bottom-left"></div>
                                                                    <div class="card-arrow-bottom-right"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
												</div>
                                                <!-- pass -->
												<div class="tab-pane fade" id="profile-pass">
                                                    <div class="desktop-sticky-top d-none d-lg-block">
                                                    <div class="card mb-3">
                                                        <div class="list-group list-group-flush">
                                                            <div class="list-group-item fw-bold px-3 d-flex">
                                                                <span class="flex-fill"> Mot de passe</span>
                                                                <a href="#" class="text-inverse text-opacity-50"><i class="fa fa-cog"></i></a>
                                                            </div>
                                                            <form  id="updatePassword" class="p-3">
                                                                @csrf
                                                                <input type="hidden" name="user_id" value="{{auth()->user()->id}}">
                                                                <h1 class="text-center">Modifier Mot de passe</h1>
                                                                <p class="text-inverse text-opacity-50 text-center"></p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Ancien Mot de passe <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password2" name="AM" placeholder="mot de passe">
                                                                        <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                                                                            <i class="bi bi-eye" id="togglePasswordIcon2"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nouveau Mot de passe <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password2" name="NM" placeholder="nouveau mot de passe">
                                                                        <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                                                                            <i class="bi bi-eye" id="togglePasswordIcon2"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Confirmez Mot de passe <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <input type="password" class="form-control form-control-lg bg-inverse bg-opacity-5" id="password2" name="CM" placeholder=" confirmez mot de passe">
                                                                        <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                                                                            <i class="bi bi-eye" id="togglePasswordIcon2"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-5">
                                                                    <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100">Modifier</button>
                                                                </div>
                                                                <!-- <div class="text-inverse text-opacity-50 text-center">Already have an Admin ID? <a href="page_login.html">Sign In</a> -->
                                                                </div>
                                                            </form>
                                                            <a href="#"class="list-group-item list-group-action bg-light text-center">
                                                            </a>
                                                        </div>
                                                        <div class="card-arrow">
                                                            <div class="card-arrow-top-left"></div>
                                                            <div class="card-arrow-top-right"></div>
                                                            <div class="card-arrow-bottom-left"></div>
                                                            <div class="card-arrow-bottom-right"></div>
                                                        </div>
                                                    </div>
											    </div>
												</div>
											</div>
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
            // hide loader
            $('.loader').hide();
            $('.pre_loader').hide();

            //update password
            $('#updateEmail').submit(function(){ event.preventDefault();
                $('#load').fadeIn();
                $.ajax({
                    type: 'POST',
                    url: 'updateEmail',
                    //enctype: 'multipart/form-data',
                    data: $('#updateEmail').serialize(),
                    datatype: 'json',
                    success: function (data){
                        console.log(data)
                        if (data.status)
                        {
                            Swal.fire({
                                icon: "success",
                                title: data.title,
                                text: data.msg,
                            }).then(() => {
                                if (data.redirect_to == "1") {
                                    window.location.assign(data.redirect_to)
                                } else {
                                    window.location.reload()
                                }
                                //window.location.reload();
                            })
                        }else{
                            Swal.fire({
                                title: data.title,
                                text:data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: '#A40000',
                            })
                        }
                    },
                    error: function (data){
                        console.log(data)
                        Swal.fire({
                            icon: "error",
                            title: "erreur",
                            text: "Impossible de communiquer avec le serveur.",
                            timer: 3600,
                        })
                    }
                });
                $('#load').hide();
            });

            //update password
            $('#updatePassword').submit(function(){ event.preventDefault();
                $('#load').fadeIn();
                $.ajax({
                    type: 'POST',
                    url: 'updatePassword',
                    //enctype: 'multipart/form-data',
                    data: $('#updatePassword').serialize(),
                    datatype: 'json',
                    success: function (data){
                        console.log(data)
                        if (data.status)
                        {
                            Swal.fire({
                                icon: "success",
                                title: data.title,
                                text: data.msg,
                            }).then(() => {
                                if (data.redirect_to == "1") {
                                    window.location.assign(data.redirect_to)
                                } else {
                                    window.location.reload()
                                }
                                //window.location.reload();
                            })
                        }else{
                            Swal.fire({
                                title: data.title,
                                text:data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: '#A40000',
                            })
                        }
                    },
                    error: function (data){
                        console.log(data)
                        Swal.fire({
                            icon: "error",
                            title: "erreur",
                            text: "Impossible de communiquer avec le serveur.",
                            timer: 3600,
                        })
                    }
                });
                $('#load').hide();
            });
        });
    </script>

    @endsection