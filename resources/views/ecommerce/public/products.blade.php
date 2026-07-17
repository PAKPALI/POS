@extends('ecommerce.public.layout')
@section('title', 'Tous nos produits - '.($company->name ?? 'Boutique'))
@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="section-title">Tous nos produits</h2>
            <p class="section-subtitle mb-0">{{ $products->total() }} produit(s)</p>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="empty-state"><i class="bi bi-box-seam"></i><h5>Aucun produit disponible</h5></div>
    @else
        <div class="row g-3 product-grid-row">
            @foreach($products as $product)
                <div class="col-6 col-md-4">
                    <div class="product-card">
                        <div class="img-wrap">
                            @if($product->image)
                                <img src="{{ asset('images/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
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
                                <button class="add-to-cart-btn flex-grow-1" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price_ttc ?? $product->price }}" data-image="{{ $product->image ? asset('images/'.$product->image) : asset('images/product-placeholder.svg') }}" {{ $product->qte <= 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-cart-plus"></i> Ajouter
                                </button>
                            </div>
                            <a href="{{ url('/shop/product/'.$product->id) }}" class="btn-outline-custom w-100 mt-2 justify-content-center" style="font-size:.78rem;padding:.4rem .8rem;">Detail <i class="bi bi-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $products->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
