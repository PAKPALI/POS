@extends('layouts.layout_sale')
@push('css-scripts')
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }

    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }

    #pdfModal .modal-dialog {
        max-width: 760px;
    }

    #pdfModal .modal-content {
        height: min(92vh, 900px);
    }

    #pdfModal .modal-body {
        overflow: auto;
        padding: 1rem;
        background: #e9ecef;
    }

    #pdfModal .modal-body canvas {
        display: block;
        width: auto;
        max-width: 100%;
        height: auto !important;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .18);
    }

    .pos-product-flyer {
        position: fixed;
        z-index: 2147483000;
        pointer-events: none;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, .9);
        border-radius: 18px;
        background-color: rgba(255, 255, 255, .92);
        background-position: center;
        background-size: cover;
        box-shadow:
            0 18px 38px rgba(0, 0, 0, .5),
            0 0 0 5px rgba(13, 202, 240, .28);
        filter: saturate(.9) contrast(1.08);
        will-change: transform, opacity, filter;
        transform-origin: center center;
    }

    .pos-product-click-origin {
        position: fixed;
        z-index: 2147483001;
        width: 12px;
        height: 12px;
        margin: -6px 0 0 -6px;
        pointer-events: none;
        border: 2px solid #fff;
        border-radius: 50%;
        background: #0dcaf0;
        box-shadow: 0 0 0 0 rgba(13, 202, 240, .7);
        animation: product-click-origin .28s ease-out forwards;
    }

    @keyframes product-click-origin {
        to {
            opacity: 0;
            box-shadow: 0 0 0 18px rgba(13, 202, 240, 0);
            transform: scale(.35);
        }
    }

    .cart-receive-pulse,
    #orderCount.cart-receive-pulse {
        animation: cart-receive-pulse .38s ease-out;
    }

    @keyframes cart-receive-pulse {
        50% {
            color: #fff;
            text-shadow: 0 0 12px #0dcaf0;
            transform: scale(1.5);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .cart-receive-pulse,
        #orderCount.cart-receive-pulse {
            animation: none;
        }
    }

    @media (max-width: 575.98px) {
        .pos .pos-content-container {
            padding: .5rem !important;
        }

        #product_list {
            --bs-gutter-x: .5rem;
        }

        #product_list .product_list {
            padding-bottom: .5rem !important;
        }

        #product_list .card-body.products {
            padding: 2px !important;
        }

        #product_list .pos-product .img {
            height: 105px !important;
            min-height: 105px;
        }

        #product_list .pos-product .info {
            padding: .45rem .35rem;
        }

        #product_list .pos-product .info .title,
        #product_list .pos-product .info .price {
            font-size: .72rem;
            line-height: 1.25;
        }

        #product_list .pos-product:hover,
        #product_list .pos-product.product-hover {
            transform: none;
        }

        #pdfModal .modal-dialog {
            width: 100%;
            max-width: none;
            height: 100%;
            margin: 0;
        }

        #pdfModal .modal-content {
            height: 100%;
            border: 0;
            border-radius: 0;
        }

        #pdfModal .modal-body {
            padding: .5rem;
        }

        #pdfModal .modal-footer {
            flex-wrap: nowrap;
        }

        #pdfModal .modal-footer .btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<style>
    /* Liste déroulante des clients : fond blanc, nom en noir,
       survol bleu avec texte blanc */
    .select2-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #ced4da !important;
    }
    .select2-container--default .select2-results__option {
        color: #000000 !important;
        background-color: #ffffff !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #1e88e5 !important;
        color: #ffffff !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        color: #000000 !important;
        background-color: #ffffff !important;
        border: 1px solid #ced4da !important;
    }
