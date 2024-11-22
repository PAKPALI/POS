@extends('layouts.layout')
@push('css-scripts')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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
            <!-- total sale -->
            <div class="col-xl-3 col-lg-6 ">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-0"><h5><strong>Total des Ventes</strong><h5></span>
                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                <i class="bi bi-fullscreen"></i></a> -->
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col-7">
                                <h5 class="mb-0" id="totalSale"></h5>
                            </div>
                            <div class="col-5">
                                <div class="mt-n2" data-render="apexchart" data-type="bar" data-title="Visitors"
                                    data-height="30"></div>
                            </div>
                        </div>
                        <!-- <div class="small text-inverse text-opacity-50 text-truncate">
                            <i class="fa fa-chevron-up fa-fw me-1"></i> 33.3% more than last week<br>
                            <i class="far fa-user fa-fw me-1"></i> 45.5% new visitors<br>
                            <i class="far fa-times-circle fa-fw me-1"></i> 3.25% bounce rate
                        </div> -->
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            </div>

            <!-- total  product sold daily-->
            <div class="col-xl-3 col-lg-6 ">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-0"><h5><strong>Total des produits</strong><h5></span>
                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                <i class="bi bi-fullscreen"></i></a> -->
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col-7">
                                <h5 class="mb-0" id="totalProduct"></h5>
                            </div>
                            <div class="col-5">
                                <div class="mt-n2" data-render="apexchart" data-type="bar" data-title="Visitors"
                                    data-height="30"></div>
                            </div>
                        </div>
                        <!-- <div class="small text-inverse text-opacity-50 text-truncate">
                            <i class="fa fa-chevron-up fa-fw me-1"></i> 33.3% more than last week<br>
                            <i class="far fa-user fa-fw me-1"></i> 45.5% new visitors<br>
                            <i class="far fa-times-circle fa-fw me-1"></i> 3.25% bounce rate
                        </div> -->
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            </div>

            <!-- total  amount daily-->
            <div class="col-xl-3 col-lg-6 ">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-0"><h5><strong>Somme totale</strong><h5></span>
                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                <i class="bi bi-fullscreen"></i></a> -->
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col-7">
                                <h5 class="mb-0" id="totalAmount"></h5>
                            </div>
                            <div class="col-5">
                                <div class="mt-n2" data-render="apexchart" data-type="bar" data-title="Visitors"
                                    data-height="30"></div>
                            </div>
                        </div>
                        <!-- <div class="small text-inverse text-opacity-50 text-truncate">
                            <i class="fa fa-chevron-up fa-fw me-1"></i> 33.3% more than last week<br>
                            <i class="far fa-user fa-fw me-1"></i> 45.5% new visitors<br>
                            <i class="far fa-times-circle fa-fw me-1"></i> 3.25% bounce rate
                        </div> -->
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            </div>

            <!-- total day profit daily-->
            <div class="col-xl-3 col-lg-6 ">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-0"><h5><strong>Bénefice</strong><h5></span>
                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                <i class="bi bi-fullscreen"></i></a> -->
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col-7">
                                <h5 class="mb-0" id="totalProfit"></h5>
                            </div>
                            <div class="col-5">
                                <div class="mt-n2" data-render="apexchart" data-type="bar" data-title="Visitors"
                                    data-height="30"></div>
                            </div>
                        </div>
                        <!-- <div class="small text-inverse text-opacity-50 text-truncate">
                            <i class="fa fa-chevron-up fa-fw me-1"></i> 33.3% more than last week<br>
                            <i class="far fa-user fa-fw me-1"></i> 45.5% new visitors<br>
                            <i class="far fa-times-circle fa-fw me-1"></i> 3.25% bounce rate
                        </div> -->
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row">
                    <div class="col-9">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            HISTORIQUE DES VENTES
                        </h1>
                        <div class="card mt-3">
                            <div class="card-body">
                                <form id="searchForm">
                                    @csrf
                                    <div class="card-body">
                                        <div class="row">
                                            <label class="form-label"><h4>Choisir la date</h4></label>
                                            <!-- <input id="reportrange" class="btn btn-outline-theme d-flex align-items-center text-start"> -->
                                            <input id="reportrange" type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="card-footer mt-1">
                                        <button id="submit" type="button" class="btn btn-info">
                                            <div id="loader" class="spinner-grow"></div>
                                            <div id="submitText">Valider</div>
                                        </button> 
                                    </div>
                                </form>
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
                        <hr class="mb-4">

                        <div id="" class="mb-2 mt-5">
                            <h4>Listes des ventes</h4>
                            <!-- <button type="button" class="btn btn-primary mb-1 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button> -->
                            <!-- <p>DataTables is a plug-in for the jQuery Javascript library. It is a highly flexible tool, built upon the foundations of progressive enhancement, that adds all of these advanced features to any HTML table. Please read the <a href="https://datatables.net/" target="_blank">official documentation</a> for the full list of options.</p> -->
                            <div class="card">
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
                        </div>
                    </div>

                    <div class="col-3">
                        <table id="mostSoldProductsTable" class="table table-striped">
                            <h6 id="mostText">Produits les plus vendus</h6>
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- dynamic content updating by JS -->
                            </tbody>
                        </table>
                    </div>
                    <!-- view modal -->
                    <div class="modal fade" id="showModal">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h3 class="modal-title text-dark ">Détail de la vente</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="show_response"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(function() {
            // hide loader
            $('#loader').hide();
            $('#mostText').hide()
            $('#mostSoldProductsTable').hide()

            // Configurer Moment.js en français
            moment.locale('fr');

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history') }}",
                    data: function(d) {
                        d.daterange = $('#reportrange').val();
                    }
                },
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
                
                drawCallback: function(dataServer) {
                    console.error(dataServer.json);
                    var json = dataServer.json;
                    $('#totalSale').text(json.totalSale);
                    $('#totalProduct').text(json.productCount);
                    $('#totalAmount').text(json.totalAmount);
                    $('#totalProfit').text(json.totalProfit);

                    // Update top-selling product 
                    if (json.mostSoldProducts !== undefined) {
                        const countMostSoldProducts = json.mostSoldProducts.length;
                        if(countMostSoldProducts>0){
                            $('#mostText').fadeIn()
                            $('#mostSoldProductsTable').fadeIn()
                        }else{
                            $('#mostText').fadeOut()
                            $('#mostSoldProductsTable').fadeOut()
                        }
                        
                        let mostSoldProductsContainer = $('#mostSoldProductsTable tbody');
                        mostSoldProductsContainer.empty(); // Effacer les anciennes données

                        json.mostSoldProducts.forEach((productDetail, index) => {
                            let product = productDetail.product;
                            let row = `
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <div class="position-relative mb-2">
                                                <div class="bg-position-center bg-size-cover bg-repeat-no-repeat w-80px h-60px"
                                                    style="background-image: url(${product ? "{{ asset('images') }}/" + product.image : ''});">
                                                </div>
                                                <div class="position-absolute top-0 start-0">
                                                    <span class="badge bg-theme text-theme-900 rounded-0 d-flex align-items-center justify-content-center w-20px h-20px">${index + 1}</span>
                                                </div>
                                            </div>
                                            <div class="flex-1 ps-3">
                                                <div class="fw-500 text-inverse">${product ? product.name : 'Produit supprimé'}</div>
                                                ${product ? product.price + ' FCFA' : ''}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <table class="mb-2">
                                            <tr>
                                                <td class="pe-3">QTY:</td>
                                                <td class="text-inverse text-opacity-75 fw-500">${productDetail.total_quantity}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            `;
                            mostSoldProductsContainer.append(row);
                        });
                    }
                    // css
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

            //Search absent by class and different date
            $('#submit').click(function(e) {
                $('#submitText').fadeOut();
                $('#loader').fadeIn();

                // get date
                var daterange = $('#reportrange').val();
                if (daterange) {
                    // Extract dates
                    var dates = daterange.split(' - ');
                    var date1 = moment(dates[0], 'DD-MM-YYYY');
                    var date2 = moment(dates[1], 'DD-MM-YYYY');

                    // alert(date1+'-'+date2)

                    // Vérifier si la date de début est après la date de fin
                    if (date1.isAfter(date2)) {
                        $('#loader').fadeOut();
                        $('#submitText').show();

                        // Show alert error
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: "error",
                            title: "Erreur de date",
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            text: 'La date de début doit être inférieure ou égale à la date de fin !',
                        });
                    }
                }
                $('#submitText').fadeIn();
                $('#loader').fadeOut();

                // Refresh DataTable
                Datatable.draw();
            });

            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    "Ajourd'hui": [moment(), moment()],
                    'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 derniers jours': [moment().subtract(6, 'days'), moment()],
                    '30 derniers jours': [moment().subtract(29, 'days'), moment()],
                    'Ce mois': [moment().startOf('month'), moment().endOf('month')],
                    'Mois passé': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'DD-MM-YYYY',
                    customRangeLabel: "Choisir votre date",
                    applyLabel: "Appliquer",
                    cancelLabel: "Annuler",
                    fromLabel: "De",
                    toLabel: "À",
                    daysOfWeek: moment.weekdaysMin(), // Jours abrégés
                    monthNames: moment.months(),     // Noms des mois
                    firstDay: 1                      // Lundi comme premier jour de la semaine
                }
            }, cb);
            cb(start, end);

            $('body').on('click', '.view', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('pos/sale')}}/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    }
                });
                $('#showModal').modal('show');
            });
        }); 
    </script>
@endsection