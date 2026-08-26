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
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-xl-12">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            INVENTAIRES
                        </h1>
                        <hr class="mb-4">
                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h3 class="modal-title">Ajouter inventaire</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label for="product_id">Produit</label>
                                                    <select name="product_id" id="product_id" class="form-select">
                                                        <option value="">Sélectionner un produit</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="supplier_id">Fournisseur</label>
                                                    <select name="supplier_id" id="supplier_id" class="form-select">
                                                        <option value="">Aucun fournisseur</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="qte_added">Quantité ajoutée</label>
                                                    <input type="number" name="qte_added" id="qte_added" class="form-control" placeholder="Quantité ajoutée">
                                                </div>
                                                <div class="form-group col-12 mt-3">
                                                    <label for="qte_before">Note</label>
                                                    <textarea name="note" id="note" class="form-control" placeholder="Note"></textarea>
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

                        <!-- remove modal -->
                        <div class="modal modal fade" id="removeModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger">
                                        <h3 class="modal-title">Retirer inventaire</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="remove">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label for="product_id1">Produit</label>
                                                    <select name="product_id" id="product_id1" class="form-select">
                                                        <option value="">Sélectionner un produit</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="qte_removed">Quantité retirée</label>
                                                    <input type="number" name="qte_removed" id="qte_removed" class="form-control" placeholder="Quantité retirée">
                                                </div>
                                                <div class="form-group col-12 mt-3">
                                                    <label for="qte_before">Note</label>
                                                    <textarea name="note" id="note" class="form-control" placeholder="Note"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-4">
                                            <button type="submit" class="btn btn-danger">
                                                <div id="loader2" class="spinner-grow"></div>
                                                <div id="submitText2">Valider</div>
                                            </button> 
                                        </div>
                                    </form>
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

                        <div class="col-xl-12">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 flex-wrap fw-bold small mb-3">
                                        <span class="me-auto"><h4 class="mb-0">Listes des inventaires</h4></span>
                                        <button type="button" class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-1"></i>Entrée</button>
                                        <button type="button" class="btn btn-sm btn-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#removeModal"><i class="fas fa-minus me-1"></i>Sortie</button>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <hr class="">
                                    <div class="accordion" id="inventoryOptionsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="inventoryFilterHeading">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#inventoryFilterCollapse" aria-expanded="false" aria-controls="inventoryFilterCollapse">
                                                    <i class="fas fa-filter me-2"></i>Filtrer les inventaires
                                                </button>
                                            </h2>
                                            <div id="inventoryFilterCollapse" class="accordion-collapse collapse" data-bs-parent="#inventoryOptionsAccordion">
                                                <div class="accordion-body">
                                                    <div class="row mb-2">
                                                        <div class="col-md-3 mb-2">
                                                            <label>Type</label>
                                                            <select class="form-select" id="type">
                                                                <option value="">Choisir Type</option>
                                                                <option value="1">Entrée</option>
                                                                <option value="2">Sortie</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-3 mb-2">
                                                            <label>Produit</label>
                                                            <select class="form-select" id="filter_product">
                                                                <option value="">Tous les produits</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-3 mb-2">
                                                            <label>Fournisseur</label>
                                                            <select class="form-select" id="filter_supplier">
                                                                <option value="">Tous les fournisseurs</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-3 mb-2">
                                                            <label>Date début</label>
                                                            <input type="date" class="form-control" id="start_date">
                                                        </div>

                                                        <div class="col-md-3 mb-2">
                                                            <label>Date fin</label>
                                                            <input type="date" class="form-control" id="end_date">
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="inventoryExportHeading">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#inventoryExportCollapse" aria-expanded="false" aria-controls="inventoryExportCollapse">
                                                    <i class="fas fa-download me-2"></i>Exporter les données
                                                </button>
                                            </h2>
                                            <div id="inventoryExportCollapse" class="accordion-collapse collapse" data-bs-parent="#inventoryOptionsAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-12 col-sm-4"><button type="button" data-format="csv" class="exportTabular btn btn-warning text-dark w-100"><i class="fas fa-file-csv me-1"></i>CSV</button></div>
                                                        <div class="col-12 col-sm-4"><button type="button" data-format="excel" class="exportTabular btn btn-success w-100"><i class="fas fa-file-excel me-1"></i>Excel</button></div>
                                                        <div class="col-12 col-sm-4"><button type="button" class="btn btn-secondary w-100" id="exportPdf"><i class="fas fa-file-pdf me-1"></i>PDF</button></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="mb-3">
                                    <div class="table-responsive">
                                        <table id="datatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Produit</th>
                                                    <th>Fournisseur</th>
                                                    <th>Qté avant</th>
                                                    <th>Qté saisie</th>
                                                    <th>Qté après</th>
                                                    <th>Créer par</th>
                                                    <th>Créer le</th>
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
            $('#loader2').hide();

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory.index') }}",
                    data: function (d) {
                        d.type = $('#type').val();
                        d.product_id = $('#filter_product').val();
                        d.supplier_id = $('#filter_supplier').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'type',name: 'type'},
                    {data: 'product_id',name: 'product_id'},
                    {data: 'supplier_id',name: 'supplier_id'},
                    {data: 'qte_before',name: 'qte_before'},
                    {data: 'qte_added',name: 'qte_added'},
                    {data: 'qte_after',name: 'qte_after'},
                    {data: 'created_by',name: 'created_by'},
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

            const productSearchUrl = @json(route('inventory.products.search'));
            const supplierSearchUrl = @json(route('inventory.suppliers.search'));

            function remoteSelect(selector, url, placeholder, options = {}) {
                const element = $(selector);
                element.select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    dropdownParent: options.dropdownParent ? $(options.dropdownParent) : undefined,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term || '',
                            page: params.page || 1,
                            ...(options.inStock ? {in_stock: 1} : {})
                        }),
                        processResults: data => data,
                        cache: true
                    }
                });
            }

            remoteSelect('#product_id', productSearchUrl, 'Rechercher un produit', {dropdownParent: '#addModal'});
            remoteSelect('#supplier_id', supplierSearchUrl, 'Aucun fournisseur', {dropdownParent: '#addModal'});
            remoteSelect('#product_id1', productSearchUrl, 'Rechercher un produit en stock', {dropdownParent: '#removeModal', inStock: true});
            remoteSelect('#filter_product', productSearchUrl, 'Tous les produits');
            remoteSelect('#filter_supplier', supplierSearchUrl, 'Tous les fournisseurs');

            $('#filter_product, #filter_supplier, #start_date, #end_date, #type').on('change', function(){
                Datatable.draw();
            });

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            //Add category
            $('#add').submit(function() {
                event.preventDefault();
                $('#loader').fadeIn();
                $('#submitText').hide();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('inventory.store') }}",
                    //enctype: 'multipart/form-data',
                    data: $('#add').serialize(),
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
                            
                            $('#addModal').modal('hide');
                            window.location.reload();
                            // Datatable.draw();
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

            $('#remove').submit(function() {
                event.preventDefault();
                $('#loader2').fadeIn();
                $('#submitText2').hide();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('inventory.remove') }}",
                    //enctype: 'multipart/form-data',
                    data: $('#remove').serialize(),
                    datatype: 'json',
                    success: function(data) {
                        console.log(data)
                        if (data.status) {
                            $('#loader2').hide();
                            $('#submitText2').fadeIn();
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
                            
                            $('#removeModal').modal('hide');
                            window.location.reload();
                            // Datatable.draw();
                        } else {
                            $('#loader2').hide();
                            $('#submitText2').fadeIn();
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

            $('body').on('click', '.view', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('component/inventory')}}/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    }
                });
                $('#showModal').modal('show');
            });

            $('#exportPdf').on('click', function(e){
                e.preventDefault();

                let params = $.param({
                    type: $('#type').val(),
                    product_id: $('#filter_product').val(),
                    supplier_id: $('#filter_supplier').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val()
                });

                window.open("{{ route('inventory.export.pdf') }}?" + params, '_blank');
            });

            $('.exportTabular').on('click', function(e) {
                e.preventDefault();
                const button = this;
                const params = $.param({
                    type: $('#type').val(),
                    product_id: $('#filter_product').val(),
                    supplier_id: $('#filter_supplier').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val()
                });
                const baseUrl = "{{ route('inventory.export.tabular', ['format' => '__FORMAT__']) }}";
                window.ServerButtonLoader.download(button, baseUrl.replace('__FORMAT__', button.dataset.format) + '?' + params)
                    .catch(error => Swal.fire({icon: 'error', title: 'Export impossible', text: error.message}));
            });
        }); 
    </script>

    @endsection
