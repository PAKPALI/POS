@extends('layouts.layout')
@push('css-scripts')
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }

    /* Transform button in circle */
    .state {
        display: inline-block;
        width: 15px; /* circle width */
        height: 15px; /* circle height */
        border-radius: 50%; /* Rounded edges to make a circle */
        animation: blink 1s infinite; /* Add blink animation */
    }
    .state1 {
        display: inline-block;
        width: 15px; /* circle width */
        height: 15px; /* circle height */
        border-radius: 50%; /* Rounded edges to make a circle */
    }

    /* Animation of blink */
    @keyframes blink {
        0%, 100% {
        opacity: 1; /* Completely visible */
        }
        50% {
        opacity: 0.5; /* Semi-transparent */
        }
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-xl-12">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            PRODUITS
                            <!-- <img src="{{ asset('images/1729538166.jpg') }}" alt="Image du produit"> -->
                        </h1>
                        <hr class="mb-4">
                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h3 class="modal-title">Ajouter produit</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <input type="hidden" name="type" value="1" class="form-control" id="exampleInputText0" placeholder="0">
                                                <div class="form-group col-6 mb-3">
                                                    <label for="exampleInputText0">Catégorie</label>
                                                    
                                                    <select class="form-select mb-3" name="category">
                                                        <option value="">selectionnez une catégorie</option>
                                                        @foreach ($Category as $cat)
                                                            <option value="{{$cat->id}}">{{$cat->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-6 mb-3">
                                                    <label for="exampleInputText0">Nom</label>
                                                    <input type="text" name="name" class="form-control" id="exampleInputText0" placeholder="Nom">
                                                </div>

                                                <div class="form-group col-6 mb-3">
                                                    <label for="exampleInputText0">Quantité</label>
                                                    <input type="number" name="qte" class="form-control" id="exampleInputText0" placeholder="0">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="exampleInputText0">Marge de sécurité</label>
                                                    <input type="number" name="margin" value="0" class="form-control" id="exampleInputText0" placeholder="0">
                                                </div>

                                                <div class="form-group col-6 mb-3">
                                                    <label for="exampleInputText0">Prix unitaire</label>
                                                    <input type="number" name="price" class="form-control price" id="exampleInputText0" placeholder="0">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="exampleInputText0">Prix d'achat</label>
                                                    <input type="number" name="purchase_price" class="form-control purchase_price" id="exampleInputText0" placeholder="0">
                                                </div>

                                                <div class="form-group col-12 mb-3">
                                                    <label for="exampleInputText0">Bénefice</label>
                                                    <input type="number" name="profit" class="form-control profit" id="exampleInputText0" readonly placeholder="0">
                                                </div>
                                                
                                                <div class="form-group col-12">
                                                    <label class="form-label" for="smFile">Choisir une image</label>
                                                    <input type="file" class="form-control form-control-sm" name="image" id="smFile">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <div id="loader" class="spinner-grow"></div>
                                                <div id="submitText">Valider</div>
                                            </button> 
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- update modal -->
                        <div class="modal" id="editModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h3 class="modal-title text-dark ">Modifier produit</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="edit_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- view modal -->
                        <div class="modal fade" id="showModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h3 class="modal-title text-dark ">Détail</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="show_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <div id="" class="mb-5">
                            <h4>Listes des produits</h4>
                            <button type="button" class="btn btn-primary mb-1 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button>
                            <!-- <p>DataTables is a plug-in for the jQuery Javascript library. It is a highly flexible tool, built upon the foundations of progressive enhancement, that adds all of these advanced features to any HTML table. Please read the <a href="https://datatables.net/" target="_blank">official documentation</a> for the full list of options.</p> -->
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatable" class="table text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Etat</th>
                                                <th>Nom</th>
                                                <th>Catégorie</th>
                                                <th>Quantité</th>
                                                <th>Prix</th>
                                                <th>Status</th>
                                                <!-- <th>Créer par</th> -->
                                                <!-- <th>Créer le</th> -->
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-xl-12">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex fw-bold small mb-3">
                                        <span class="flex-grow-1"><h4>Listes des produits</h4></span>
                                        <button type="button" class="btn btn-primary mb-1 me-3 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="datatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Etat</th>
                                                    <th>Nom</th>
                                                    <th>Catégorie</th>
                                                    <th>Quantité</th>
                                                    <th>Prix</th>
                                                    <th>Status</th>
                                                    <!-- <th>Créer par</th> -->
                                                    <!-- <th>Créer le</th> -->
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    
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
            $('#loader').hide();

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('product.index')}}",
                columns: [
                    {data: 'id',name: 'id'},
                    {data: 'margin',name: 'margin'},
                    {data: 'name',name: 'name'},
                    {data: 'category_id',name: 'category_id'},
                    {data: 'qte',name: 'qte'},
                    {data: 'price',name: 'price'},
                    {data: 'status',name: 'status'},
                    // {data: 'created_by',name: 'created_by'},
                    // {data: 'created_at',name: 'created_at'},
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

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            //Add category
            $('#add').submit(function() {
                $('#loader').fadeIn();
                $('#submitText').hide();
                var formData = new FormData($('#add')[0]);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('product.store') }}",
                    enctype: 'multipart/form-data',
                    data: formData,
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    success: function(data) {
                        console.log(data)
                        if (data.status) {
                            $('#loader').hide();
                            $('#submitText').fadeIn();
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
                            
                            // $('#addModal').modal('hide');
                            $('#add')[0].reset();
                            Datatable.draw();
                        } else {
                            $('#loader').hide();
                            $('#submitText').fadeIn();
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: "error",
                                title: data.title,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                text: data.msg,
                            });
                        }
                    },
                    error: function(data) {
                        console.log(data)
                        $('#loader').hide();
                        $('#submitText').fadeIn();
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
                    url:'{{url('component/product')}}/'+id+'/edit',
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#edit_response').html(result);
                    }
                });
                $('#editModal').modal('show');
            });

            $('body').on('click', '.view', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('component/product')}}/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    }
                });
                $('#showModal').modal('show');
            });

            $('body').on('click', '.deleteUser', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir désactiver cet utilisateur?",
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

            // when unit price and purchase price are updated
            $('.price, .purchase_price').on('input', function() {
                // Récupérer les valeurs des champs
                var unitPrice = parseFloat($('.price').val()) || 0;
                var purchasePrice = parseFloat($('.purchase_price').val()) || 0;
                
                // Calculate profit
                var profit = unitPrice - purchasePrice;

                // Display result in profit field
                $('.profit').val(profit);
            });
            
            $('body').on('click', '.archive', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");   
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir archiver ce produit?",
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
                            url: 'product/'+id,
                            type: "DELETE",
                            datatype: 'json',
                            success: function (data) {
                                if(data.status){
                                    Swal.fire({
                                        toast: true,
                                        position: 'top',
                                        icon: "success",
                                        title: data.title,
                                        showConfirmButton: false,
                                        timer: 5000,
                                        timerProgressBar: true,
                                        text: data.msg,
                                    });
                                    Datatable.draw();
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

            $('body').on('click', '.restore', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir restaurer ce produit?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    confirmButtonColor: 'green',
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
                            url: 'product/'+id,
                            type: "DELETE",
                            datatype: 'json',
                            success: function (data) {
                                if(data.status){
                                    Swal.fire({
                                        toast: true,
                                        position: 'top',
                                        icon: "success",
                                        title: data.title,
                                        showConfirmButton: false,
                                        timer: 5000,
                                        timerProgressBar: true,
                                        text: data.msg,
                                    });
                                    Datatable.draw();
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