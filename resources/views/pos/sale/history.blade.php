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
            <div class="col-xl-3 col-lg-3 ">
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
            <div class="col-xl-3 col-lg-3 ">
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
            <div class="col-xl-3 col-lg-3 ">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-0"><h5><strong>Chiifre d'affaire</strong><h5></span>
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
            @if ($canViewFinancials)
            <div class="col-xl-3 col-lg-3 ">
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
            @endif

            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            HISTORIQUE DES VENTES
                        </h1>
                        <div class="accordion mt-3" id="salesFilterAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="salesFilterHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#salesFilterCollapse" aria-expanded="false" aria-controls="salesFilterCollapse">
                                        <i class="bi bi-funnel me-2"></i>Filtrer les ventes et le classement des produits
                                    </button>
                                </h2>
                                <div id="salesFilterCollapse" class="accordion-collapse collapse" aria-labelledby="salesFilterHeading">
                                    <div class="accordion-body">
                                        <form id="searchForm">
                                            @csrf
                                            <div class="row g-3 align-items-end">
                                                <div class="col-12 col-lg-4">
                                                    <label for="reportrange" class="form-label">Période</label>
                                                    <input id="reportrange" type="text" class="form-control">
                                                </div>
                                                <div class="col-12 col-md-6 col-lg-3">
                                                    <label for="historyClient" class="form-label">Client</label>
                                                    <select id="historyClient" class="form-select" data-placeholder="Tous les clients">
                                                        <option value="">Tous les clients</option>
                                                        @foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6 col-lg-3">
                                                    <label for="historySupplier" class="form-label">Fournisseur</label>
                                                    <select id="historySupplier" class="form-select" data-placeholder="Tous les fournisseurs">
                                                        <option value="">Tous les fournisseurs</option>
                                                        @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 col-lg-2 d-grid gap-2">
                                                    <button id="submit" type="button" class="btn btn-info" data-loading-text="Filtrage…">
                                                        <i class="bi bi-search me-1"></i>Filtrer
                                                    </button>
                                                    <button id="resetHistoryFilters" type="button" class="btn btn-outline-secondary btn-sm">Réinitialiser</button>
                                                </div>
                                            </div>
                                            <p class="small text-muted mt-3 mb-0">Le tableau, les statistiques, les exports et le classement des produits utiliseront les mêmes critères.</p>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="mb-4">

                        <div class="col-xl-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 flex-wrap fw-bold small mb-3">
                                        <span class="me-auto"><h4 class="mb-0">Listes des ventes</h4></span>
                                        <!-- <button type="button" class="btn btn-primary mb-1 me-3 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button> -->
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="accordion mb-3" id="salesExportAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="salesExportHeading">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#salesExportCollapse" aria-expanded="false" aria-controls="salesExportCollapse">
                                                    <i class="fas fa-download me-2"></i>Exporter les ventes affichées
                                                </button>
                                            </h2>
                                            <div id="salesExportCollapse" class="accordion-collapse collapse" data-bs-parent="#salesExportAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-12 col-sm-4"><button type="button" data-format="csv" class="exportSalesTabular btn btn-warning text-dark w-100"><i class="fas fa-file-csv me-1"></i>CSV</button></div>
                                                        <div class="col-12 col-sm-4"><button type="button" data-format="excel" class="exportSalesTabular btn btn-success w-100"><i class="fas fa-file-excel me-1"></i>Excel</button></div>
                                                        <div class="col-12 col-sm-4"><button type="button" id="exportSalesPdf" class="btn btn-primary w-100"><i class="fas fa-file-pdf me-1"></i>PDF</button></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="datatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Code</th>
                                                    <th>Chiifre d'affaire</th>
                                                    <th>Remise</th>
                                                    @if ($canViewFinancials)<th>Profit total</th>@endif
                                                    <th>Client</th>
                                                    <th>Date</th>
                                                    <th>Caissier</th>
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
                    </div>
                    <hr class="mb-4 bg-light" style="opacity: 0.9">

                    <div class="col-12 mt-3">
                        <table id="mostSoldProductsTable" class="table table-striped">
                            <h4 class="border" id="mostText">Produits les plus vendus</h4>
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
                        d.client_id = $('#historyClient').val();
                        d.supplier_id = $('#historySupplier').val();
                    }
                },
                columns: [
                    {data: 'id',name: 'id'},
                    {data: 'code',name: 'code'},
                    {data: 'total_amount',name: 'total_amount'},
                    {data: 'discount',name: 'discount'},
                    @if ($canViewFinancials)
                    {data: 'total_profit',name: 'total_profit'},
                    @endif
                    {data: 'client',name: 'client'},
                    {data: 'created_at',name: 'created_at'},
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
                    @if ($canViewFinancials)
                    $('#totalProfit').text(json.totalProfit);
                    @endif

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
                                                ${product ? ((product.price_ttc && product.price_ttc > 0 ? product.price_ttc : product.price) + ' FCFA') : ''}
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
                const button = this;
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
                        return;
                    }
                }

                const reloadRequest = new Promise(function(resolve, reject) {
                    Datatable.one('xhr.dt.historyFilter', function(event, settings, json, xhr) {
                        if (!json || (xhr && xhr.status >= 400) || json.error) {
                            reject(new Error(json?.error || 'Impossible de charger l’historique.'));
                            return;
                        }
                        resolve(json);
                    });
                    Datatable.ajax.reload(null, true);
                });

                window.ServerButtonLoader.withLoader(button, reloadRequest, 'Chargement…')
                    .catch(function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Chargement impossible',
                            text: error.message || 'Impossible de charger l’historique des ventes.',
                            confirmButtonText: "D'accord"
                        });
                    });
            });

            $('#exportSalesPdf').on('click', function() {
                const tableInfo = Datatable.page.info();
                if (!tableInfo || tableInfo.recordsDisplay === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Aucune donnée à exporter',
                        text: 'Aucune vente ne correspond aux filtres sélectionnés.',
                        confirmButtonText: "D'accord",
                        confirmButtonColor: '#0dcaf0'
                    });
                    return;
                }

                const params = new URLSearchParams({
                    daterange: $('#reportrange').val(),
                    client_id: $('#historyClient').val(),
                    supplier_id: $('#historySupplier').val(),
                    search: Datatable.search()
                });

                window.location.href = "{{ route('history.export.pdf') }}?" + params.toString();
            });

            $('.exportSalesTabular').on('click', function() {
                const button = this;
                const params = new URLSearchParams({
                    daterange: $('#reportrange').val(),
                    client_id: $('#historyClient').val(),
                    supplier_id: $('#historySupplier').val(),
                    search: Datatable.search()
                });
                const baseUrl = "{{ route('history.export.tabular', ['format' => '__FORMAT__']) }}";
                window.ServerButtonLoader.download(button, baseUrl.replace('__FORMAT__', button.dataset.format) + '?' + params.toString())
                    .catch(error => Swal.fire({icon: 'error', title: 'Export impossible', text: error.message}));
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

            $('#resetHistoryFilters').on('click', function () {
                const button = this;
                $('#historyClient, #historySupplier').val('').trigger('change');
                const picker = $('#reportrange').data('daterangepicker');
                picker.setStartDate(moment().subtract(29, 'days'));
                picker.setEndDate(moment());
                const resetRequest = new Promise(function(resolve, reject) {
                    Datatable.one('xhr.dt.historyReset', function(event, settings, json, xhr) {
                        if (!json || (xhr && xhr.status >= 400) || json.error) {
                            reject(new Error(json?.error || 'Impossible de réinitialiser les filtres.'));
                            return;
                        }
                        resolve(json);
                    });
                    Datatable.ajax.reload(null, true);
                });
                window.ServerButtonLoader.withLoader(button, resetRequest, 'Réinitialisation…')
                    .catch(error => Swal.fire({icon: 'error', title: 'Réinitialisation impossible', text: error.message}));
            });

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

            $('body').on('click', '.pdf', function () {
                var id = $(this).data("id");
                window.location.href = 'sale/invoice/' + id + '/pdf';
            });

            let invoiceWhatsappQuota = {{ (int) ($company?->whatsapp_count ?? 0) }};
            let invoiceSmsQuota = {{ (int) ($company?->sms_count ?? 0) }};
            const invoiceWhatsappAuthorized = {{ $company?->invoice_whatsapp_enabled ? 'true' : 'false' }};
            const invoiceSmsAuthorized = {{ $company?->invoice_sms_enabled ? 'true' : 'false' }};

            $('body').on('click', '.deliver-invoice', function () {
                const saleId = $(this).data('id');
                const clientPhone = $(this).data('phone') || '';
                const clientCountry = $(this).data('country') || '{{ $company->country_code ?? 'TG' }}';
                Swal.fire({
                    title: 'Envoyer la facture',
                    html: `<input id="deliveryPhone" type="tel" inputmode="numeric" minlength="6" maxlength="15" class="swal2-input" value="${String(clientPhone).replace(/"/g, '&quot;')}" placeholder="Numéro local sans indicatif">
                        <select id="deliveryCountry" class="swal2-select">@foreach(config('african_countries') as $iso => $countryName)<option value="{{ $iso }}">{{ $countryName }} ({{ $iso }})</option>@endforeach</select>
                        <div class="d-flex justify-content-center gap-4 mt-3">
                            <div class="form-check form-switch"><input id="deliveryWhatsapp" class="form-check-input" type="checkbox" ${invoiceWhatsappAuthorized && invoiceWhatsappQuota > 0 ? 'checked' : 'disabled'}><label class="form-check-label" for="deliveryWhatsapp">WhatsApp (${invoiceWhatsappQuota})</label></div>
                            <div class="form-check form-switch"><input id="deliverySms" class="form-check-input" type="checkbox" ${invoiceSmsAuthorized && invoiceSmsQuota > 0 ? '' : 'disabled'}><label class="form-check-label" for="deliverySms">SMS (${invoiceSmsQuota})</label></div>
                        </div>
                        ${!invoiceWhatsappAuthorized && !invoiceSmsAuthorized ? `<div class="small text-warning mt-3">Activez WhatsApp ou SMS dans la section « Envoi des factures aux clients » de Communications &gt; SMS &amp; WhatsApp &gt; Configuration.@if($currentMembership?->hasPermission('notifications.manage')) <a href="{{ route('notifications.index') }}" class="text-warning text-decoration-underline">Ouvrir la configuration</a>@endif</div>` : ''}`,
                    showCancelButton: true,
                    confirmButtonText: 'Envoyer',
                    cancelButtonText: 'Annuler',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    allowEscapeKey: () => !Swal.isLoading(),
                    didOpen: () => {
                        const popup = Swal.getPopup();
                        $('#deliveryCountry').val(clientCountry).select2({
                            width: '100%',
                            dropdownParent: $(popup),
                            placeholder: 'Rechercher un pays',
                            minimumResultsForSearch: 0
                        });
                    },
                    preConfirm: async () => {
                        const phone = document.getElementById('deliveryPhone').value.trim();
                        const country_code = document.getElementById('deliveryCountry').value;
                        const whatsapp = document.getElementById('deliveryWhatsapp').checked;
                        const sms = document.getElementById('deliverySms').checked;
                        if (!phone || (!whatsapp && !sms)) {
                            Swal.showValidationMessage('Saisissez un numéro et choisissez au moins un canal.');
                            return false;
                        }
                        try {
                            const url = '{{ route('sale.send-invoice', ['sale' => '__SALE__']) }}'.replace('__SALE__', saleId);
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                                body: JSON.stringify({phone, country_code, whatsapp, sms})
                            });
                            const data = await response.json();
                            invoiceWhatsappQuota = Number(data.whatsappQuota ?? invoiceWhatsappQuota);
                            invoiceSmsQuota = Number(data.smsQuota ?? invoiceSmsQuota);
                            if (!response.ok || !data.status) throw new Error(data.message || 'Envoi impossible.');
                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(error.message || 'Envoi impossible.');
                            return false;
                        }
                    }
                }).then(result => {
                    if (result.isConfirmed) Swal.fire({icon: 'success', title: 'Facture envoyée', text: result.value.message});
                });
            });
        }); 
    </script>
@endsection
