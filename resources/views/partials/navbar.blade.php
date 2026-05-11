<!--header-->
<div id="header" class="app-header">

    <div class="desktop-toggler">
        <button type="button" class="menu-toggler" data-toggle-class="app-sidebar-collapsed"
            data-dismiss-class="app-sidebar-toggled" data-toggle-target=".app">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>

    <div class="mobile-toggler">
        <button type="button" class="menu-toggler" data-toggle-class="app-sidebar-mobile-toggled"
            data-toggle-target=".app">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>

    <div class="brand">
        <a href="index.html" class="brand-logo">
            <span class="brand-img">
                <span class="brand-img-text text-theme">PS</span>
            </span>
            <span class="brand-text">{{ config('app.name') }}</span>
        </a>
    </div>

    <div class="menu">
        <!-- <div class="menu-item dropdown">
            <a href="#" data-toggle-class="app-header-menu-search-toggled" data-toggle-target=".app"
                class="menu-link">
                <div class="menu-icon"><i class="bi bi-search nav-icon"></i></div>
            </a>
        </div> -->
        <div class="menu-item dropdown dropdown-mobile-full">
            <a href="#" data-bs-toggle="dropdown" data-bs-display="static" class="menu-link">
                <div class="menu-icon"><i class="bi bi-grid-3x3-gap nav-icon"></i></div>
            </a>
            <div class="dropdown-menu fade dropdown-menu-end w-300px text-center p-0 mt-1">
                <div class="row row-grid gx-0">
                    <div class="col-12">
                        <a href="pos_customer_order.html" target="_blank"
                            class="dropdown-item text-decoration-none p-3 bg-none">
                            <div><i class="bi bi-hdd-network h2 opacity-5 d-block my-1"></i></div>
                            <div class="fw-500 fs-10px text-inverse">POS</div>
                        </a>
                    </div>
                </div>
                <!-- <div class="row row-grid gx-0">
                    <div class="col-12">
                        <a href="settings.html" class="dropdown-item text-decoration-none p-3 bg-none">
                            <div class="position-relative">
                                <i class="bi bi-circle-fill position-absolute text-theme top-0 mt-n2 me-n2 fs-6px d-block text-center w-100"></i> 
                                <i class="bi bi-sliders h2 opacity-5 d-block my-1"></i>
                            </div>
                            <div class="fw-500 fs-10px text-inverse">PARAMETRES</div>
                        </a>
                    </div>
                </div> -->
            </div>
        </div>
        <div class="menu-item dropdown dropdown-mobile-full">
            <a href="#" data-bs-toggle="dropdown" data-bs-display="static" class="menu-link">
                <div class="menu-icon"><i class="bi bi-bell nav-icon"></i></div>
                <div class="menu-badge bg-theme"></div>
            </a>
            <!-- <div class="dropdown-menu dropdown-menu-end mt-1 w-300px fs-11px pt-1">
                <h6 class="dropdown-header fs-10px mb-1">NOTIFICATIONS</h6>
                <div class="dropdown-divider mt-1"></div>
                <a href="#" class="d-flex align-items-center py-10px dropdown-item text-wrap fw-semibold">
                    <div class="fs-20px">
                        <i class="bi bi-bag text-theme"></i>
                    </div>
                    <div class="flex-1 flex-wrap ps-3">
                        <div class="mb-1 text-inverse">Le stock de l'article est de '0'</div>
                        <div class="small text-inverse text-opacity-50">il y'a 4 min</div>
                    </div>
                    <div class="ps-2 fs-16px">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
                
                <hr class="my-0">
                <div class="py-10px mb-n2 text-center">
                    <a href="#" class="text-decoration-none fw-bold">Voir tout</a>
                </div>
            </div> -->
        </div>
        <div class="menu-item dropdown dropdown-mobile-full">
            <a href="#" data-bs-toggle="dropdown" data-bs-display="static" class="menu-link">
                <div class="menu-img online">
                    <img src="{{asset('hub/assets/img/logo.png')}}" alt="Profile" height="60">
                </div>
                <div class="menu-text d-sm-block d-none w-170px">
                    <!-- <span class="__cf_email__"data-cfemail="32474157405c535f57725351515d475c461c515d5f">[email&#160;protected]</span> -->
                    {{auth()->user()->name}}
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end me-lg-3 fs-11px mt-1">
                <a class="dropdown-item d-flex align-items-center" href="{{ route('profil') }}">PROFIL <i class="bi bi-person-circle ms-auto text-theme fs-16px my-n1"></i></a>
                <!-- <a class="dropdown-item d-flex align-items-center" href="settings.html">PARAMETRE<i class="bi bi-gear ms-auto text-theme fs-16px my-n1"></i></a> -->
                <div class="dropdown-divider"></div>
                <form id="form-logout">
                    @csrf
                    <!-- <button id="clignotant" class="btn btn-outline-danger btn-pill" title="Cliquez pour se deconnecter" type="submit">
                        <i class="fas fa-power-off"></i><span></span></a>
                    </button> -->
                    <button type="submit" class="dropdown-item d-flex align-items-center">
                        DECONNEXION<i class="fas fa-power-off ms-auto text-theme fs-16px my-n1"></i>
                    </button>
                </form>
                <!-- <a class="dropdown-item d-flex align-items-center" href="page_login.html">DDECONNEXION<i class="bi bi-toggle-off ms-auto text-theme fs-16px my-n1"></i></a> -->
            </div>
        </div>
    </div>


    <form class="menu-search" method="POST" name="header_search_form">
        <div class="menu-search-container">
            <div class="menu-search-icon"><i class="bi bi-search"></i></div>
            <div class="menu-search-input">
                <input type="text" class="form-control form-control-lg" placeholder="Search menu...">
            </div>
            <div class="menu-search-icon">
                <a href="#" data-toggle-class="app-header-menu-search-toggled" data-toggle-target=".app"><i
                        class="bi bi-x-lg"></i></a>
            </div>
        </div>
    </form>

</div>
<!--end header-->