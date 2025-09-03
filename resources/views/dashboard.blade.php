@extends('layouts.layout')
{{-- css --}}
@push('css-scripts')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('content')
<div class="row">

    <div class="col-xl-3 col-lg-6">
        <a href="{{ route('category.index') }}">
            <div class="card border-color mb-3">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-0">CATEGORIES</span>
                        <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                            <i class="bi bi-fullscreen"></i></a> -->
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-7">
                            <h3 class="mb-0">{{$Category->count()}}</h3>
                        </div>
                        <div class="col-5">
                            <div class="mt-n2" data-render="apexchart" data-type="bar" data-title="Visitors"
                                data-height="30"></div>
                        </div>
                    </div>
                    <div class="small text-inverse text-opacity-50 text-truncate">
                        <!-- <i class="fa fa-chevron-up fa-fw me-1"></i> 33.3% more than last week<br>
                        <i class="far fa-user fa-fw me-1"></i> 45.5% new visitors<br>
                        <i class="far fa-times-circle fa-fw me-1"></i> 3.25% bounce rate -->
                    </div>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </a>
    </div>


    <div class="col-xl-3 col-lg-6">
        <a href="{{ route('product.index') }}">
            <div class="card border-color mb-3">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">PRODUITS </span>
                        <!-- <a href="#" data-toggle="card-expand"
                            class="text-inverse text-opacity-50 text-decoration-none"><i
                                class="bi bi-fullscreen"></i></a> -->
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-7">
                            <h3 class="mb-0">{{$Product->count()}}</h3>
                        </div>
                        <div class="col-5">
                            <div class="mt-n2" data-render="apexchart" data-type="bar" data-title="Visitors"
                                data-height="30"></div>
                        </div>
                    </div>
                    <div class="small text-inverse text-opacity-50 text-truncate">
                        <!-- <i class="fa fa-chevron-up fa-fw me-1"></i> 20.4% more than last week<br>
                        <i class="fa fa-shopping-bag fa-fw me-1"></i> 33.5% new orders<br>
                        <i class="fa fa-dollar-sign fa-fw me-1"></i> 6.21% conversion rate -->
                    </div>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card border-color mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">VENTES</span>
                    <a href="#" data-toggle="card-expand"
                        class="text-inverse text-opacity-50 text-decoration-none"><i
                            class="bi bi-fullscreen"></i></a>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-7">
                        <h3 class="mb-0">{{$Sale->count()}}</h3>
                    </div>
                    <div class="col-5">
                        <div class="mt-n3 mb-n2" data-render="apexchart" data-type="bar"
                            data-title="Visitors" data-height="45"></div>
                    </div>
                </div>
                <div class="small text-inverse text-opacity-50 text-truncate">
                    <!-- <i class="fa fa-chevron-up fa-fw me-1"></i> 59.5% more than last week<br>
                    <i class="fab fa-facebook-f fa-fw me-1"></i> 45.5% from facebook<br>
                    <i class="fab fa-youtube fa-fw me-1"></i> 15.25% from youtube -->
                </div>
            </div>
            <div class="card-arrow">
                <div class="card-arrow-top-left"></div>
                <div class="card-arrow-top-right"></div>
                <div class="card-arrow-bottom-left"></div>
                <div class="card-arrow-bottom-right"></div>
            </div>
        </div>
    </div>


    <div class="col-xl-3 col-lg-6">
        <div class="card border-color mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">BENEFICES</span>
                    <a href="#" data-toggle="card-expand"
                        class="text-inverse text-opacity-50 text-decoration-none"><i
                            class="bi bi-fullscreen"></i></a>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-7">
                        <h3 class="mb-0">{{$sale_total_profit}} FCFA</h3>
                    </div>
                    <div class="col-5">
                        <div class="mt-n3 mb-n2" data-render="apexchart" data-type="bar"
                            data-title="Visitors" data-height="45"></div>
                    </div>
                </div>
                <div class="small text-inverse text-opacity-50 text-truncate">
                    <!-- <i class="fa fa-chevron-up fa-fw me-1"></i> 5.3% more than last week<br>
                    <i class="far fa-hdd fa-fw me-1"></i> 10.5% from total usage<br>
                    <i class="far fa-hand-point-up fa-fw me-1"></i> 2MB per visit -->
                </div>
            </div>
            <div class="card-arrow">
                <div class="card-arrow-top-left"></div>
                <div class="card-arrow-top-right"></div>
                <div class="card-arrow-bottom-left"></div>
                <div class="card-arrow-bottom-right"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">
                        SERVER STATS
                        <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </span>
                    <div class="card-body">
                        <div class="row item-align-left">
                            <label class="form-label"><h6>Choisir la date</h6></label>
                            <!-- <input id="reportrange" class="btn btn-outline-theme d-flex align-items-center text-start"> -->
                            <input id="reportrange" type="text" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="ratio ratio-21x9 mb-0">
                    {{-- <div id="chart-server"></div> --}}
                    <div id="top-products-chart"></div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        {{-- <div class="d-flex align-items-center">
                            <div class="w-50px h-50px">
                                <div data-render="apexchart" data-type="donut" data-title="Visitors"
                                    data-height="50"></div>
                            </div>
                            <div class="ps-3 flex-1">
                                <div class="fs-10px fw-bold text-inverse text-opacity-50 mb-1">DISK USAGE
                                </div>
                                <div class="mb-2 fs-5 text-truncate">20.04 / 256 GB</div>
                                <div class="progress h-3px bg-secondary-transparent-2 mb-1">
                                    <div class="progress-bar bg-theme" style="width: 20%"></div>
                                </div>
                                <div class="fs-11px text-inverse text-opacity-50 mb-2 text-truncate">
                                    Last updated 1 min ago
                                </div>
                                <div class="d-flex align-items-center small">
                                    <i class="bi bi-circle-fill fs-6px me-2 text-theme"></i>
                                    <div class="flex-1">DISK C</div>
                                    <div>19.56GB</div>
                                </div>
                                <div class="d-flex align-items-center small">
                                    <i class="bi bi-circle-fill fs-6px me-2 text-theme text-opacity-50"></i>
                                    <div class="flex-1">DISK D</div>
                                    <div>0.50GB</div>
                                </div>
                            </div>
                        </div> --}}
                    </div>

                    <div class="col-lg-6">
                        {{-- <div class="d-flex">
                            <div class="w-50px pt-3">
                                <div data-render="apexchart" data-type="donut" data-title="Visitors"
                                    data-height="50"></div>
                            </div>
                            <div class="ps-3 flex-1">
                                <div class="fs-10px fw-bold text-inverse text-opacity-50 mb-1">BANDWIDTH
                                </div>
                                <div class="mb-2 fs-5 text-truncate">83.76GB / 10TB</div>
                                <div class="progress h-3px bg-secondary-transparent-2 mb-1">
                                    <div class="progress-bar bg-theme" style="width: 10%"></div>
                                </div>
                                <div class="fs-11px text-inverse text-opacity-50 mb-2 text-truncate">
                                    Last updated 1 min ago
                                </div>
                                <div class="d-flex align-items-center small">
                                    <i class="bi bi-circle-fill fs-6px me-2 text-theme"></i>
                                    <div class="flex-1">HTTP</div>
                                    <div>35.47GB</div>
                                </div>
                                <div class="d-flex align-items-center small">
                                    <i class="bi bi-circle-fill fs-6px me-2 text-theme text-opacity-50"></i>
                                    <div class="flex-1">FTP</div>
                                    <div>1.25GB</div>
                                </div>
                            </div>
                        </div> --}}
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
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">TOP PRODUITS VENDUS</span>
                    <a href="#" data-toggle="card-expand"
                        class="text-inverse text-opacity-50 text-decoration-none"><i
                            class="bi bi-fullscreen"></i></a>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="w-100 mb-0 small align-middle text-nowrap">
                        <tbody>
                            @php
                                $n = 1;
                            @endphp
                            @foreach($mostSoldProducts as $productDetail)
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <div class="position-relative mb-2">
                                                <div class="bg-position-center bg-size-cover bg-repeat-no-repeat w-80px h-60px"
                                                    style="background-image: url({{ asset('images/' . $productDetail->product->image) }});">
                                                </div>
                                                <div class="position-absolute top-0 start-0">
                                                    <span
                                                        class="badge bg-theme text-theme-900 rounded-0 d-flex align-items-center justify-content-center w-20px h-20px">{{$n++}}</span>
                                                </div>
                                            </div>
                                            <div class="flex-1 ps-3">
                                                <div class="fw-500 text-inverse">{{ $productDetail->product->name ?? 'Produit supprimé' }}</div>
                                                {{ $productDetail->product->price }} FCFA
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <table class="mb-2">
                                            <tr>
                                                <td class="pe-3">QTY:</td>
                                                <td class="text-inverse text-opacity-75 fw-500">{{ $productDetail->total_quantity }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $mostSoldProducts->links('pagination::bootstrap-4')}}
            </div>

            <div class="card-arrow">
                <div class="card-arrow-top-left"></div>
                <div class="card-arrow-top-right"></div>
                <div class="card-arrow-bottom-left"></div>
                <div class="card-arrow-bottom-right"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex fw-bold small mb-3">
                    <span class="flex-grow-1">ACTIVITES</span>
                    <a href="#" data-toggle="card-expand"
                        class="text-inverse text-opacity-50 text-decoration-none"><i
                            class="bi bi-fullscreen"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-borderless mb-2px small text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fonction</th>
                                <th>Acteur</th>
                                <th>Action</th>
                                <th>Creer le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($Action as $action)
                                <tr>
                                    <td>
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-circle-fill fs-6px text-theme me-2"></i>
                                        </span>
                                    </td>
                                    <td>{{$action->function}}</td>
                                    <td><span class="badge d-block bg-theme text-theme-900 rounded-0 pt-5px w-70px"style="min-height: 18px;">
                                        {{$action->user?$action->user->name:'-'}}</span>
                                    </td>
                                    <td>{{$action->user?$action->text:'-'}}</td>
                                    <td>{{$action->user?$action->created_at->format('d-m-Y H:i:s'):'-'}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $Action->links('pagination::bootstrap-4')}}
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

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(function() {
        // Configurer Moment.js en français
        moment.locale('fr');
        
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

        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }
        // console.log(getCookie('app-theme'));

        const theme = getCookie('app-theme'); // Ex: "theme-red"
        let primaryColor;

        const colors = {
            primary: '#0d6efd',
            secondary: '#6c757d',
            success: '#198754',
            danger: '#dc3545',
            warning: '#FFA500',
            info: '#0dcaf0',
            light: '#f8f9fa',
            yellow: '#ffc107',
            pink: '#d63384',
            purple: '#6f42c1',
            indigo: '#6610f2',
        };

        // Définis les couleurs selon le thème
        switch (theme) {
            case 'theme-pink':primaryColor = colors.pink;break;   case 'theme-red':primaryColor = colors.danger;break;
            case 'theme-warning':primaryColor = colors.warning;break;  case 'theme-yellow':primaryColor = colors.yellow;break;
            case 'theme-green':primaryColor = colors.success;break;  case 'theme-info':primaryColor = colors.info;break;
            case 'theme-primary':primaryColor = colors.primary;break;  case 'theme-purple':primaryColor = colors.purple;break;
            case 'theme-indigo':primaryColor = colors.indigo;break;  case 'theme-gray-200':primaryColor = colors.secondary;break;
            // case 'theme-blue':primaryColor = colors.danger;break;  case 'theme-blue':primaryColor = colors.danger;break;
            default:
                primaryColor = '#333333'; // Couleur par défaut
        }

        $('#reportrange').on('apply.daterangepicker', function () {
            updateChart();
        });

        // Déclarez `chart` en dehors de la fonction pour conserver son état
        let chart = null;

        function updateChart() {
            // Récupérer la plage de dates
            const daterange = $('#reportrange').val();

            if (daterange) {
                // Extraire les dates
                const dates = daterange.split(' - ');
                const date1 = moment(dates[0], 'DD-MM-YYYY');
                const date2 = moment(dates[1], 'DD-MM-YYYY');

                // Validation des dates
                if (date1.isAfter(date2)) {
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: "Erreur de date",
                        showConfirmButton: false,
                        timer: 5000,
                        text: 'La date de début doit être inférieure ou égale à la date de fin !',
                    });
                    return;
                }
            }

            // Envoi des données au serveur
            fetch("{{ route('statistics.topProducts') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ daterange })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Data received:", data);

                const productNames = data.map(item => item.name);
                const quantities = data.map(item => item.total_quantity);

                // Initialisation ou mise à jour du graphique
                if (!chart) {
                    // Initialisation
                    const options = {
                        series: [{
                            name: 'Quantité Vendue',
                            data: quantities
                        }],
                        chart: {
                            type: 'bar',
                            height: 200,
                            responsive: [{
                                breakpoint: 768, // Pour les écrans < 768px
                                options: {
                                    chart: {
                                        height: 150 // Ajuste la hauteur
                                    },
                                    plotOptions: {
                                        bar: {
                                            columnWidth: '30%' // Réduit la largeur des colonnes
                                        }
                                    },
                                    legend: {
                                        position: 'bottom' // Déplace la légende
                                    }
                                }
                            }]
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '10%',
                                endingShape: 'rounded'
                            }
                        },
                        colors: [primaryColor],
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 2, colors: ['transparent'] },
                        xaxis: { categories: productNames },
                        yaxis: { 
                            title: { text: 'Quantité' ,
                                style: {
                                    color: '#ffffff', // Définit la couleur en blanc
                                    fontSize: '14px', // Optionnel : ajuste la taille de la police
                                    fontWeight: 'bold', // Optionnel : rend le texte gras
                                }
                            } 
                        },
                        fill: { opacity: 1 },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return val + "";
                                }
                            }
                        }
                    };

                    chart = new ApexCharts(document.querySelector("#top-products-chart"), options);
                    chart.render();
                } else {
                    // Mise à jour
                    chart.updateOptions({
                        series: [{
                            name: 'Quantité Vendue',
                            data: quantities
                        }],
                        xaxis: {
                            categories: productNames
                        }
                    });
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        updateChart()

        window.addEventListener('resize', () => {
            if (chart) {
                chart.resize();
            }
        });

        // Hover effect
        $('.border-color').hover(
            function() {
                $(this).addClass('border-color-change');
            },
            function() {
                $(this).removeClass('border-color-change');
            }
        );

        // Click effect
        $('.product_list .pos-product').on('click', function(e) {
            e.preventDefault();
            
            // Removes the click effect of other products
            $('.product_list .pos-product').removeClass('product-clicked');
            
            // Add the click effect of other products
            $(this).addClass('product-clicked');
        });
    });
</script>
@endsection