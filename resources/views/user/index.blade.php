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
                            UTILISATEURS
                        </h1>
                        <hr class="mb-4">
                        <!-- add modal -->
                        <div class="modal modal-cover fade" id="addmodal">
                            <div class="modal-dialog ">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title">Ajouter utilisateur</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label for="exampleInputText0">Nom</label>
                                                    <input type="text" name="name" class="form-control" id="exampleInputText0" placeholder="Nom">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="exampleInputText0">Email</label>
                                                    <input type="email" name="email" class="form-control" id="exampleInputText0" placeholder="Email">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <div class="loader" class="spinner-border text-light" role="status"></div>
                                                <div id="add">Valider</div>
                                            </button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- update modal -->
                        <div class="modal modal-cover fade" id="editModal">
                            <div class="modal-dialog ">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title text-warning ">Modifier utilisateur</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="edit_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="" class="mb-5">
                            <h4>Listes des utilisateurs</h4>
                            <button type="button" class="btn btn-primary mb-1 text-right" data-bs-toggle="modal" data-bs-target="#addmodal">Ajouter</button>
                            <!-- <p>DataTables is a plug-in for the jQuery Javascript library. It is a highly flexible tool, built upon the foundations of progressive enhancement, that adds all of these advanced features to any HTML table. Please read the <a href="https://datatables.net/" target="_blank">official documentation</a> for the full list of options.</p> -->
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatable" class="table text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nom</th>
                                                <th>Email</th>
                                                <th>Créer le</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
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
                    {{-- <div class="col-xl-2">
                        <nav id="sidebar-bootstrap" class="navbar navbar-sticky d-none d-xl-block">
                            <nav class="nav">
                                <a class="nav-link text-danger" href="#datatable" data-toggle="scroll-to"><strong> Lux Grill</strong></a>
                                <!-- <a class="nav-link text-danger" href="#bootstrapTable" data-toggle="scroll-to">GRILL</a> -->
                            </nav>
                        </nav>
                    </div> --}}
                </div>
            </div>
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

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user.index')}}",
                columns: [
                    {data: 'id',name: 'id'},
                    {data: 'name',name: 'name'},
                    {data: 'email',name: 'email'},
                    {data: 'created_at',name: 'created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                responsive: true, 
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucune donnée disponible",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher:",
                    "paginate": {
                        "first": "Premier",
                        "last": "Dernier",
                        "next": "Suivant",
                        "previous": "Précédent"
                    }
                },
                
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('#datatable').css('width','100%');
                    $('#datatable tbody tr').each(function() {
                        $(this).css('background-color', 'black');  // Appliquer un fond personnalisé
                        $(this).css('color', 'white');
                    });
                    $('.dataTables_info, .dataTables_paginate').css('color', 'white');
                    $('.dataTables_paginate .paginate_button a').css('color', 'white');
                    $('.dataTables_length select option').css('color', 'black'); // Mettre la couleur noire pour les options
                    $('.dataTables_length select option').css('background-color', 'white'); // Fond blanc pour les options

                    // Appliquer la couleur blanche au texte des labels
                    $('.dataTables_length label').css('color', 'white'); // Couleur blanche pour "Afficher _MENU_ entrées"
                    $('.dataTables_filter label').css('color', 'white'); // Couleur blanche pour "Rechercher:"
                    
                    // Appliquer les styles pour le dropdown et le champ de recherche
                    $('.dataTables_length select').css({
                        'background-color': 'black', // Fond noir
                        'color': 'white' // Texte en blanc
                    });

                    $('.dataTables_filter input').css({
                        'background-color': 'black', // Fond noir
                        'color': 'white' // Texte en blanc
                    });
                    $('.dataTables_filter input::placeholder').css('color', 'white'); // Placeholder en blanc
                    $('#datatable').css('width', '100%');
                },
            });

            //Add user
            $('#add').submit(function() {
                event.preventDefault();
                $('#loader').fadeIn();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('user.store') }}",
                    //enctype: 'multipart/form-data',
                    data: $('#add').serialize(),
                    datatype: 'json',
                    success: function(data) {
                        console.log(data)
                        if (data.status) {
                            $('#loader').hide();
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: "success",
                                title: data.title,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                text: data.msg,
                            });
                            $('#addModal').hide();
                            Datatable.draw();
                        } else {
                            $('#loader').fadeOut();
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
                        $('#loader').fadeOut();
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

            $('body').on('click', '.editModal', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('component/category')}}/'+id+'/edit',
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#edit_response').html(result);
                    }
                });
                $('#editModal').modal('show');
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

    @endsection