@extends('ecommerce.public.layout')
@push('styles')
<style>
    .home-hero { isolation:isolate; box-shadow:0 24px 60px rgba(15,23,42,.18); }
    .home-hero .hero-orb { position:absolute; right:-70px; top:-90px; width:310px; height:310px; border-radius:50%; background:linear-gradient(135deg,rgba(59,130,246,.35),rgba(99,102,241,.05)); filter:blur(2px); animation:heroFloat 7s ease-in-out infinite; }
    .home-hero .hero-eyebrow { display:inline-flex; align-items:center; gap:8px; color:#bfdbfe; background:rgba(37,99,235,.16); border:1px solid rgba(147,197,253,.18); padding:6px 11px; border-radius:999px; font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; margin-bottom:14px; }
    .home-hero h1 { max-width:650px; font-size:clamp(1.75rem,4vw,2.6rem); line-height:1.12; }
    .home-hero .hero-btn-primary:hover, .home-hero .hero-btn-outline:hover { transform:translateY(-2px); }
    .products-heading { padding:0 2px; }
    .products-grid > div { animation:productReveal .5s both; }
    .products-grid > div:nth-child(2) { animation-delay:.07s; }
    .products-grid > div:nth-child(3) { animation-delay:.14s; }
    .products-grid .product-card { border:1px solid rgba(226,232,240,.7); }
    .products-grid .img-wrap::after { content:''; position:absolute; inset:0; background:linear-gradient(to top,rgba(15,23,42,.08),transparent 38%); pointer-events:none; }
    .products-grid .product-name { font-size:1rem; margin:3px 0 7px; }
    @keyframes heroFloat { 0%,100%{transform:translate3d(0,0,0)} 50%{transform:translate3d(-18px,18px,0)} }
    @keyframes productReveal { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }
    @media (prefers-reduced-motion:reduce) { .home-hero .hero-orb,.products-grid > div{animation:none} }
    @media (max-width:575px) { .products-heading{align-items:flex-end!important}.products-heading .section-subtitle{max-width:180px}.home-hero .hero-actions a{flex:1;justify-content:center;text-align:center} }
</style>
@endpush
@section('content')
    @if($company->description)
        <div class="hero-modern home-hero">
            <span class="hero-orb" aria-hidden="true"></span>
            <div class="hero-content">
                <div class="hero-eyebrow"><i class="bi bi-stars"></i> Votre boutique en ligne</div>
                <h1>Bienvenue chez {{ $company->name }}</h1>
                <p>{{ $company->description }}</p>
                <div class="hero-actions">
                    <a href="{{ url('/shop/products') }}" class="hero-btn-primary">Decouvrir nos produits</a>
                    <a href="{{ url('/shop/checkout') }}" class="hero-btn-outline">Voir le panier</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>{{ \App\Models\Product::where('status',1)->where('type',1)->where('qte','>',0)->count() }}</h3>
                        <span>Produits</span>
                    </div>
                    <div class="stat-item">
                        <h3>{{ \App\Models\Category::where('status',1)->count() }}</h3>
                        <span>Categories</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="products-heading d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="section-title">Nos produits</h2>
            <p class="section-subtitle mb-0">Les derniers produits ajoutes</p>
        </div>
        <a href="{{ url('/shop/products') }}" class="btn-outline-custom" style="font-size:.82rem;padding:.45rem 1rem;">
            Voir tout <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    @if($products->isEmpty())
        <div class="empty-state"><i class="bi bi-box-seam"></i><h5>Aucun produit disponible</h5></div>
    @else
        <div class="row g-3 products-grid product-grid-row">
            @foreach($products as $product)
                <div class="col-6 col-md-4">
                    <div class="product-card">
                        <div class="img-wrap">
                            @if($product->image && $product->image !== 'null')
                                <img src="{{ asset('images/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('icons/product-placeholder.svg') }}';">
                            @else
                                <img src="{{ asset('icons/product-placeholder.svg') }}" alt="Image par défaut pour {{ $product->name }}" loading="lazy">
                            @endif
                            @if($product->qte <= 5 && $product->qte > 0)
                                <span class="badge bg-warning text-dark">Plus que {{ $product->qte }}</span>
                            @elseif($product->qte > 0)
                                <span class="badge bg-success"><span class="pulse-dot me-1"></span>Disponible</span>
                            @else
                                <span class="badge bg-danger">Rupture</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($product->category)<div class="cat-label">{{ $product->category->name }}</div>@endif
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-price">{{ number_format($product->price_ttc ?? $product->price, 0, ',', ' ') }} <span class="currency">FCFA</span></div>
                            <div class="d-flex gap-2 mt-auto">
                                <input type="number" class="qty-input" value="1" min="1" max="{{ $product->qte ?: 1 }}">
                                <button class="add-to-cart-btn flex-grow-1" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price_ttc ?? $product->price }}" data-image="{{ ($product->image && $product->image !== 'null') ? asset('images/'.$product->image) : asset('icons/product-placeholder.svg') }}" {{ $product->qte <= 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-cart-plus"></i> Ajouter
                                </button>
                            </div>
                            <a href="{{ url('/shop/product/'.$product->id) }}" class="btn-outline-custom w-100 mt-2 justify-content-center" style="font-size:.78rem;padding:.4rem .8rem;">Detail <i class="bi bi-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
