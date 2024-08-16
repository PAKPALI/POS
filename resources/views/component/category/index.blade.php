@extends('layouts.layout')
@push('css-scripts')
<link href="{{asset('hub/assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet">
<link href="{{asset('hub/assets/plugins/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet">
<link href="{{asset('hub/assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="row">
                    <div class="col-xl-10">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            CATEGORIES
                        </h1>
                        <hr class="mb-4">
                        <div class="modal modal-cover fade" id="modalCoverExample">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title">Ajouter catégorie</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-12">
                                                    <label for="exampleInputText0">Nom</label>
                                                    <input type="text" name="name" class="form-control" id="exampleInputText0"
                                                        placeholder="Nom">
                                                </div>
                                            </div>
                                            
                                            <!-- loader -->
                                            <div id="add_loader" class="text-center">
                                                <img class="animation__shake" src="{{asset('img/trimax.gif')}}" alt="TRIMAX_Logo"
                                                    height="70" width="70">
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">Valider</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="datatable" class="mb-5">
                            <h4>Listes des catégories</h4>
                            <button type="button" class="btn btn-primary mb-1 text-right" data-bs-toggle="modal" data-bs-target="#modalCoverExample">Ajouter</button>
                            <!-- <p>DataTables is a plug-in for the jQuery Javascript library. It is a highly flexible tool, built upon the foundations of progressive enhancement, that adds all of these advanced features to any HTML table. Please read the <a href="https://datatables.net/" target="_blank">official documentation</a> for the full list of options.</p> -->
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatableDefault" class="table text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Creer par</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1.</td>
                                                <td>Tiger Nixon</td>
                                                <td>System Architect</td>
                                                <td>Action</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-arrow">
                                    <div class="card-arrow-top-left"></div>
                                    <div class="card-arrow-top-right"></div>
                                    <div class="card-arrow-bottom-left"></div>
                                    <div class="card-arrow-bottom-right"></div>
                                </div>
                                <div class="hljs-container">
                                    <pre><code class="xml" data-url="assets/data/table-plugins/code-1.json"></code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2">
                        <nav id="sidebar-bootstrap" class="navbar navbar-sticky d-none d-xl-block">
                            <nav class="nav">
                                <a class="nav-link text-danger" href="#datatable" data-toggle="scroll-to"><strong> Lux Grill</strong></a>
                                <!-- <a class="nav-link text-danger" href="#bootstrapTable" data-toggle="scroll-to">GRILL</a> -->
                            </nav>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('pages-scripts')
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
        //Bootstrap Duallistbox
        $('#add_loader').fadeOut();

        var class_list = $('#class_list').DataTable({
            processing: true,
            serverSide: true,
            {{--ajax: "{{ route('showListClassroom')}}",--}}
            columns: [
                {data: 'id',name: 'id'},
                {data: 'name',name: 'name'},
                {data: 'manager',name: 'manager'},
                // {data: 'created_at',name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],

            drawCallback: function() {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                $('#class_list').css('width','100%');
            },
        });

        //Add user
        $('#add').submit(function() {
            event.preventDefault();
            $('#add_loader').fadeIn();
            $.ajax({
                type: 'POST',
                url: 'classroom/add',
                //enctype: 'multipart/form-data',
                data: $('#add').serialize(),
                datatype: 'json',
                success: function(data) {
                    $('#add_loader').hide();
                    console.log(data)
                    if (data.status) {
                        Swal.fire({
                            icon: "success",
                            title: data.title,
                            text: data.msg,
                        }).then(() => {
                            class_list.draw();
                        })
                    } else {
                        $('#add_loader').fadeOut();
                        Swal.fire({
                            title: data.title,
                            text: data.msg,
                            icon: 'error',
                            confirmButtonText: "D'accord",
                            confirmButtonColor: '#A40000',
                        })
                    }
                },
                error: function(data) {
                    console.log(data)
                    $('#add_loader').fadeOut();
                    Swal.fire({
                        icon: "error",
                        title: "erreur",
                        text: "Impossible de communiquer avec le serveur.",
                        timer: 3600,
                    })
                }
            });
            return false;
        });

        $('body').on('click', '.editUser', function (e) {
            $('#update_loader').fadeOut();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var id = $(this).data('id');
            $('#edit_response').empty();
            $.ajax({
                url:'classroom/edit/'+id,
                dataType: 'html',
                success:function(result)
                {
                    $('#edit_response').html(result);
                }
            });
            $('#modal-update').modal('show');
        });

        $('body').on('click', '.viewUser', function (e) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var id = $(this).data('id');
            $('#view_response').empty();
            $.ajax({
                url:'classroom/view/'+id,
                dataType: 'html',
                success:function(result)
                {
                    $('#view_response').html(result);
                }
            });
            $('#modal-view').modal('show');
        });

        $(document).on('click','.editUser',function(e){
            var modalHeader = $("#modal-header-edit");
            modalHeader.attr("class", "modal-header bg-success text-light");
            e.preventDefault();
        });

        $('#updateUser').submit(function(){
            event.preventDefault();
            $('#update_loader').fadeIn();
            $.ajax({
                type: 'POST',
                url: 'professor/update',
                //enctype: 'multipart/form-data',
                data: $('#updateUser').serialize(),
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
                            $('#modal-update').modal('hide');
                            user_list.draw();
                        })
                    }else{
                        $('#update_loader').fadeOut();
                        Swal.fire({
                            title: data.title,
                            text:data.msg,
                            icon: 'error',
                            confirmButtonText: "Ok",
                            confirmButtonColor: 'blue',
                        })
                    }
                },
                error: function (data){
                    console.log(data)
                    $('#update_loader').fadeOut();
                    Swal.fire({
                        icon: "error",
                        title: "error",
                        text: "server error",
                        timer: 3000,
                    })
                }
            });
            return false;
        });

        $('body').on('click', '.deleteUser', function () {
				var csrfToken = $('meta[name="csrf-token"]').attr('content');
				var id = $(this).data("id");
				
				Swal.fire({
					icon: "question",
					title: "Etes vous sur de vouloir supprimer cet utilisateur?",
					// text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
					confirmButtonText: "Oui",
					confirmButtonColor: 'red',
					showCancelButton: true,
					cancelButtonText: "Non",
					cancelButtonColor: 'blue',
				}).then((result) => {
					if (result.isConfirmed){
						$.ajax({
							headers: {
								'X-CSRF-TOKEN': csrfToken
							},
							type: "post",
							url: "utilisateurs/delete_user",
							data: {id: id},
							datatype: 'json',
							success: function (data) {
								if(data.status){
                                    Swal.fire({
                                        icon: "success",
                                        title: data.title,
                                        text: data.msg,
								    }).then(() => {
									    user_list.draw();
								    })
                                }else{
                                    Swal.fire({
                                        icon: "error",
                                        title: data.title,
                                        text: data.msg,
								    })
                                }
							},
							error: function (data) {
								console.log('Error:', data);
							}
						});
					}
				})
			});
        });
</script>
    @endpush

    @endsection