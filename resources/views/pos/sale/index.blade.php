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
                                <a class="nav-link active" href="#" data-filter="all">
                                    <div class="card">
                                        <div class="card-body">
                                            <i class="fa fa-fw fa-utensils"></i> Tous
                                            ( <span>{{$Product->where('status',1)->count()}}</span> )
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
                            
                            <div class="{{auth()->user()->user_type==3?'col-xl-4':'col-xl-3'}} col-lg-6 ">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex fw-bold small mb-3">
                                            <span class="flex-grow-0"><h5><strong>Total des Ventes</strong><h5></span>
                                            <!-- <a href="#" data-toggle="card-expand"class="text-inverse text-opacity-50 text-decoration-none">
                                                <i class="bi bi-fullscreen"></i></a> -->
                                        </div>
                                        <div class="row align-items-center mb-2">
                                            <div class="col-7">
                                                <h3 class="mb-0">{{$Object->count()}}</h3>
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
                            <div class="{{auth()->user()->user_type==3?'col-xl-4':'col-xl-3'}} col-lg-6 ">
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
                            <div class="{{auth()->user()->user_type==3?'col-xl-4':'col-xl-3'}} col-lg-6 ">
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
                             @if (auth()->user()->user_type!=3)
                                 <div class="{{auth()->user()->user_type==3?'col-xl-4':'col-xl-3'}} col-lg-6 ">
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
                                            <th>Profit total</th>
                                            <th>Code promo</th>
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

                        <!-- product list -->
                        @foreach($Category->where('status',1) as $category)
                            @foreach($category->products->where('status',1) as $product)
                                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-4 col-sm-6 pb-4 product_list" data-type="{{ $category->name }}">
                                    <div class="card h-100">
                                        <div class="card-body products h-100 p-1">
                                            <a href="#" class="pos-product" data-bs-toggle="modal" data-bs-target="#modalPosItem"
                                                data-id="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}"
                                                data-image="{{ asset('images/' . $product->image) }}"
                                                data-qte="{{ $product->qte }}"
                                            >

                                            <!-- 1440 * 1024 -->
                                            <div class="img" style="background-image: url({{ asset('images/' . $product->image) }}); background-size: cover; background-position: center; width: 100%; height: 150px;"></div>
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
                                <marquee class="bg-dark"><h2>{{Auth::user()->name}}</h2></marquee >
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
                                                                                    <!-- <div class="mb-1"><small
                                                                                            class="fs-9px fw-500 lh-1 d-inline-block rounded-0 badge bg-secondary bg-opacity-25 text-inverse text-opacity-75 pt-5px">SKU90400</small>
                                                                                    </div> -->
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
                        <div class="bg-light">
                        <img src="http://127.0.0.1:1111/storage/barcodes/75FKZVT.png" alt="Code Barre"></div>
                        
                        <!-- <form action=""> -->
                            <input type="text" id="promoCodeInput" class="form-control" placeholder="Scannez le code promo" autofocus>
                            <button class="btn btn-danger btn-sm" id="deletpromoinput" type=""><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></button>
                        <!-- </form> -->
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
                                @if ($company AND $company->count()==1)
                                    <a href="#" id="confirmSale" class="btn btn-outline-theme rounded-0 w-150px"><i class="bi bi-send-check fa-lg"></i><br>
                                        <span class="small">Vendre</span>
                                    </a>
                                @else
                                    <a href="#" id="" class="btn btn-outline-theme rounded-0 w-150px" disabled><br>
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
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Utilisez modal-xl pour un modal plus large -->
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
                    <button type="button" class="btn btn-secondary close" data-dismiss="pdfModalLabel">Fermer</button>
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
        $('#loader').hide();
        $('#saleLoader').hide();
        $('#search_loader').hide();
        let originalProducts = $('#product_list').html();
        bindProductEvents();

        // Au clic sur un élément de navigation
        $('.nav-link').on('click', function(e) {
            e.preventDefault();
            // hide sale list
            $('.sale_list').fadeOut();
            $('#confirmSale').fadeIn();
            $('.no-sale').hide();
            
            // Get the selected category
            var filter = $(this).attr('data-filter');
            
            // Delete active class from all tabs and add to clicked tab
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
            $('#product_list').html(originalProducts); // Remet les produits initiaux
            bindProductEvents();
            
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

        $('#searchProduct').on('keyup', function () {
            let value = $(this).val();
            $('.search_product_list').remove();
            if (value.length === 0) {
                let originalProducts = $('#product_list').html();
                bindProductEvents();
                return;
            }

            $('#search_loader').show();
            $.ajax({
                url: "{{ route('products.search') }}",
                type: "GET",
                data: { q: value},
                success: function (products) {
                    let container = $('#product_list');
                    container.empty();
                    if (products.length === 0) {
                        container.append('<h1 class="text-center" >Aucun produit trouvé</h1>');
                    } else {
                        products.forEach(product => {
                            container.append(`
                                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-4 col-sm-6 pb-4 search_product_list">
                                    <div class="card h-100">
                                        <div class="card-body products h-100 p-1">
                                            <a href="#" class="pos-product" data-bs-toggle="modal" data-bs-target="#modalPosItem"
                                                data-id="${product.id}"
                                                data-name="${product.name}"
                                                data-price="${product.price}"
                                                data-image="/images/${product.image}"
                                                data-qte="${product.qte}">
                                                <div class="img" style="background-image: url(/images/${product.image}); background-size: cover; background-position: center; width: 100%; height: 150px;"></div>
                                                <div class="info">
                                                    <div class="title">Nom : ${product.name}&reg;</div>
                                                    <div class="title price">Prix : ${product.price} FCFA</div>
                                                    <div class="title qte">Quantité : ${product.qte}</div>
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
                            `);
                        });
                        bindProductEvents();
                    }
                },
                complete: function () {
                    // Cache le loader après la réponse (réussie ou échouée)
                    $('#search_loader').hide();
                }
            });
        });

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
            $('.pos-product').off('click').on('click', function (e) {
                e.preventDefault();

                let productId = $(this).data('id');
                let productName = $(this).data('name');
                let productPrice = $(this).data('price');
                let productImage = $(this).data('image');
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

            $('.search_product_list .pos-product').hover(
                function() {
                    $(this).addClass('product-hover');
                },
                function() {
                    $(this).removeClass('product-hover');
                }
            );
            $('.search_product_list .pos-product').on('click', function(e) {
                e.preventDefault();
                
                // Removes the click effect of other products
                $('.product_list .pos-product').removeClass('product-clicked');
                
                // Add the click effect of other products
                $(this).addClass('product-clicked');
            });
        }

        function openPdfInModal(pdfBase64) {
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
                title: "Saisissez le montant donné par le client",
                input: "number",
                inputAttributes: {
                    min: totalAmount, // Empêcher une saisie inférieure au total
                    step: "1"
                },
                showCancelButton: true,
                confirmButtonText: "Calculer la monnaie",
                cancelButtonText: "Annuler",
                inputValidator: (value) => {
                    if (!value || value < totalAmount) {
                        return "Le montant doit être supérieur ou égal au total de la vente.";
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let received_amount = parseFloat(result.value);
                    let codePromo = $('#promoCodeInput').val();

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
                                let finalAmount = totalAmount;

                                if (response.valid) {
                                    let percent = response.percent; // Récupérer le pourcentage
                                    discount = (totalAmount * percent) / 100;
                                    finalAmount = totalAmount - discount;
                                }

                                let monnaie = received_amount - finalAmount;

                                // Affichage de la confirmation avec réduction
                                Swal.fire({
                                    title: "Confirmation de la vente avec code promo",
                                    html: `<p>Montant initial : <b>${totalAmount.toFixed(2)}</b></p>
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
                                                discount: discount, // Ajout de la réduction
                                                code_promo: codePromo
                                            },
                                            success: function(data) {
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

                                                    // Ouvrir le reçu PDF
                                                    openPdfInModal(data.pdfBase64);
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
                        let monnaie = received_amount - totalAmount;

                        Swal.fire({
                            title: "Confirmation de la vente",
                            html: `<p>Total à payer : <b>${totalAmount.toFixed(2)}</b></p>
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
                                        total_amount: totalAmount,
                                        discount: 0, // Pas de réduction
                                        code_promo: codePromo
                                    },
                                    success: function(data) {
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

                                            // Ouvrir le reçu PDF
                                            openPdfInModal(data.pdfBase64);
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
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'code',name: 'code'},
                {data: 'received_amount',name: 'received_amount'},
                {data: 'total_amount',name: 'total_amount'},
                {data: 'remaining_amount',name: 'remaining_amount'},
                {data: 'total_profit',name: 'total_profit'},
                {data: 'code_promo',name: 'code_promo'},
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