</style>
<div id="content" class="app-content p-1 ps-xl-4 pe-xl-4 pt-xl-3 pb-xl-3">

    <div class="pos card" id="pos">
        <div class="pos-container card-body">
            <!-- Menu -->
            <div class="pos-menu">
                <!-- logo -->
                <div class="logo">
                    <a href="{{ route('dashboard') }}">
                        <div class="logo-img"><i class="bi bi-x-diamond" style="font-size: 2.1rem;"></i></div>
                        <div class="logo-text">{{config('app.name')}}</div>
                    </a>
                </div>
                <!-- Menu body-->
                <div class="nav-container">
                    <div data-scrollbar="true" data-height="100%" data-skip-mobile="true">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-filter="all" data-category-id="">
                                    <div class="card">
                                        <div class="card-body">
                                            <i class="fa fa-th-large"></i> Tous
                                    ( <span>{{ $productCount }}</span> )
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

                            @foreach($Category->where('status',1) as $category)
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-filter="category" data-category-id="{{ $category->id }}">
                                    <div class="card">
                                        <div class="card-body">
                                            <i class="fa fa-tags"></i> {{$category->name}}
                                            ( <span>{{ $category->available_products_count }}</span> )
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
                <div class="pos-content-container h-100 p-4 text-center" data-scrollbar="true" data-height="100%">
                    <!-- search product -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <input type="text" id="searchProduct" class="form-control" placeholder="Rechercher un produit...">
                        </div>
                    </div>

                    <div class="row gx-4 text-center" id="product_list">
                        <!-- statistics of sale -->

                        <!-- sale total daily-->
                        <h3><strong class="sale_list">Statistiques des ventes cette journée</strong></h3>
                        <div class="row sale_list mb-5">
                            <!-- total sale -->
                            
                            <div class="{{ $canViewFinancials ? 'col-xl-3' : 'col-xl-4' }} col-lg-6 ">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex fw-bold small mb-3">
                                            <span class="flex-grow-0"><h5><strong>Total des Ventes</strong><h5></span>
                                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                                <i class="bi bi-fullscreen"></i></a> -->
                                        </div>
                                        <div class="row align-items-center mb-2">
                                            <div class="col-7">
                                                <h3 class="mb-0">{{ $saleCount }}</h3>
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
                            <div class="{{ $canViewFinancials ? 'col-xl-3' : 'col-xl-4' }} col-lg-6 ">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex fw-bold small mb-3">
                                            <span class="flex-grow-0"><h5><strong>Total des produits</strong><h5></span>
                                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                                <i class="bi bi-fullscreen"></i></a> -->
                                        </div>
                                        <div class="row align-items-center mb-2">
                                            <div class="col-7">
                                                <h3 class="mb-0">{{$product_count}}</h3>
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
                            <div class="{{ $canViewFinancials ? 'col-xl-3' : 'col-xl-4' }} col-lg-6 ">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex fw-bold small mb-3">
                                            <span class="flex-grow-0"><h5><strong>Somme totale</strong><h5></span>
                                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                                <i class="bi bi-fullscreen"></i></a> -->
                                        </div>
                                        <div class="row align-items-center mb-2">
                                            <div class="col-7">
                                                <h3 class="mb-0">{{$total_amount}}</h3>
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
                                 <div class="col-xl-3 col-lg-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex fw-bold small mb-3">
                                                <span class="flex-grow-0"><h5><strong>Bénefice journalier</strong><h5></span>
                                                <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                                    <i class="bi bi-fullscreen"></i></a> -->
                                            </div>
                                            <div class="row align-items-center mb-2">
                                                <div class="col-7">
                                                    <h3 class="mb-0">{{$sale_total_profit}}</h3>
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
                        </div>

                        <!-- sale list daily-->
                        <h3><strong class="sale_list">Liste des ventes effectuées cette journée avec les détails</strong></h3>
                        <div class="card sale_list">
                            <div class="card-body">
                                <table id="datatable" class="table text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Montant reçu</th>
                                            <th>Montant payé</th>
                                            <th>Monnaie rendue</th>
                                            @if ($canViewFinancials)<th>Profit total</th>@endif
                                            <th>Code promo</th>
                                            <th>Remise</th>
                                            <th>Client</th>
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
                        
                        <!-- search loader -->
                        <div id="search_loader" class="text-center my-3" style="display:none;">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>

                        <div id="catalogProducts" class="row gx-4 text-center w-100 m-0"></div>
                        <div id="catalogEmpty" class="col-12 py-5 text-muted" style="display:none;">
                            Aucun produit disponible pour ce filtre.
                        </div>
                        <div class="col-12 pb-4">
                            <button id="catalogLoadMore" type="button" class="btn btn-outline-info" data-loading-text="Chargement…" style="display:none;">
                                Charger plus de produits
                            </button>
                        </div>
                    </div>
                    <div id="loader" class="spinner-grow"></div>
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
                        <div class="title">Table de vente <marquee class="bg-dark">{{Auth::user()->name}}</marquee ></div>
                        <!-- <div class="order">Order: <b>#0056</b></div> -->
                    </div>

                    <div class="pos-sidebar-nav">
                        <ul class="nav nav-tabs nav-fill">
                            <li class="nav-item nav-sale-command">
                                <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#newOrderTab">Commande (<span id="orderCount">0</span>)</a>
                            </li>
                            <li class="nav-item nav-sale">
                                <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#orderHistoryTab">Produits vendus ({{$mostSoldProducts->count()}})</a>
                            </li>
                        </ul>
                    </div>

                    <div class="pos-sidebar-body tab-content" data-scrollbar="true" data-height="100%">
                        <div class="tab-pane fade h-100 show active" id="newOrderTab">
                            <div class="pos-order">
                                <select id="clientSelect" class="form-control mb-2">
                                    <option value="">Client de la vente (aucun)</option>
                                </select>
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
                            <div class="h-100 d-flex align-items-top justify-content-center text-center p-20">
                                <div>
                                    <!-- if product sold is verify -->
                                    @if ($mostSoldProducts->count()>0)
                                        <div class="col-12">
                                            <div class="card mb-2 mt-3">
                                                <div class="card-body">
                                                    <div class="d-flex fw-bold small mb-3">
                                                        <span class="flex-grow-1">TOP PRODUITS VENDUS AUJOURD'HUI</span>
                                                        <a href="#" data-toggle="card-expand"
                                                            class="text-inverse text-opacity-50 text-decoration-none"><i
                                                                class="bi bi-fullscreen"></i></a>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="w-100 mb-0 small align-middle text-nowrap">
                                                            <tbody>
                                                                @php
                                                                    $n = 1;
                                                                @endphp
                                                                @foreach($mostSoldProducts as $productDetail)
                                                                    @php
                                                                        $soldProductImage = $productDetail->product
                                                                            && $productDetail->product->image
                                                                            && $productDetail->product->image !== 'null'
                                                                            && file_exists(public_path('images/'.$productDetail->product->image))
                                                                                ? asset('images/'.$productDetail->product->image)
                                                                                : asset('icons/product-placeholder.svg');
                                                                    @endphp
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex">
                                                                                <div class="position-relative mb-2">
                                                                                    <div class="bg-position-center bg-size-cover bg-repeat-no-repeat w-80px h-60px"
                                                                                        style="background-image: url('{{ $soldProductImage }}');">
                                                                                    </div>
                                                                                    <div class="position-absolute top-0 start-0">
                                                                                        <span
                                                                                            class="badge bg-theme text-theme-900 rounded-0 d-flex align-items-center justify-content-center w-20px h-20px">{{$n++}}</span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="flex-1 ps-3">
                                                                                    <!-- <div class="mb-1"><small
                                                                                            class="fs-9px fw-500 lh-1 d-inline-block rounded-0 badge bg-secondary bg-opacity-25 text-inverse text-opacity-75 pt-5px">SKU90400</small>
                                                                                    </div> -->
                                                                                    <div class="fw-500 text-inverse">{{ $productDetail->product->name ?? 'Produit supprimé' }}</div>
                                                                                    {{ $productDetail->product->price_ttc ?? $productDetail->product->price }} FCFA
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <table class="mb-2">
                                                                                <tr>
                                                                                    <td class="pe-3">QTY:</td>
                                                                                    <td class="text-inverse text-opacity-75 fw-500">{{ $productDetail->total_quantity }}</td>
                                                                                </tr>
                                                                                <!-- <tr>
                                                                                    <td class="pe-3">REVENUE:</td>
                                                                                    <td class="text-inverse text-opacity-75 fw-500">$51,471</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="pe-3 text-nowrap">PROFIT:</td>
                                                                                    <td class="text-inverse text-opacity-75 fw-500">$15,441</td>
                                                                                </tr> -->
                                                                            </table>
                                                                        </td>
                                                                        <!-- <td><a href="#" class="text-decoration-none text-inverse"><iclass="bi bi-search"></i></a></td> -->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
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
                                    @else
                                        <div class="mb-3 mt-n5 no-sale">
                                            <svg width="6em" height="6em" viewBox="0 0 16 16" class="text-gray-300" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M14 5H2v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5zM1 4v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4H1z" />
                                                <path d="M8 1.5A2.5 2.5 0 0 0 5.5 4h-1a3.5 3.5 0 1 1 7 0h-1A2.5 2.5 0 0 0 8 1.5z" />
                                            </svg>
                                        </div>
                                        <h5 class="no-sale">Aucune vente effectuée</h5>
                                    @endif
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
                        <!-- <div class="bg-light"> -->
                        <!-- <img src="http://127.0.0.1:1111/storage/barcodes/75FKZVT.png" alt="Code Barre"></div> -->
                        
                        <!-- <form action=""> -->
                            
                            {{--<div class="d-flex gap-1 mb-2">
                                <input type="text" id="promoCodeInput" class="form-control" placeholder="Scannez le code promo" autofocus>
                                <button class="btn btn-danger btn-sm" id="deletpromoinput" type=""><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></button>
                            </div>--}}
                            <div class="d-flex gap-1 mb-2">
                                <input type="number" id="remiseInput" class="form-control" placeholder="Montant de la remise (FCFA)" min="0">
                                <button class="btn btn-danger btn-sm" id="deletremiseinput" type=""><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></button>
                            </div>
                        <!-- </form> -->
                        <div class="mt-3">
                            <div class="btn-group d-flex flex-wrap gap-1">
                                @if ($company && $company->count() == 1)
                                    @if(!$mainCash)
                                        <div class="alert alert-danger text-center">
                                            ⚠️ Aucune caisse principale configurée !
                                        </div>
                                        <a href="{{ route('ams.settings') }}" class="btn btn-danger w-150px">
                                            Configurer
                                        </a>
                                    @elseif($setting && $setting->default_tax > 0 && !$taxCash)
                                        <div class="alert alert-warning text-center">
                                            ⚠️ Taxe définie mais aucune caisse de taxe !
                                        </div>

                                        <a href="{{ route('ams.settings') }}" class="btn btn-warning w-150px">
                                            Configurer
                                        </a>
                                    @else
                                        <a href="#" id="savePendingOrder" class="btn btn-outline-primary rounded-0 flex-fill">
                                            <i class="bi bi-save fa-lg"></i><br>
                                            <span class="small">Sauvegarder</span>
                                        </a>
                                        <a href="#" id="showPendingOrders" class="btn btn-outline-info rounded-0 flex-fill position-relative">
                                            <i class="bi bi-clock-history fa-lg"></i><br>
                                            <span class="small">En cours</span>
                                            <span id="pendingBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="display:none;">0</span>
                                        </a>
                                        <a href="#" id="confirmSale" class="btn btn-outline-theme rounded-0 flex-fill">
                                            <i class="bi bi-send-check fa-lg"></i><br>
                                            <span class="small">Vendre</span>
                                        </a>
                                    @endif
                                @else
                                    <a href="#" class="btn btn-outline-theme rounded-0 w-150px" disabled>
                                        <span class="small">CREER UNE COMPAGNIE</span>
                                    </a>
                                @endif
                                
                                <a href="#" id="saleLoader" class="btn btn-outline-theme rounded-0 w-150px">
                                    <div id="loader" class="spinner-grow"></div>
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
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Aperçu du reçu</h5>
                    <button type="button" class="btn-close receipt-modal-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="receiptPreview">
                    <div class="py-5 text-muted">Chargement du reçu...</div>
                </div>
                <div class="px-3 pb-3 d-none" id="invoiceDeliveryPanel">
                    <div class="border rounded p-3">
                        <label for="invoiceCountry" class="form-label">Pays du numéro</label>
                        <select class="form-select country-select mb-3" id="invoiceCountry" data-placeholder="Rechercher un pays">
                            @foreach(config('african_countries') as $iso => $countryName)<option value="{{ $iso }}" @selected($iso === ($company->country_code ?? 'TG'))>{{ $countryName }} ({{ $iso }})</option>@endforeach
                        </select>
                        <label for="invoicePhone" class="form-label">Numéro local du client</label>
                        <input type="tel" class="form-control mb-3" id="invoicePhone" inputmode="numeric" pattern="[0-9]{6,15}" minlength="6" maxlength="15" placeholder="Numéro local sans indicatif">
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="invoiceWhatsapp" {{ $company->invoice_whatsapp_enabled && $company->whatsapp_count > 0 ? 'checked' : '' }} {{ !$company->invoice_whatsapp_enabled || $company->whatsapp_count < 1 ? 'disabled' : '' }}>
                                <label class="form-check-label" for="invoiceWhatsapp">WhatsApp (<span id="invoiceWhatsappQuota">{{ $company->whatsapp_count }}</span>)</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="invoiceSms" {{ !$company->invoice_whatsapp_enabled && $company->invoice_sms_enabled && $company->sms_count > 0 ? 'checked' : '' }} {{ !$company->invoice_sms_enabled || $company->sms_count < 1 ? 'disabled' : '' }}>
                                <label class="form-check-label" for="invoiceSms">SMS (<span id="invoiceSmsQuota">{{ $company->sms_count }}</span>)</label>
                            </div>
                        </div>
                        <button type="button" id="sendInvoice" class="btn btn-success" data-loading-text="Envoi en cours…" {{ (!$company->invoice_whatsapp_enabled || $company->whatsapp_count < 1) && (!$company->invoice_sms_enabled || $company->sms_count < 1) ? 'disabled' : '' }}>
                            <i class="bi bi-whatsapp me-1"></i> Envoyer la facture
                        </button>
                        @if(!$company->invoice_whatsapp_enabled && !$company->invoice_sms_enabled)
                            <div class="small text-warning mt-2">
                                L’envoi doit d’abord être autorisé dans Communications &gt; SMS &amp; WhatsApp &gt; Configuration.
                                @if($currentMembership?->hasPermission('notifications.manage'))
                                    <a href="{{ route('notifications.index') }}" class="text-warning text-decoration-underline">Ouvrir la configuration</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="print" class="btn btn-success">
                        <i class="bi bi-printer me-1"></i> Imprimer
                    </button>
                    <button type="button" class="btn btn-secondary receipt-modal-close" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
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

    <!-- Pending Orders Modal -->
    <div class="modal fade" id="pendingOrdersModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h3 class="modal-title text-dark">Commandes en cours</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="pendingOrdersList">
                        <div class="text-center text-muted py-4">Aucune commande en cours</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(function() {
        $('.sale_list').hide();
        $('#loader').hide();
        $('#saleLoader').hide();
        $('#search_loader').hide();
        const defaultPosProductImage = @json(asset('icons/product-placeholder.svg'));
        const catalogUrl = @json(route('products.search'));
        const clientSearchUrl = @json(route('clients.search'));
        const cartStorageKey = @json('pos_cart_v1_' . auth()->id() . '_' . $activeCompany->id);
        const catalogState = {
            page: 1,
            query: '',
            categoryId: '',
            hasMore: true,
            loading: false,
            request: null
        };
        let catalogSearchTimer = null;
        let lastProductPointer = null;
        let isRestoringCart = false;

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function productCard(product) {
            const image = escapeHtml(product.image_url || defaultPosProductImage);
            const name = escapeHtml(product.name);
            const price = Number(product.sale_price || product.price || 0);
            const quantity = Number(product.qte || 0);

            return `
                <div class="col-6 col-xxl-3 col-xl-4 col-lg-6 col-md-4 col-sm-6 pb-4 product_list">
                    <div class="card h-100">
                        <div class="card-body products h-100 p-1">
                            <a href="#" class="pos-product" data-bs-toggle="modal" data-bs-target="#modalPosItem"
                                data-id="${Number(product.id)}" data-name="${name}" data-price="${price}"
                                data-image="${image}" data-qte="${quantity}">
                                <div class="img" style="background-image:url('${image}');background-size:cover;background-repeat:no-repeat;background-position:center;width:100%;height:150px"></div>
                                <div class="info">
                                    <div class="title">Nom : ${name}&reg;</div>
                                    <div class="title price">Prix : ${price} FCFA</div>
                                    <div class="title qte">Quantité : ${quantity}</div>
                                </div>
                            </a>
                        </div>
                        <div class="card-arrow">
                            <div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div>
                            <div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div>
                        </div>
                    </div>
                </div>`;
        }

        function pointerCoordinates(pointerEvent, fallbackRect) {
            const nativeEvent = pointerEvent?.originalEvent || pointerEvent;
            const touch = nativeEvent?.changedTouches?.[0] || nativeEvent?.touches?.[0];
            const clientX = touch?.clientX ?? nativeEvent?.clientX;
            const clientY = touch?.clientY ?? nativeEvent?.clientY;

            if (Number.isFinite(clientX) && Number.isFinite(clientY) && (clientX !== 0 || clientY !== 0)) {
                return { x: clientX, y: clientY };
            }

            if (lastProductPointer && Date.now() - lastProductPointer.at < 1200) {
                return { x: lastProductPointer.x, y: lastProductPointer.y };
            }

            return {
                x: fallbackRect.left + (fallbackRect.width / 2),
                y: fallbackRect.top + (fallbackRect.height / 2)
            };
        }

        function animateProductToCart(productElement, pointerEvent) {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const imageElement = productElement.querySelector('.img');
            const mobileCart = document.querySelector('.pos-mobile-sidebar-toggler');
            const desktopCart = document.querySelector('.pos-sidebar-nav .nav-sale-command .nav-link');
            const mobileCartIsVisible = mobileCart && window.getComputedStyle(mobileCart).display !== 'none';
            const cartElement = mobileCartIsVisible ? mobileCart : desktopCart;
            if (!imageElement || !cartElement) return;

            const start = imageElement.getBoundingClientRect();
            const target = cartElement.getBoundingClientRect();
            if (!start.width || !start.height || !target.width || !target.height) return;

            const flyerSize = Math.min(118, Math.max(76, start.width * .68));
            const pointer = pointerCoordinates(pointerEvent, start);
            const clickX = pointer.x;
            const clickY = pointer.y;
            const flyerLeft = clickX - (flyerSize / 2);
            const flyerTop = clickY - (flyerSize / 2);

            const clickOrigin = document.createElement('span');
            clickOrigin.className = 'pos-product-click-origin';
            clickOrigin.setAttribute('aria-hidden', 'true');
            clickOrigin.style.left = `${clickX}px`;
            clickOrigin.style.top = `${clickY}px`;
            document.body.appendChild(clickOrigin);
            window.setTimeout(() => clickOrigin.remove(), 300);

            const flyer = document.createElement('div');
            flyer.className = 'pos-product-flyer';
            flyer.setAttribute('aria-hidden', 'true');
            flyer.style.left = `${flyerLeft}px`;
            flyer.style.top = `${flyerTop}px`;
            flyer.style.width = `${flyerSize}px`;
            flyer.style.height = `${flyerSize}px`;
            flyer.style.backgroundImage = window.getComputedStyle(imageElement).backgroundImage;
            document.body.appendChild(flyer);

            if (typeof flyer.animate !== 'function') {
                flyer.remove();
                return;
            }

            const deltaX = target.left + (target.width / 2) - (flyerLeft + flyerSize / 2);
            const deltaY = target.top + (target.height / 2) - (flyerTop + flyerSize / 2);
            const arcHeight = reduceMotion ? 20 : Math.min(150, Math.max(70, Math.abs(deltaX) * .16));

            const animation = flyer.animate([
                { transform: 'translate3d(0, 0, 0) scale(.05)', opacity: .2, filter: 'blur(1px)', offset: 0 },
                { transform: 'translate3d(0, 0, 0) scale(1)', opacity: .94, filter: 'blur(0)', offset: .16 },
                { transform: `translate3d(${deltaX * .52}px, ${deltaY * .46 - arcHeight}px, 0) scale(.8) rotate(-5deg)`, opacity: .82, filter: 'blur(.2px)', offset: .56 },
                { transform: `translate3d(${deltaX}px, ${deltaY}px, 0) scale(.18) rotate(4deg)`, opacity: .2, filter: 'blur(1px)' }
            ], {
                duration: reduceMotion ? 320 : 720,
                easing: 'cubic-bezier(.2,.72,.3,1)',
                fill: 'forwards'
            });

            animation.finished.catch(() => {}).finally(() => {
                flyer.remove();
                cartElement.classList.remove('cart-receive-pulse');
                void cartElement.offsetWidth;
                cartElement.classList.add('cart-receive-pulse');
                window.setTimeout(() => cartElement.classList.remove('cart-receive-pulse'), 400);
            });
        }

        function loadCatalog(reset = false) {
            if (reset && catalogState.request) {
                catalogState.request.abort();
                catalogState.request = null;
                catalogState.loading = false;
            }
            if (catalogState.loading || (!reset && !catalogState.hasMore)) {
                return catalogState.request || $.Deferred().resolve().promise();
            }

            if (reset) {
                catalogState.page = 1;
                catalogState.hasMore = true;
                $('#catalogProducts').empty();
                $('#catalogEmpty').hide();
            }

            catalogState.loading = true;
            $('#search_loader').show();
            $('#catalogLoadMore').prop('disabled', true);

            catalogState.request = $.ajax({
                url: catalogUrl,
                type: 'GET',
                data: {
                    q: catalogState.query,
                    category_id: catalogState.categoryId,
                    page: catalogState.page
                }
            }).done(function(response) {
                const products = response.data || [];
                products.forEach(product => $('#catalogProducts').append(productCard(product)));
                bindProductEvents();

                catalogState.hasMore = Number(response.current_page) < Number(response.last_page);
                catalogState.page = Number(response.current_page) + 1;
                $('#catalogEmpty').toggle($('#catalogProducts .product_list').length === 0);
                $('#catalogLoadMore').toggle(catalogState.hasMore);
            }).fail(function(xhr, status) {
                if (status === 'abort') return;
                Swal.fire({
                    icon: 'error',
                    title: 'Catalogue indisponible',
                    text: xhr.responseJSON?.message || 'Impossible de charger les produits.',
                    confirmButtonText: "D'accord"
                });
            }).always(function() {
                catalogState.loading = false;
                catalogState.request = null;
                $('#search_loader').hide();
                $('#catalogLoadMore').prop('disabled', false);
            });

            return catalogState.request;
        }

        // Référence stable de la copie jQuery qui a initialisé Select2
        // (hub/assets/js/vendor.min.js charge une autre copie de jQuery qui écrase window.jQuery → window.$)
        const clientSelect = $('#clientSelect');

        function initClientSelect() {
            if (typeof clientSelect.select2 !== 'function') return;
            try {
                clientSelect.select2({
                    width: '100%',
                    placeholder: 'Rechercher un client (facultatif)',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: clientSearchUrl,
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                q: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function(response) {
                            return response;
                        },
                        cache: true
                    },
                    language: {
                        searching: function() { return 'Recherche en cours…'; },
                        noResults: function() { return 'Aucun client trouvé'; },
                        loadingMore: function() { return 'Chargement…'; }
                    }
                });
            } catch (e) {}
        }

        // Force l'affichage du client dans le widget select2 de façon fiable :
        // on pose la valeur sur le <select> puis on recrée le widget si besoin,
        // sans dépendre d'un événement 'change' qui peut ne pas atteindre select2
        // à cause des deux copies de jQuery chargées sur la page.
        function syncClientSelection(value, clientName = '') {
            const id = value === null || value === undefined ? '' : String(value);
            if (!id) {
                clientSelect.val('').trigger('change.select2');
                return;
            }

            let option = clientSelect.find(`option[value="${id}"]`);
            if (!option.length) {
                option = $(new Option(clientName || `Client #${id}`, id, true, true));
                clientSelect.append(option);
            }
            clientSelect.val(id).trigger('change.select2');

            if (clientName) return;

            $.getJSON(clientSearchUrl, { client_id: id }).done(function(response) {
                const client = response.results?.[0];
                if (!client) {
                    option.remove();
                    clientSelect.val('').trigger('change.select2');
                    persistCurrentCart();
                    return;
                }
                option.text(client.text);
                clientSelect.trigger('change.select2');
                persistCurrentCart();
            });
        }

        initClientSelect();

        // Filtrage serveur du catalogue par catégorie.
        $('.pos-menu .nav-link[data-filter]').on('click', function(e) {
            e.preventDefault();
            $('.sale_list').fadeOut();
            $('#confirmSale').fadeIn();
            $('.no-sale').hide();

            $('.pos-menu .nav-link[data-filter]').removeClass('active');
            $(this).addClass('active');
            catalogState.categoryId = String($(this).data('category-id') || '');
            loadCatalog(true);
        });

        $('#searchProduct').on('input', function () {
            window.clearTimeout(catalogSearchTimer);
            catalogState.query = $(this).val().trim();
            catalogSearchTimer = window.setTimeout(() => loadCatalog(true), 300);
        });

        $('#catalogLoadMore').on('click', function () {
            loadCatalog(false);
        });

        loadCatalog(true);

        // Au clic sur élément de navigation de la liste des ventes
        $('.nav-sale').on('click', function(e) {
            e.preventDefault();
            $('.product_list').hide();
            $('.sale_list').fadeIn();
            $('#confirmSale').hide();
            $('.no-sale').show();
        });

        // Au clic sur élément de commande dans la navigation laterale
        $('.nav-sale-command').on('click', function(e) {
            e.preventDefault();
            $('.sale_list').hide();
            $('.product_list').fadeIn();
            $('#confirmSale').fadeIn();
        });

        // Au clic sur élément fermez modal print
        $('.close').on('click', function(e) {
            e.preventDefault();
            window.location.reload();
        });

        function bindProductEvents() {
            $('#search_loader').hide();
            // Supprime les événements avant de les ré-attacher pour éviter les doublons
            $('.pos-product').off('pointerdown touchstart').on('pointerdown touchstart', function (e) {
                const nativeEvent = e.originalEvent || e;
                const touch = nativeEvent.changedTouches?.[0] || nativeEvent.touches?.[0];
                const clientX = touch?.clientX ?? nativeEvent.clientX;
                const clientY = touch?.clientY ?? nativeEvent.clientY;

                if (Number.isFinite(clientX) && Number.isFinite(clientY)) {
                    lastProductPointer = { x: clientX, y: clientY, at: Date.now() };
                }
            });

            $('.pos-product').off('click').on('click', function (e) {
                e.preventDefault();

                animateProductToCart(this, e.originalEvent || e);

                let productId = $(this).data('id');
                let productName = $(this).data('name');
                let productPrice = $(this).data('price');
                let productImage = $(this).data('image') || defaultPosProductImage;
                let productQte = 1;

                // Vérifier si le produit existe déjà
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
                    addProduct(productId);
                }
                updateTotal();
            });

            // Rebind des effets hover (si tu les veux encore)
            $('.pos-product').off('mouseenter mouseleave').hover(
                function () {
                    $(this).addClass('product-hover');
                },
                function () {
                    $(this).removeClass('product-hover');
                }
            );

        }

        let currentReceiptSaleId = null;
        function openReceiptInModal(receiptHtml, saleData) {
            $('#receiptPreview').html(receiptHtml);
            currentReceiptSaleId = saleData.saleId;
            $('#invoiceDeliveryPanel').toggleClass('d-none', !!saleData.hasClient && !saleData.allowDelivery);
            $('#invoicePhone').val(saleData.clientPhone || '');
            $('#invoiceCountry').val(saleData.clientCountryCode || '{{ $company->country_code ?? 'TG' }}');
            updateInvoiceQuotas(saleData);
            $('#pdfModal').modal('show');
            return;

            const pdfBase64 = '';
            const pdfData = atob(pdfBase64); // Décode le base64
            const loadingTask = pdfjsLib.getDocument({ data: pdfData });

            loadingTask.promise.then(function(pdf) {
                // On récupère la première page
                pdf.getPage(1).then(function(page) {
                    const scale = 1.5;// Augmente l'échelle pour une meilleure qualité
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
            const receipt = document.querySelector('#receiptPreview');
            if (!receipt) return;

            const printWindow = window.open('');
            printWindow.document.write('<!doctype html><html><head><title>Impression du reçu</title>');
            printWindow.document.write('<meta name="viewport" content="width=device-width, initial-scale=1">');
            printWindow.document.write('<style>@page{margin:0}body{margin:0;background:#fff}#receiptPreview .receipt{width:80mm!important}</style>');
            printWindow.document.write('</head><body><div id="receiptPreview">');
            printWindow.document.write(receipt.innerHTML);
            printWindow.document.write('</div></body></html>');
            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }

        function printPdfCanvas() {
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

            let remiseMontant = parseFloat($('#remiseInput').val()) || 0;
            if (remiseMontant >= totalAmount) {
                Swal.fire({
                    icon: "warning",
                    title: "Remise invalide",
                    text: "La remise doit être inférieure au total de la vente.",
                    timer: 3000,
                    showConfirmButton: false
                });
                return;
            }

            Swal.fire({
                title: "Saisissez le montant donné par le client",
                input: "number",
                inputAttributes: {
                    min: totalAmount - remiseMontant, // Empêcher une saisie inférieure au total après remise
                    step: "1"
                },
                showCancelButton: true,
                confirmButtonText: "Calculer la monnaie",
                cancelButtonText: "Annuler",
                inputValidator: (value) => {
                    if (!value || value < totalAmount - remiseMontant) {
                        return "Le montant doit être supérieur ou égal au total de la vente.";
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let received_amount = parseFloat(result.value);
                    let codePromo = $('#promoCodeInput').val() || '';

                    // Vérifier s'il y a un code promo
                    if (codePromo.trim() !== "") {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('verifyPromo') }}",
                            data: {
                                code: codePromo,
                                _token: "{{ csrf_token() }}" // Protection CSRF Laravel
                            },
                            dataType: "json",
                            success: function(response) {
                                let discount = 0;
                                let finalAmount = totalAmount - remiseMontant;

                                if (response.valid) {
                                    let percent = response.percent; // Récupérer le pourcentage
                                    discount = (totalAmount * percent) / 100;
                                    finalAmount = totalAmount - discount - remiseMontant;
                                }

                                let monnaie = received_amount - finalAmount;

                                // Affichage de la confirmation avec réduction
                                Swal.fire({
                                    title: "Confirmation de la vente avec code promo",
                                    html: `<p>Montant initial : <b>${totalAmount.toFixed(2)}</b></p>
                                        <p>Remise : <b style="color:red">-${remiseMontant.toFixed(2)}</b></p>
                                        <p>Réduction appliquée (${response.valid ? response.percent : 0}%) : <b style="color:red">-${discount.toFixed(2)}</b></p>
                                        <p><b>Total à payer après réduction : ${finalAmount.toFixed(2)}</b></p>
                                        <p>Montant reçu : <b>${received_amount.toFixed(2)}</b></p>
                                        <p>Monnaie à rendre : <b style="color:green">${monnaie.toFixed(2)}</b></p>`,
                                    icon: "question",
                                    confirmButtonText: "Confirmer la vente",
                                    confirmButtonColor: "green",
                                    showCancelButton: true,
                                    cancelButtonText: "Annuler",
                                    cancelButtonColor: "blue",
                                }).then((saleResult) => {
                                    if (saleResult.isConfirmed) {
                                        $('#loader').show();
                                        $('#saleLoader').show();
                                        $('#confirmSale').hide();
                                        $('.product_list').fadeOut();

                                        // Envoyer les données via AJAX
                                        $.ajax({
                                            url: '{{ route("sale.store") }}',
                                            type: 'POST',
                                            data: {
                                                _token: '{{ csrf_token() }}',
                                                products: products,
                                                received_amount: received_amount,
                                                total_amount: finalAmount,
                                                discount: discount + remiseMontant, // Ajout de la réduction (promo + remise)
                                                code_promo: codePromo,
                                                client_id: clientSelect.val()
                                            },
                                            success: function(data) {
                                                if (data.status) {
                                                    removeActivePendingOrder();
                                                    clearPersistedCart();
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

                                                    // Ouvrir le reçu PDF
                                                    openReceiptInModal(data.receiptHtml, data);
                                                } else {
                                                    $('#loader').hide();
                                                    $('#saleLoader').hide();
                                                    $('#confirmSale').show();
                                                    $('.product_list').fadeIn();
                                                    Swal.fire({
                                                        title: data.title,
                                                        text: data.msg,
                                                        icon: 'error',
                                                        confirmButtonText: "D'accord",
                                                        confirmButtonColor: '#A40000',
                                                    })
                                                }
                                            },
                                            error: function() {
                                                $('#loader').hide();
                                                $('#saleLoader').hide();
                                                $('#confirmSale').show();
                                                $('.product_list').fadeIn();
                                                Swal.fire({
                                                    icon: "error",
                                                    title: "Erreur",
                                                    text: "Impossible de communiquer avec le serveur.",
                                                    timer: 3600,
                                                })
                                            }
                                        });
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                                Swal.fire({
                                    icon: "error",
                                    title: "Erreur serveur",
                                    text: "Impossible de vérifier le code promo.",
                                    timer: 3600
                                });
                            }
                        });
                    } else {
                        // Aucun code promo, traitement direct
                        let finalAmount = totalAmount - remiseMontant;
                        let monnaie = received_amount - finalAmount;

                        Swal.fire({
                            title: "Confirmation de la vente",
                            html: `<p>Total initial : <b>${totalAmount.toFixed(2)}</b></p>
                                <p>Remise : <b style="color:red">-${remiseMontant.toFixed(2)}</b></p>
                                <p><b>Total à payer : ${finalAmount.toFixed(2)}</b></p>
                                <p>Montant reçu : <b>${received_amount.toFixed(2)}</b></p>
                                <p>Monnaie à rendre : <b style="color:green">${monnaie.toFixed(2)}</b></p>`,
                            icon: "question",
                            confirmButtonText: "Confirmer la vente",
                            confirmButtonColor: "green",
                            showCancelButton: true,
                            cancelButtonText: "Annuler",
                            cancelButtonColor: "blue",
                        }).then((saleResult) => {
                            if (saleResult.isConfirmed) {
                                $('#loader').show();
                                $('#saleLoader').show();
                                $('#confirmSale').hide();
                                $('.product_list').fadeOut();

                                // Envoyer les données via AJAX
                                $.ajax({
                                    url: '{{ route("sale.store") }}',
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        products: products,
                                        received_amount: received_amount,
                                        total_amount: finalAmount,
                                        discount: remiseMontant, // Remise manuelle
                                        code_promo: codePromo,
                                        client_id: clientSelect.val()
                                    },
                                    success: function(data) {
                                        if (data.status) {
                                            removeActivePendingOrder();
                                            clearPersistedCart();
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

                                            // Ouvrir le reçu PDF
                                            openReceiptInModal(data.receiptHtml, data);
                                        } else {
                                            $('#loader').hide();
                                            $('#saleLoader').hide();
                                            $('#confirmSale').show();
                                            $('.product_list').fadeIn();
                                            Swal.fire({
                                                title: data.title,
                                                text: data.msg,
                                                icon: 'error',
                                                confirmButtonText: "D'accord",
                                                confirmButtonColor: '#A40000',
                                            })
                                        }
                                    },
                                    error: function() {
                                        $('#loader').hide();
                                        $('#saleLoader').hide();
                                        $('#confirmSale').show();
                                        $('.product_list').fadeIn();
                                        Swal.fire({
                                            icon: "error",
                                            title: "Erreur",
                                            text: "Impossible de communiquer avec le serveur.",
                                            timer: 3600,
                                        })
                                    }
                                });
                            }
                        });
                    }
                }
            });
        });

        $('#print').on('click', function(e) {
            printPdf()
        });

        $('#sendInvoice').on('click', async function() {
            const button = this;
            const phone = $('#invoicePhone').val().trim();
            const country_code = $('#invoiceCountry').val();
            const whatsapp = $('#invoiceWhatsapp').is(':checked');
            const sms = $('#invoiceSms').is(':checked');
            if (!phone || (!whatsapp && !sms)) {
                Swal.fire({icon: 'warning', title: 'Informations incomplètes', text: 'Saisissez un numéro et choisissez au moins un canal.'});
                return;
            }
            const url = '{{ route('sale.send-invoice', ['sale' => '__SALE__']) }}'.replace('__SALE__', currentReceiptSaleId);
            const request = fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({phone, country_code, whatsapp, sms})
            }).then(async response => {
                const data = await response.json();
                if (!response.ok || !data.status) {
                    const error = new Error(data.message || 'Envoi impossible.');
                    error.payload = data;
                    throw error;
                }
                return data;
            });
            try {
                const data = window.ServerButtonLoader
                    ? await window.ServerButtonLoader.withLoader(button, request, 'Envoi en cours…')
                    : await request;
                updateInvoiceQuotas(data);
                Swal.fire({icon: 'success', title: 'Facture envoyée', text: data.message});
            } catch (error) {
                updateInvoiceQuotas(error.payload || {});
                Swal.fire({icon: 'error', title: 'Envoi impossible', text: error.message || 'Veuillez réessayer.'});
            }
        });

        const invoiceWhatsappAuthorized = {{ $company->invoice_whatsapp_enabled ? 'true' : 'false' }};
        const invoiceSmsAuthorized = {{ $company->invoice_sms_enabled ? 'true' : 'false' }};
        function updateInvoiceQuotas(data) {
            if (data.whatsappQuota !== undefined) {
                $('#invoiceWhatsappQuota').text(data.whatsappQuota);
                $('#invoiceWhatsapp').prop('disabled', !invoiceWhatsappAuthorized || Number(data.whatsappQuota) < 1);
                if (Number(data.whatsappQuota) < 1) $('#invoiceWhatsapp').prop('checked', false);
            }
            if (data.smsQuota !== undefined) {
                $('#invoiceSmsQuota').text(data.smsQuota);
                $('#invoiceSms').prop('disabled', !invoiceSmsAuthorized || Number(data.smsQuota) < 1);
                if (Number(data.smsQuota) < 1) $('#invoiceSms').prop('checked', false);
            }
            $('#sendInvoice').prop('disabled', $('#invoiceWhatsapp').prop('disabled') && $('#invoiceSms').prop('disabled'));
        }

        $('body').on('click', '.deliver-invoice', async function() {
            const button = this;
            const url = '{{ route('sale.receipt', ['sale' => '__SALE__']) }}'.replace('__SALE__', $(button).data('id'));
            const request = fetch(url, {headers: {'Accept': 'application/json'}}).then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Impossible de charger la facture.');
                return data;
            });
            try {
                const data = window.ServerButtonLoader
                    ? await window.ServerButtonLoader.withLoader(button, request, 'Chargement…')
                    : await request;
                openReceiptInModal(data.receiptHtml, data);
            } catch (error) {
                Swal.fire({icon: 'error', title: 'Facture indisponible', text: error.message});
            }
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

        // $('.pos-product').on('click', function(e) {
        //     e.preventDefault();

        //     let productId = $(this).data('id');
        //     let productName = $(this).data('name');
        //     let productPrice = $(this).data('price');
        //     let productImage = $(this).data('image');
        //     let productQte = 1;

        //     // Verify if product already exist
        //     let existingProduct = $(`.pos-order-product[data-product-id="${productId}"]`);
        //     if (existingProduct.length > 0) {
        //         let quantityInput = existingProduct.find('.quantity-input');
        //         quantityInput.val(parseInt(quantityInput.val()) + 1);
        //         updateProductTotal(existingProduct, productPrice);
        //     } else {
        //         let productHtml = `
        //             <div class="pos-order">
        //                 <div class="pos-order-product" data-product-id="${productId}">
        //                     <div class="img" style="background-image: url(${productImage})"></div>
        //                     <div class="flex-1">
        //                         <div class="h6 mb-1">${productName}</div>
        //                         <div class="small">${productPrice} FCFA</div>
        //                         <div class="d-flex">
        //                             <a href="#" class="btn btn-outline-theme btn-sm btn-minus"><i class="fa fa-minus"></i></a>
        //                             <input type="text" class="form-control w-50px form-control-sm mx-2 bg-white bg-opacity-25 text-center quantity-input" value="${productQte}">
        //                             <a href="#" class="btn btn-outline-theme btn-sm btn-plus"><i class="fa fa-plus"></i></a>
        //                         </div>
        //                     </div>
        //                     <div class="pos-order-price">${productPrice * productQte} FCFA</div>
        //                     <div><a href="#" title="supprimer le produit" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></a></div>
        //                 </div>
        //             </div>
        //         `;

        //         $('#newOrderTab').append(productHtml);
        //         addProduct(productId)
        //     }
        //     updateTotal();
        // });

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
            let remiseMontant = parseFloat($('#remiseInput').val()) || 0;
            $('.total-amount').text((total - remiseMontant) + ' FCFA');
            persistCurrentCart();
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
        $(document).on('input', '.quantity-input', function(e) {
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
            detachPendingOrderWhenItsProductsAreGone();
        });

        var Datatable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('sale.index')}}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'code',name: 'code'},
                {data: 'received_amount',name: 'received_amount'},
                {data: 'total_amount',name: 'total_amount'},
                {data: 'remaining_amount',name: 'remaining_amount'},
                @if ($canViewFinancials)
                {data: 'total_profit',name: 'total_profit'},
                @endif
                {data: 'code_promo',name: 'code_promo'},
                {data: 'discount',name: 'discount'},
                {data: 'client',name: 'client'},
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

        // Hover effect
        $('.product_list .pos-product').hover(
            function() {
                $(this).addClass('product-hover');
            },
            function() {
                $(this).removeClass('product-hover');
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

        $("#deletpromoinput").on("click", function () {
            $("#promoCodeInput").val("").focus(); // Effacer et remettre le focus
            persistCurrentCart();
        });

        $("#deletremiseinput").on("click", function () {
            $("#remiseInput").val("").focus(); // Effacer et remettre le focus
            updateTotal();
        });

        $('#remiseInput').on('input', function() {
            updateTotal();
        });

        $('#promoCodeInput').on('input', function() {
            persistCurrentCart();
        });

        clientSelect.on('change', function() {
            persistCurrentCart();
        });

        // ============= PENDING ORDERS (localStorage) =============

        let activePendingOrder = null;

        function getPendingOrdersKey() {
            return @json('pending_orders_' . auth()->id() . '_' . $activeCompany->id);
        }

        function getPendingOrders() {
            const key = getPendingOrdersKey();
            const data = localStorage.getItem(key);
            return data ? JSON.parse(data) : [];
        }

        function savePendingOrdersList(orders) {
            localStorage.setItem(getPendingOrdersKey(), JSON.stringify(orders));
        }

        function getNextOrderId(orders) {
            if (orders.length === 0) return 1;
            return Math.max(...orders.map(o => o.id)) + 1;
        }

        function updatePendingBadge() {
            const orders = getPendingOrders();
            const badge = $('#pendingBadge');
            if (orders.length > 0) {
                badge.text(orders.length).show();
            } else {
                badge.hide();
            }
        }

        function clearCurrentOrder() {
            $('#newOrderTab .pos-order-product').closest('.pos-order').remove();
            selectedProducts.clear();
            $('#promoCodeInput').val('');
            $('#remiseInput').val('');
            syncClientSelection('');
            updateTotal();
            updateOrderCount();
        }

        function removeActivePendingOrder() {
            if (!activePendingOrder) return;

            const orders = getPendingOrders().filter(
                order => order.id !== activePendingOrder.id
            );
            savePendingOrdersList(orders);
            activePendingOrder = null;
            updatePendingBadge();
        }

        function detachPendingOrderWhenItsProductsAreGone() {
            if (!activePendingOrder || !activePendingOrder.productIds) return;

            const remainingProductIds = new Set(
                $('#newOrderTab .pos-order-product').map(function() {
                    return String($(this).data('product-id'));
                }).get()
            );
            const hasLoadedProduct = activePendingOrder.productIds.some(
                productId => remainingProductIds.has(String(productId))
            );

            if (!hasLoadedProduct) {
                activePendingOrder = null;
            }
        }

        function getOrderFromCart() {
            const products = [];
            let hasItems = false;

            $('.pos-order-product').each(function() {
                hasItems = true;
                const productId = $(this).data('product-id');
                const quantity = $(this).find('.quantity-input').val();
                const priceText = $(this).find('.small').text().replace(' FCFA', '').trim();
                const price = parseFloat(priceText);
                const name = $(this).find('.h6.mb-1').text().trim();
                const imgStyle = $(this).find('.img').attr('style') || '';
                const imgMatch = imgStyle.match(/url\(['"]?(.*?)['"]?\)/);
                const image = imgMatch ? imgMatch[1] : '';

                products.push({
                    product_id: productId,
                    name: name,
                    unit_price: price,
                    quantity: quantity,
                    total_price: price * parseInt(quantity),
                    image: image
                });
            });

            if (!hasItems) return null;

            const totalAmount = parseFloat($('.total-amount').text().replace(' FCFA', '')) || 0;
            const codePromo = ($('#promoCodeInput').val() || '').trim();
            const remise = parseFloat($('#remiseInput').val()) || 0;
            const client_id = clientSelect.val();
            const client_name = client_id
                ? clientSelect.find('option:selected').text().trim()
                : '';

            return { products, total_amount: totalAmount, code_promo: codePromo, remise: remise, client_id, client_name };
        }

        function clearPersistedCart() {
            try {
                localStorage.removeItem(cartStorageKey);
            } catch (error) {
                console.warn('Le panier local n’a pas pu être supprimé.', error);
            }
        }

        function persistCurrentCart() {
            if (isRestoringCart) return;

            const order = getOrderFromCart();
            if (!order) {
                clearPersistedCart();
                return;
            }

            order.saved_at = new Date().toISOString();
            order.version = 1;

            try {
                localStorage.setItem(cartStorageKey, JSON.stringify(order));
            } catch (error) {
                console.warn('Le panier local n’a pas pu être enregistré.', error);
            }
        }

        function restorePersistedCart() {
            let order = null;

            try {
                const storedCart = localStorage.getItem(cartStorageKey);
                order = storedCart ? JSON.parse(storedCart) : null;
            } catch (error) {
                clearPersistedCart();
                console.warn('Le panier local était illisible et a été ignoré.', error);
                return;
            }

            if (!order || !Array.isArray(order.products) || order.products.length === 0) return;

            isRestoringCart = true;
            try {
                restoreOrderToCart(order);
            } finally {
                isRestoringCart = false;
            }

            persistCurrentCart();
        }

        function restoreOrderToCart(order) {
            // Clear current cart
            $('#newOrderTab .pos-order-product').closest('.pos-order').remove();
            selectedProducts.clear();

            if (!order.products || order.products.length === 0) return;

            order.products.forEach(function(p) {
                const productId = Number.parseInt(p.product_id, 10);
                const unitPrice = Number.parseFloat(p.unit_price);
                const quantity = Math.max(1, Number.parseInt(p.quantity, 10) || 1);
                if (!Number.isInteger(productId) || !Number.isFinite(unitPrice)) return;

                const productName = escapeHtml(p.name || 'Produit');
                const productImage = escapeHtml(p.image || defaultPosProductImage);
                const productHtml = `
                    <div class="pos-order">
                        <div class="pos-order-product" data-product-id="${productId}">
                            <div class="img" style="background-image: url('${productImage}')"></div>
                            <div class="flex-1">
                                <div class="h6 mb-1">${productName}</div>
                                <div class="small">${unitPrice} FCFA</div>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-outline-theme btn-sm btn-minus"><i class="fa fa-minus"></i></a>
                                    <input type="text" class="form-control w-50px form-control-sm mx-2 bg-white bg-opacity-25 text-center quantity-input" value="${quantity}">
                                    <a href="#" class="btn btn-outline-theme btn-sm btn-plus"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="pos-order-price">${unitPrice * quantity} FCFA</div>
                            <div><a href="#" title="supprimer le produit" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></a></div>
                        </div>
                    </div>
                `;
                $('#newOrderTab').append(productHtml);
                addProduct(productId);
            });

            updateOrderCount();

            if (order.code_promo) {
                $('#promoCodeInput').val(order.code_promo);
            }
            if (order.remise) {
                $('#remiseInput').val(order.remise);
            }
            if (order.client_id) {
                syncClientSelection(order.client_id, order.client_name || '');
            }
            updateTotal();
        }

        function renderPendingOrdersList() {
            const orders = getPendingOrders();
            const container = $('#pendingOrdersList');

            if (orders.length === 0) {
                container.html('<div class="text-center text-muted py-4">Aucune commande en cours</div>');
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-dark table-hover">';
            html += '<thead><tr><th>#</th><th>Libellé</th><th>Date</th><th>Articles</th><th>Total</th><th>Actions</th></tr></thead><tbody>';

            orders.forEach(function(order, index) {
                const label = order.label || 'Commande #' + order.id;
                const date = order.date || '';
                const itemsCount = order.products ? order.products.length : 0;
                const total = order.total_amount || 0;
                html += `<tr>
                    <td>${index + 1}</td>
                    <td>${label}</td>
                    <td>${date}</td>
                    <td>${itemsCount}</td>
                    <td>${total} FCFA</td>
                    <td>
                        <button class="btn btn-success btn-sm load-order" data-id="${order.id}"><i class="fa fa-upload"></i> Charger</button>
                        <button class="btn btn-danger btn-sm delete-order" data-id="${order.id}"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            container.html(html);
        }

        // Save current order
        $('#savePendingOrder').on('click', function(e) {
            e.preventDefault();

            const orderData = getOrderFromCart();
            if (!orderData) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Panier vide',
                    text: 'Ajoutez des produits avant de sauvegarder.',
                    timer: 2500,
                    showConfirmButton: false
                });
                return;
            }

            const clientName = clientSelect.val()
                ? clientSelect.find('option:selected').text().trim()
                : '';
            const timeLabel = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

            Swal.fire({
                title: 'Sauvegarder la commande',
                input: 'text',
                inputLabel: 'Donnez un nom à cette commande',
                inputValue: activePendingOrder
                    ? activePendingOrder.label
                    : (clientName ? clientName + ' ' + timeLabel : 'Commande ' + timeLabel),
                showCancelButton: true,
                confirmButtonText: 'Sauvegarder',
                cancelButtonText: 'Annuler',
                inputValidator: (value) => {
                    if (!value) return 'Veuillez entrer un libellé';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const orders = getPendingOrders();
                    const savedOrder = {
                        id: activePendingOrder ? activePendingOrder.id : getNextOrderId(orders),
                        label: result.value,
                        date: new Date().toLocaleString('fr-FR'),
                        products: orderData.products,
                        total_amount: orderData.total_amount,
                        code_promo: orderData.code_promo,
                        remise: orderData.remise,
                        client_id: orderData.client_id,
                        client_name: orderData.client_name
                    };

                    const existingOrderIndex = orders.findIndex(order => order.id === savedOrder.id);
                    if (existingOrderIndex !== -1) {
                        orders[existingOrderIndex] = savedOrder;
                    } else {
                        orders.push(savedOrder);
                    }

                    savePendingOrdersList(orders);
                    updatePendingBadge();
                    clearCurrentOrder();
                    activePendingOrder = null;

                    Swal.fire({
                        icon: 'success',
                        title: 'Commande sauvegardée',
                        toast: true,
                        position: 'top',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        });

        // Show pending orders modal
        $('#showPendingOrders').on('click', function(e) {
            e.preventDefault();
            renderPendingOrdersList();
            $('#pendingOrdersModal').modal('show');
        });

        // Load an order (delegated event because buttons are dynamic)
        $(document).on('click', '.load-order', function() {
            const id = parseInt($(this).data('id'));
            let orders = getPendingOrders();
            const order = orders.find(o => o.id === id);
            if (!order) {
                Swal.fire({ icon: 'error', title: 'Commande introuvable', timer: 2000, showConfirmButton: false });
                return;
            }

            restoreOrderToCart(order);
            activePendingOrder = {
                id: order.id,
                label: order.label || 'Commande #' + order.id,
                productIds: (order.products || []).map(product => product.product_id)
            };
            $('#pendingOrdersModal').modal('hide');

            Swal.fire({
                icon: 'success',
                title: 'Commande chargée',
                text: 'Vous pouvez modifier la commande et la sauvegarder à nouveau.',
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 2500
            });
        });

        // Delete an order (delegated event)
        $(document).on('click', '.delete-order', function() {
            const id = parseInt($(this).data('id'));
            Swal.fire({
                title: 'Supprimer cette commande ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    let orders = getPendingOrders();
                    orders = orders.filter(o => o.id !== id);
                    savePendingOrdersList(orders);
                    if (activePendingOrder && activePendingOrder.id === id) {
                        activePendingOrder = null;
                    }
                    updatePendingBadge();
                    renderPendingOrdersList();

                    Swal.fire({
                        icon: 'success',
                        title: 'Commande supprimée',
                        toast: true,
                        position: 'top',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        });

        // Init badge on page load
        updatePendingBadge();
        restorePersistedCart();

        let stockRefreshRequest = null;

        function refreshProductStocks() {
            if (stockRefreshRequest) {
                return stockRefreshRequest;
            }

            stockRefreshRequest = loadCatalog(true).always(function() {
                stockRefreshRequest = null;
            });

            return stockRefreshRequest;
        }

        function resetSaleInterfaceAfterReceipt() {
            $('#loader, #saleLoader').stop(true, true).hide();
            $('#confirmSale').stop(true, true).show();
            $('.product_list, #product_list').stop(true, true).show();
            clearCurrentOrder();

            if (typeof Datatable !== 'undefined' && Datatable.ajax) {
                Datatable.ajax.reload(null, false);
            }
        }

        $(document).on('click', '#pdfModal .receipt-modal-close', function() {
            resetSaleInterfaceAfterReceipt();
            refreshProductStocks();
        });

        $('#pdfModal').on('hidden.bs.modal', function() {
            resetSaleInterfaceAfterReceipt();
            $('#receiptPreview').html('<div class="py-5 text-center text-muted">Chargement du reçu...</div>');
            refreshProductStocks();
        });
    });
</script>

<!-- verify code promo -->
<script>
    $(document).ready(function() {
        let inputField = $("#promoCodeInput");
        

        // Capture l'événement "Enter" après scan
        inputField.on("keyup", function(event) {
            let promoCode = inputField.val().trim();
            if (event.key === "Enter") {
                event.preventDefault(); // Empêche le rechargement de la page
                 // Récupère la valeur
                if (promoCode.length >= 6) {  // Vérifie si le code est suffisant avant d'envoyer
                    verifyCode(promoCode);
                }else{
                    Swal.fire({
                        toast: true,
                        position: "top",
                        icon: "error",
                        title: "Nombre de caractère du Code promo invalide !"+promoCode.length,
                        showConfirmButton: false,
                        timer: 5000
                    });
                }
                inputField.focus(); // Remet le focus
            }else{
                verifyCode(promoCode);
                inputField.focus();
            }
        });

        function verifyCode(promoCode){
            if (promoCode.length >= 6) {
                console.log("Code promo scanné :", promoCode); // Vérification console

                // Envoi à Laravel via AJAX pour vérification
                $.ajax({
                    type: "POST",
                    url: "{{ route('verifyPromo')}}",
                    data: {
                        code: promoCode,
                        _token: "{{ csrf_token() }}" // Protection CSRF Laravel
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.valid) {
                            Swal.fire({
                                toast: true,
                                position: "top",
                                icon: "success",
                                title: "Code promo valide !",
                                showConfirmButton: false,
                                timer: 5000
                            });
                        } else {
                            Swal.fire({
                                toast: true,
                                position: "top",
                                icon: "error",
                                title: "Code promo invalide ou inactif !",
                                showConfirmButton: false,
                                timer: 5000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            icon: "error",
                            title: "Erreur serveur",
                            text: "Impossible de vérifier le code promo.",
                            timer: 3600
                        });
                    }
                });
            }else{
                // Swal.fire({
                //     toast: true,
                //     position: "top",
                //     icon: "error",
                //     title: "Nombre de caractère du Code promo invalide !"+promoCode.length,
                //     showConfirmButton: false,
                //     timer: 5000
                // });
                console.log("Nombre de caractère du Code promo invalide !");
            }
        }
    });
</script>



@endsection
