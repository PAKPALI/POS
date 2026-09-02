@extends('layouts.saas')
@section('title', 'Menus')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <div class="saas-page-heading">
                            <div>
                                <h1>Menus</h1>
                                <p>Composez des offres à partir des produits du catalogue.</p>
                            </div>
                            <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-lg"></i> Ajouter un menu
                            </button>
                        </div>
                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header modal-header-accent">
                                        <h3 class="modal-title">Ajouter un menu</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <input type="hidden" name="type" value="2" class="form-control" id="exampleInputText0" placeholder="0">
                                                <div class="form-group col-12 mb-3 text-center bg-light">
                                                    <label for="exampleInputText0"><h5 class="text-dark">informations menu</h5></label>
                                                </div>
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
                                                <div class="form-group col-6 mb-3">
                                                    <label for="exampleInputText0">Marge de sécurité</label>
                                                    <input type="number" name="margin" value="0" class="form-control" id="exampleInputText0" placeholder="0">
                                                </div>

                                                <div class="form-group col-6 mb-3">
                                                    <label for="exampleInputText0">Prix unitaire</label>
                                                    <input type="number" name="price" class="form-control price" id="exampleInputText0" placeholder="0">
                                                </div>
                                                <div class="form-group col-6 mb-3">
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

                                            <div class="row mt-3">
                                                <div class="form-group col-12 mb-3 text-center bg-light">
                                                    <label for="exampleInputText0"><h5 class="text-dark">informations produits</h5></label>
                                                </div>
                                                <button type="button" class="saas-btn saas-btn-outline add-product-field mb-2">
                                                    <i class="bi bi-plus-lg"></i> Ajouter un produit
                                                </button>
                                                <div id="product-fields-container">
                                                    <!-- Dynamic field will be here -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-4">
                                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Création…">
                                                <span>Créer le menu</span>
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
                                <div class="modal-content saas-modal-content saas-modal-warning">
                                    <div class="modal-header modal-header-accent modal-header-warning">
                                        <h3 class="modal-title">Modifier le menu</h3>
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
                                    <div class="modal-header modal-header-accent">
                                        <h3 class="modal-title">Détail du menu</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="show_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- template for dynamic field -->
                        <template id="product-field-template">
                            <div class="row product-field">
                                <div class="form-group col-5 mb-2">
                                    <label for="exampleInputText0">Produits</label>
                                    <select class="form-select mb-3 product-select" name="products[]">
                                        <option value="">selectionnez un produit</option>
                                    </select>
                                </div>

                                <div class="form-group col-6 mb-2">
                                    <label for="exampleInputText0">Quantité</label>
                                    <input type="number" name="quantities[]" class="form-control product-quantity" placeholder="0">
                                </div>

                                <div class="form-group col-1 mb-2 d-flex align-items-center">
                                    <button type="button" class="btn btn-danger remove-product-field">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="col-xl-12">
                            <div class="saas-card">
                                <div class="saas-card-head">
                                    <div>
                                        <h2>Liste des menus</h2>
                                        <p class="saas-card-description">Consultez, modifiez ou archivez les compositions proposées à la vente.</p>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="datatable" class="table text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>État</th>
                                                <th>Nom</th>
                                                <th>Catégorie</th>
                                                <th>Quantité</th>
                                                <th>Prix</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    
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

    @push('scripts')
    <script src="{{ asset('hub/assets/plugins/datatables.net/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function() {
            // hide loader
            $('#loader').hide();

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('menu.index')}}",
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
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun menu composé pour le moment",
                    "processing": "Chargement des menus…",
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
                
            });

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            // Add product field
            const menuProductSearchUrl = @json(route('menu.products.search'));

            function initMenuProductSelect(element, dropdownParent) {
                $(element).select2({
                    width: '100%',
                    placeholder: 'Rechercher un produit',
                    allowClear: true,
                    dropdownParent: $(dropdownParent),
                    ajax: {
                        url: menuProductSearchUrl,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({q: params.term || '', page: params.page || 1}),
                        processResults: data => data,
                        cache: true
                    }
                });
            }

            $('.add-product-field').on('click', function () {
                let template = $('#product-field-template').html(); // get template model
                $('#product-fields-container').append(template);   // Add template in container
                initMenuProductSelect($('#product-fields-container .product-select').last(), '#addModal');
            });

            // Delete product field
            $('#product-fields-container').on('click', '.remove-product-field', function () {
                $(this).closest('.product-field').remove(); // Delete parent bloc
            });

            //Add menu
            $('#add').submit(function (event) {
                event.preventDefault();
                let isValid = true; 
                let products = [];

                // Construire la liste des produits
                $('.product-field').each(function () {
                    let productSelect = $(this).find('.product-select').val();
                    let productQuantity = $(this).find('.product-quantity').val();

                    // if (!productSelect && !productQuantity) {
                    //     return true; // Ignore les champs vides
                    // }

                    if (!productSelect) {
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: "error",
                            title: "ERREUR",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            text: "Veuillez sélectionner un produit !",
                        });
                        isValid = false;
                        return false;
                    }

                    if (!productQuantity || productQuantity <= 0) {
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: "error",
                            title: "ERREUR",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            text: "Veuillez saisir une quantité valide !",
                        });
                        isValid = false;
                        return false;
                    }

                    products.push({
                        product_id: productSelect,
                        quantity: parseInt(productQuantity)
                    });
                });

                // Vérifier si la validation a échoué ou si aucun produit n'a été ajouté
                if (!isValid || products.length === 0) {
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: "ERREUR",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        text: "Ajoutez au moins un produit valide avant de soumettre !",
                    });
                    return false;
                }

                // Préparer les données du formulaire
                let formData = new FormData($('#add')[0]);

                // Ajouter les produits dans le FormData
                products.forEach((product, index) => {
                    formData.append(`products[${index}][product_id]`, product.product_id);
                    formData.append(`products[${index}][quantity]`, product.quantity);
                });

                // Envoi des données via AJAX
                $.ajax({
                    type: 'POST',
                    url: "{{ route('menu.store') }}",
                    enctype: 'multipart/form-data',
                    data: formData,
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    success: function (data) {
                        if (data.status) {
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
                            Datatable.draw();
                        } else {
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
                    error: function (data) {
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


            $('body').on('click', '.editModal', function () {
                const trigger = this;
                var id = $(this).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#edit_response').empty();
                $('#editModal').modal('show');
                $.ajax({
                    url:'{{url('component/menu')}}/'+id+'/edit',
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#edit_response').html(result);
                    },
                    error: function() { $('#editModal').modal('hide'); Swal.fire({icon: 'error', title: 'Chargement impossible', text: 'Impossible de charger ce menu.'}); },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            $('body').on('click', '.view', function () {
                const trigger = this;
                var id = $(this).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#show_response').empty();
                $('#showModal').modal('show');
                $.ajax({
                    url:'{{url('component/menu')}}/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    },
                    error: function() { $('#showModal').modal('hide'); Swal.fire({icon: 'error', title: 'Chargement impossible', text: 'Impossible de charger ce menu.'}); },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            $('body').on('click', '.deleteUser', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir désactiver cet utilisateur?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                    showCancelButton: true,
                    cancelButtonText: "Non",
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
                    title: "Etes vous sur de vouloir archiver ce menu?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                    showCancelButton: true,
                    cancelButtonText: "Non",
                }).then((result) => {
                    if (result.isConfirmed){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            type: "post",
                            url: 'menu/'+id,
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
                    title: "Etes vous sur de vouloir restaurer ce menu?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                    showCancelButton: true,
                    cancelButtonText: "Non",
                }).then((result) => {
                    if (result.isConfirmed){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            type: "post",
                            url: 'menu/'+id,
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
    @endpush

    @endsection
