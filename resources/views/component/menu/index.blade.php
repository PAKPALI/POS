@extends('layouts.saas')
@section('title', 'Packs')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .pack-form-grid > .saas-form-group { min-width: 0; gap: 6px; align-self: start; }
        @media (min-width: 992px) { .saas-body .modal .pack-form-grid .col-lg-4 { grid-column: span 4; width: auto; } }
        .pack-composition { margin-top: 22px; padding-top: 20px; border-top: 1px solid var(--ds-border); }
        .pack-section-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
        .pack-section-head h4 { margin: 0 0 3px; font-size: 1rem; }
        .pack-section-head p { margin: 0; color: var(--ds-text-secondary); font-size: .78rem; }
        .product-field { display: grid !important; grid-template-columns: minmax(0, 1fr) 140px 42px !important; gap: 12px; align-items: end; margin-bottom: 12px; padding: 14px; border: 1px solid var(--ds-border); border-radius: 12px; background: var(--ds-surface-muted); }
        .product-field .saas-form-group { grid-column: auto !important; width: auto !important; margin: 0; min-width: 0; gap: 6px; }
        .product-field .select2-container { max-width: 100%; }
        .product-field input { display: block; width: 100%; }
        .pack-empty-state { padding: 22px; border: 1px dashed var(--ds-border); border-radius: 12px; color: var(--ds-text-secondary); text-align: center; }
        .pack-modal-submit { display: flex; justify-content: flex-end; }
        @media (max-width: 767.98px) {
            .pack-section-head { align-items: stretch; flex-direction: column; }
            .pack-section-head .saas-btn, .pack-modal-submit .saas-btn { width: 100%; justify-content: center; }
            .product-field { grid-template-columns: minmax(0, 1fr) 86px 42px !important; gap: 8px; padding: 12px; }
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <div class="saas-page-heading">
                            <div>
                                <h1>Packs</h1>
                                <p>Regroupez plusieurs produits existants dans une seule offre vendable.</p>
                            </div>
                            <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-lg"></i> Ajouter un pack
                            </button>
                        </div>
                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header modal-header-accent">
                                        <h3 class="modal-title">Ajouter un pack</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <input type="hidden" name="type" value="2">
                                        <div class="row pack-form-grid">
                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_category">Catégorie</label>
                                                    <select id="pack_category" class="form-select select2-category" name="category" required>
                                                        <option value="">Sélectionnez une catégorie</option>
                                                        @foreach ($Category as $cat)
                                                            <option value="{{$cat->id}}">{{$cat->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_name">Nom</label>
                                                    <input id="pack_name" type="text" name="name" placeholder="Nom du pack" required>
                                                </div>

                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_qte">Quantité disponible</label>
                                                    <input id="pack_qte" type="number" name="qte" min="0" step="1" value="0" required>
                                                    <small>Nombre de packs pouvant être vendus.</small>
                                                </div>
                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_margin">Marge de sécurité <small>(facultative)</small></label>
                                                    <input id="pack_margin" type="number" name="margin" min="0" step="1" placeholder="Ex. 2">
                                                </div>

                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_price">Prix de vente</label>
                                                    <input id="pack_price" type="number" name="price" class="price" min="0" step="0.01" placeholder="0" required>
                                                </div>
                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_purchase_price">Prix d'achat <small>(facultatif)</small></label>
                                                    <input id="pack_purchase_price" type="number" name="purchase_price" class="purchase_price" min="0" step="0.01" placeholder="Non renseigné">
                                                </div>

                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_profit">Bénéfice estimé</label>
                                                    <input id="pack_profit" type="number" class="profit" readonly placeholder="0">
                                                </div>
                                                
                                                <div class="col-md-6 col-lg-4 saas-form-group">
                                                    <label for="pack_image">Image</label>
                                                    <input type="file" class="form-control" name="image" id="pack_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                                </div>
                                            </div>

                                            <section class="pack-composition" aria-labelledby="pack-products-title">
                                                <div class="pack-section-head">
                                                    <div><h4 id="pack-products-title">Produits inclus dans le pack</h4><p>Indiquez la quantité de chaque produit consommée par un pack vendu.</p></div>
                                                <button type="button" class="saas-btn saas-btn-outline add-product-field">
                                                    <i class="bi bi-plus-lg"></i> Ajouter un produit
                                                </button>
                                                </div>
                                                <div id="product-fields-container">
                                                    <div class="pack-empty-state"><i class="bi bi-box-seam"></i><br>Ajoutez au moins un produit pour composer ce pack.</div>
                                                </div>
                                            </section>
                                    </form>
                                    </div>
                                    <div class="modal-footer pack-modal-submit"><button type="submit" form="add" class="saas-btn saas-btn-primary" data-loading-text="Création…"><span>Créer le pack</span></button></div>
                                </div>
                            </div>
                        </div>

                        <!-- update modal -->
                        <div class="modal" id="editModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content saas-modal-content saas-modal-warning">
                                    <div class="modal-header modal-header-accent modal-header-warning">
                                        <h3 class="modal-title">Modifier le pack</h3>
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
                                        <h3 class="modal-title">Détail du pack</h3>
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
                            <div class="product-field">
                                <div class="saas-form-group">
                                    <label>Produit</label>
                                    <select class="form-select product-select">
                                        <option value="">Sélectionnez un produit</option>
                                    </select>
                                </div>

                                <div class="saas-form-group">
                                    <label>Qté / pack</label>
                                    <input type="number" class="product-quantity" min="1" step="1" value="1">
                                    <small class="product-stock-hint">Stock actuel : sélectionnez un produit.</small>
                                </div>

                                <div>
                                    <button type="button" class="saas-icon-button remove-product-field" aria-label="Retirer ce produit">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="col-xl-12">
                            <div class="saas-card">
                                <div class="saas-card-head">
                                    <div>
                                        <h2>Liste des packs</h2>
                                        <p class="saas-card-description">Consultez, modifiez ou archivez les packs proposés à la vente.</p>
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
                    "emptyTable": "Aucun pack composé pour le moment",
                    "processing": "Chargement des packs…",
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
            $('.select2-category').select2({ dropdownParent: $('#addModal'), width: '100%', placeholder: 'Sélectionnez une catégorie', allowClear: true });

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
                $(element).on('select2:select', function (event) {
                    const stock = Number(event.params.data.qte || 0);
                    const row = $(this).closest('.product-field');
                    const quantity = row.find('.product-quantity');
                    quantity.attr('max', stock).data('max-stock', stock);
                    row.find('.product-stock-hint').text('Stock actuel : ' + stock + ' unité' + (stock > 1 ? 's' : '') + '.');
                    if (Number(quantity.val()) > stock) quantity.val(stock || 1);
                });
            }

            $('.add-product-field').on('click', function () {
                $('#product-fields-container .pack-empty-state').remove();
                let template = $('#product-field-template').html(); // get template model
                $('#product-fields-container').append(template);   // Add template in container
                initMenuProductSelect($('#product-fields-container .product-select').last(), '#addModal');
            });

            // Delete product field
            $('#product-fields-container').on('click', '.remove-product-field', function () {
                $(this).closest('.product-field').remove(); // Delete parent bloc
                if (!$('#product-fields-container .product-field').length) {
                    $('#product-fields-container').html('<div class="pack-empty-state"><i class="bi bi-box-seam"></i><br>Ajoutez au moins un produit pour composer ce pack.</div>');
                }
            });

            // Ajouter un pack
            $('#add').submit(function (event) {
                event.preventDefault();
                let isValid = true; 
                let products = [];

                // Construire la liste des produits
                $('.product-field').each(function () {
                    let productSelect = $(this).find('.product-select').val();
                    let productQuantity = $(this).find('.product-quantity').val();
                    const maxStock = Number($(this).find('.product-quantity').data('max-stock'));

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

                    if (Number.isFinite(maxStock) && maxStock >= 0 && Number(productQuantity) > maxStock) {
                        Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Stock insuffisant', showConfirmButton: false, timer: 3500, timerProgressBar: true, text: 'La quantité saisie ne peut pas dépasser le stock actuel (' + maxStock + ').' });
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
                            $('#add')[0].reset();
                            $('#product-fields-container').html('<div class="pack-empty-state"><i class="bi bi-box-seam"></i><br>Ajoutez au moins un produit pour composer ce pack.</div>');
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
                    error: function() { $('#editModal').modal('hide'); Swal.fire({icon: 'error', title: 'Chargement impossible', text: 'Impossible de charger ce pack.'}); },
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
                    error: function() { $('#showModal').modal('hide'); Swal.fire({icon: 'error', title: 'Chargement impossible', text: 'Impossible de charger ce pack.'}); },
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
                    title: "Êtes-vous sûr de vouloir archiver ce pack ?",
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
                    title: "Êtes-vous sûr de vouloir restaurer ce pack ?",
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
