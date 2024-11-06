@extends('layouts.layout_sale')
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
<div id="content" class="app-content p-1 ps-xl-4 pe-xl-4 pt-xl-3 pb-xl-3">

    <div class="pos card" id="pos">
        <div class="pos-container card-body">
            <!-- Menu -->
            <div class="pos-menu">
                <!-- logo -->
                <div class="logo">
                    <a href="index.html">
                        <div class="logo-img"><i class="bi bi-x-diamond" style="font-size: 2.1rem;"></i></div>
                        <div class="logo-text">{{config('app.name')}}</div>
                    </a>
                </div>
                <!-- Menu body-->
                <div class="nav-container">
                    <div data-scrollbar="true" data-height="100%" data-skip-mobile="true">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-filter="all">
                                    <div class="card">
                                        <div class="card-body">
                                            <i class="fa fa-fw fa-utensils"></i> Tous
                                            ( <span>{{$Product->count()}}</span> )
                                        </div>
                                        <div class="card-arrow">
                                            <div class="card-arrow-top-left"></div>
                                            <div class="card-arrow-top-right"></div>
                                            <div class="card-arrow-bottom-left"></div>
                                            <div class="card-arrow-bottom-right"></div>
                                        </div>
                                    </div>
                                </a>
                            </li>

                            @foreach($Category as $category)
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-filter="{{$category->name}}">
                                    <div class="card">
                                        <div class="card-body">
                                            <i class="fa fa-fw fa-drumstick-bite"></i> {{$category->name}}
                                            ( <span>{{$category->products->count()}}</span> )
                                        </div>
                                        <div class="card-arrow">
                                            <div class="card-arrow-top-left"></div>
                                            <div class="card-arrow-top-right"></div>
                                            <div class="card-arrow-bottom-left"></div>
                                            <div class="card-arrow-bottom-right"></div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- product -->
            <div class="pos-content">
                <div class="pos-content-container h-100 p-4" data-scrollbar="true" data-height="100%">
                    <div class="row gx-4">
                        <strong class="sale_list">Liste des ventes effectuées cette journée</strong>
                        <div class="card sale_list">
                            <div class="card-body">
                                <table id="datatable" class="table text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Somme totale</th>
                                            <th>Profit total</th>
                                            <th>Caissier</th>
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

                        <!-- product list -->
                        @foreach($Category as $category)
                            @foreach($category->products as $product)
                                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-4 col-sm-6 pb-4" data-type="{{ $category->name }}">
                                    <div class="card h-100">
                                        <div class="card-body h-100 p-1">
                                            <a href="#" class="pos-product" data-bs-toggle="modal" data-bs-target="#modalPosItem"
                                                data-id="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}"
                                                data-image="{{ asset('images/' . $product->image) }}"
                                                data-qte="{{ $product->qte }}"
                                            >

                                                <div class="img" style="background-image: url({{ asset('images/' . $product->image) }})"></div>
                                                <div class="info">
                                                    <div class="title">Nom : {{ $product->name }}&reg;</div>
                                                    <!-- <div class="desc">pork, egg, mushroom, salad</div> -->
                                                    <div class="title price">Prix : {{ $product->price }} FCFA</div>
                                                    <div class="title qte">Quantité : {{ $product->qte }}</div>
                                                </div>
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
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pos-sidebar" id="pos-sidebar">
                <div class="h-100 d-flex flex-column p-0">

                    <div class="pos-sidebar-header">
                        <div class="back-btn">
                            <button type="button" data-toggle-class="pos-mobile-sidebar-toggled" data-toggle-target="#pos" class="btn">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                        <div class="icon"><img src="assets/img/pos/icon-table-black.svg" class="invert-dark" alt></div>
                        <div class="title">Table de vente</div>
                        <!-- <div class="order">Order: <b>#0056</b></div> -->
                    </div>

                    <div class="pos-sidebar-nav">
                        <ul class="nav nav-tabs nav-fill">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#newOrderTab">Commande (<span id="orderCount">0</span>)</a>
                            </li>
                            <li class="nav-item nav-sale">
                                <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#orderHistoryTab">Ventes (0)</a>
                            </li>
                        </ul>
                    </div>

                    <div class="pos-sidebar-body tab-content" data-scrollbar="true" data-height="100%">
                        <div class="tab-pane fade h-100 show active" id="newOrderTab">
                            <div class="pos-order">
                                
                            </div>

                            <!-- <div class="pos-order">
                                <div class="pos-order-product">
                                    <div class="img" style="background-image: url(assets/img/pos/product-2.jpg)"></div>
                                    <div class="flex-1">
                                        <div class="h6 mb-1">Grill Pork Chop</div>
                                        <div class="small">$12.99</div>
                                        <div class="small mb-2">- size: large</div>
                                        <div class="d-flex">
                                            <a href="#" class="btn btn-outline-theme btn-sm"><i class="fa fa-minus"></i></a>
                                            <input type="text" class="form-control w-50px form-control-sm mx-2 bg-white bg-opacity-25 text-center" value="01">
                                            <a href="#" class="btn btn-outline-theme btn-sm"><i class="fa fa-plus"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="pos-order-price">
                                    $12.99
                                </div>
                            </div> -->
                        </div>
                        <div class="tab-pane fade h-100" id="orderHistoryTab">
                            <div class="h-100 d-flex align-items-center justify-content-center text-center p-20">
                                <div>
                                    <div class="mb-3 mt-n5">
                                        <svg width="6em" height="6em" viewBox="0 0 16 16" class="text-gray-300" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M14 5H2v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5zM1 4v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4H1z" />
                                            <path d="M8 1.5A2.5 2.5 0 0 0 5.5 4h-1a3.5 3.5 0 1 1 7 0h-1A2.5 2.5 0 0 0 8 1.5z" />
                                        </svg>
                                    </div>
                                    <h5>No order history found</h5>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="pos-sidebar-footer">
                        <!-- <div class="d-flex align-items-center mb-2">
                            <div>Subtotal</div>
                            <div class="flex-1 text-end h6 mb-0">$30.98</div>
                        </div> -->
                        <!-- <div class="d-flex align-items-center">
                            <div>Taxes (6%)</div>
                            <div class="flex-1 text-end h6 mb-0">$2.12</div>
                        </div> -->
                        <hr>
                        <div class="d-flex align-items-center mb-2">
                            <div>Total</div>
                            <div class="flex-1 text-end h4 mb-0 total-amount">0 FCFA</div>
                        </div>
                        <div class="mt-3">
                            <div class="btn-group d-flex">
                                <!-- <a href="#" class="btn btn-outline-default rounded-0 w-80px">
                                    <i class="bi bi-bell fa-lg"></i><br>
                                    <span class="small">Service</span>
                                </a>
                                <a href="#" class="btn btn-outline-default rounded-0 w-80px">
                                    <i class="bi bi-receipt fa-fw fa-lg"></i><br>
                                    <span class="small">Bill</span>
                                </a> -->
                                <a href="#" id="confirmSale" class="btn btn-outline-theme rounded-0 w-150px">
                                    <i class="bi bi-send-check fa-lg"></i><br>
                                    <span class="small">Vendre</span>
                                </a>
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

    <!-- Modal pour afficher le PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- Utilisez modal-xl pour un modal plus large -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Aperçu du reçu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <iframe id="pdfIframe" width="100%" height="600px"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" id="print" class="btn btn-dark">Imprimer</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="pdfModalLabel">Fermer</button>
                </div>
            </div>
        </div>
    </div>



    <a href="#" class="pos-mobile-sidebar-toggler" data-toggle-class="pos-mobile-sidebar-toggled" data-toggle-target="#pos">
        <i class="bi bi-bag"></i>
        <span id="mobileBadge" class="badge">0</span>
    </a>

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
        $('.sale_list').hide();

        function openPdfInModal(pdfBase64) {
            const pdfData = atob(pdfBase64); // Décode le base64
            const loadingTask = pdfjsLib.getDocument({ data: pdfData });

            loadingTask.promise.then(function(pdf) {
                // On récupère la première page
                pdf.getPage(1).then(function(page) {
                    const scale = 3;// Augmente l'échelle pour une meilleure qualité
                    const viewport = page.getViewport({ scale: scale });
                    
                    // Préparez l'élément canvas
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    // Rendu de la page sur le canvas
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport 
                    };
                    page.render(renderContext);

                    // Ajoutez le canvas à votre modal
                    const modalBody = document.querySelector('#pdfModal .modal-body');
                    modalBody.innerHTML = ''; // Réinitialise le contenu
                    modalBody.appendChild(canvas);

                    // Affiche le modal
                    $('#pdfModal').modal('show');
                });
            }, function (reason) {
                console.error(reason);
            });
        }

        // Ajoutez une fonction d'impression pour imprimer le contenu du canvas
        function printPdf() {
            const canvas = document.querySelector('#pdfModal .modal-body canvas');

            if (!canvas) {
                console.error("Le canvas n'est pas trouvé.");
                return;
            }

            // Convertir le canvas en image
            const imageData = canvas.toDataURL("image/png");

            // Ouvrir une nouvelle fenêtre pour l'impression
            const printWindow = window.open('');
            printWindow.document.write('<html><head><title>Impression PDF</title></head><body>');
            printWindow.document.write('<img src="' + imageData + '" style="width: 100%;" />');
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Attendre que le contenu soit complètement chargé avant d'imprimer
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close(); // Fermer la fenêtre après l'impression
                window.location.reload()
            };
        }

        $('#confirmSale').on('click', function(e) {
            e.preventDefault();

            let products = [];
            let totalAmount = 0;

            $('.pos-order-product').each(function() {
                let productId = $(this).data('product-id');
                let quantity = $(this).find('.quantity-input').val();
                let price = parseFloat($(this).find('.small').text().replace(' FCFA', ''));
                let totalPrice = quantity * price;

                products.push({
                    product_id: productId,
                    quantity: quantity,
                    unit_price: price,
                    total_price: totalPrice
                });

                totalAmount += totalPrice;
            });
            console.log(products);

            Swal.fire({
                    icon: "question",
                    title: "Confirmez?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    confirmButtonColor: 'green',
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    cancelButtonColor: 'blue',
            }).then((result) => {
                if (result.isConfirmed){
                    // AJAX forsend data
                    $.ajax({
                        url: '{{ route("sale.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            products: products,
                            total_amount: totalAmount
                        },
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
                                // create new window for print pdf
                                // var pdfWindow = window.open("");
                                // pdfWindow.document.write(
                                //     "<iframe width='100%' height='100%' src='data:application/pdf;base64, " +
                                //     encodeURI(data.pdfBase64) + "'></iframe>"
                                // );

                                // Appellez cette fonction avec la donnée pdfBase64 reçue du serveur
                                openPdfInModal(data.pdfBase64);
                            } else {
                                $('#loader').hide();
                                $('#submitText').fadeIn();
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
                }
            })
        });

        $('#print').on('click', function(e) {
            printPdf()
        });

        // Au clic sur un élément de navigation
        $('.nav-link').on('click', function(e) {
            e.preventDefault();
            // hide sale list
            $('.sale_list').fadeOut();
            
            // Get the selected category
            var filter = $(this).attr('data-filter');
            
            // Delete active class from all tabs and add to clicked tab
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
            
            // Filter items by category
            if (filter == 'all') {
                // Afficher tous les articles
                $('.pos-content .col-xxl-3').show();
            } else {
               // Hide all items, then show only those in the category
                $('.pos-content .col-xxl-3').hide();
                $('.pos-content .col-xxl-3[data-type="' + filter + '"]').show();
            }
        });

        // Au clic sur élément de navigation de la liste des ventes
        $('.nav-sale').on('click', function(e) {
            e.preventDefault();
            // hide sale list
            $('.sale_list').fadeIn();
        });

        let selectedProducts = new Set(); // init count for count order
        // Update order count
        function updateOrderCount() {
            document.getElementById("orderCount").textContent = selectedProducts.size;
            document.getElementById("mobileBadge").textContent = selectedProducts.size;
        }

        function addProduct(productId) {
            // Ajoute l'ID du produit uniquement s'il n'est pas déjà présent dans l'ensemble
            selectedProducts.add(productId);
            updateOrderCount();
        }

        function removeProduct(productId) {
            // Supprime l'ID du produit de l'ensemble s'il est présent
            selectedProducts.delete(productId);
            updateOrderCount();
        }

        $('.pos-product').on('click', function(e) {
            e.preventDefault();

            let productId = $(this).data('id');
            let productName = $(this).data('name');
            let productPrice = $(this).data('price');
            let productImage = $(this).data('image');
            let productQte = 1;

            // Verify if product already exist
            let existingProduct = $(`.pos-order-product[data-product-id="${productId}"]`);
            if (existingProduct.length > 0) {
                let quantityInput = existingProduct.find('.quantity-input');
                quantityInput.val(parseInt(quantityInput.val()) + 1);
                updateProductTotal(existingProduct, productPrice);
            } else {
                let productHtml = `
                    <div class="pos-order">
                        <div class="pos-order-product" data-product-id="${productId}">
                            <div class="img" style="background-image: url(${productImage})"></div>
                            <div class="flex-1">
                                <div class="h6 mb-1">${productName}</div>
                                <div class="small">${productPrice} FCFA</div>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-outline-theme btn-sm btn-minus"><i class="fa fa-minus"></i></a>
                                    <input type="text" class="form-control w-50px form-control-sm mx-2 bg-white bg-opacity-25 text-center quantity-input" value="${productQte}">
                                    <a href="#" class="btn btn-outline-theme btn-sm btn-plus"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="pos-order-price">${productPrice * productQte} FCFA</div>
                            <div><a href="#" title="supprimer le produit" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></a></div>
                        </div>
                    </div>
                `;

                $('#newOrderTab').append(productHtml);
                addProduct(productId)
            }
            updateTotal();
        });

        function updateProductTotal(productRow, unitPrice) {
            let quantity = productRow.find('.quantity-input').val();
            let total = unitPrice * quantity;
            productRow.find('.pos-order-price').text(total + ' FCFA');
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            $('.pos-order-product').each(function() {
                let productTotal = parseFloat($(this).find('.pos-order-price').text());
                total += productTotal;
            });
            $('.total-amount').text(total + ' FCFA');
        }

        $(document).on('click', '.btn-plus', function(e) {
            e.preventDefault();
            let productRow = $(this).closest('.pos-order-product');
            let quantityInput = productRow.find('.quantity-input');
            quantityInput.val(parseInt(quantityInput.val()) + 1);
            updateProductTotal(productRow, parseFloat(productRow.find('.small').text()));
        });

        $(document).on('click', '.btn-minus', function(e) {
            e.preventDefault();
            let productRow = $(this).closest('.pos-order-product');
            let quantityInput = productRow.find('.quantity-input');
            if (quantityInput.val() > 1) {
                quantityInput.val(parseInt(quantityInput.val()) - 1);
                updateProductTotal(productRow, parseFloat(productRow.find('.small').text()));
            }
        });

        // update price when quantity input is change
        $(document).on('keyup', '.quantity-input', function(e) {
            e.preventDefault();
            let productRow = $(this).closest('.pos-order-product');
            let quantityInput = productRow.find('.quantity-input');
            updateProductTotal(productRow, parseFloat(productRow.find('.small').text()));
        });

        // Delete item and update total
        $(document).on('click', '.remove-item', function(e) {
            const productElement = $(this).closest(".pos-order").find(".pos-order-product");
            const productId = productElement.data('product-id');
            removeProduct(productId)
            $(this).closest(".pos-order").remove();
            updateTotal();
        });

        var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sale.index')}}",
                columns: [
                    {data: 'id',name: 'id'},
                    {data: 'code',name: 'code'},
                    {data: 'total_amount',name: 'total_amount'},
                    {data: 'total_profit',name: 'total_profit'},
                    {data: 'cashier',name: 'cashier'},
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

    });
</script>

@endsection