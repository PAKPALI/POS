@extends('ecommerce.public.layout')
@section('title', $product->name.' - '.($company->name ?? 'Boutique'))
@section('content')
    <a href="{{ url()->previous() }}" class="btn-outline-custom mb-4" style="font-size:.82rem;padding:.45rem 1rem;">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>

    <div class="row g-4">
        <div class="col-lg-6">
            <div style="border-radius:var(--radius);overflow:hidden;background:#f1f5f9;position:relative;">
                @if($product->image && $product->image !== 'null')
                    <img src="{{ asset('images/'.$product->image) }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('icons/product-placeholder.svg') }}';" style="width:100%;max-height:450px;object-fit:cover;display:block;">
                @else
                    <img src="{{ asset('icons/product-placeholder.svg') }}" alt="Image par défaut pour {{ $product->name }}" loading="lazy">
            </div>
            <div class="mt-3 d-flex gap-2">
                @if($product->qte > 0)
                    <span class="badge bg-success d-inline-flex align-items-center gap-1" style="font-size:.75rem;padding:5px 12px;border-radius:6px;">
                        <span class="pulse-dot"></span> En stock ({{ $product->qte }})
                    </span>
                @else
                    <span class="badge bg-danger d-inline-flex align-items-center" style="font-size:.75rem;padding:5px 12px;border-radius:6px;">Rupture</span>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div style="border-radius:var(--radius);background:var(--card-bg);box-shadow:var(--shadow);height:100%;padding:32px;">
                @if($product->category)
                    <div style="font-size:.75rem;font-weight:600;color:var(--acc);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">{{ $product->category->name }}</div>
                @endif
                <h1 style="font-weight:800;font-size:1.65rem;color:var(--dark);margin:4px 0 10px;">{{ $product->name }}</h1>
                <div style="font-weight:800;font-size:2rem;color:var(--acc);margin-bottom:20px;">
                    {{ number_format($product->price_ttc ?? $product->price, 0, ',', ' ') }}
                    <span style="font-size:1rem;font-weight:600;color:var(--muted);">FCFA</span>
                </div>
                <hr style="border-color:var(--border);margin:20px 0;">
                @if($product->qte > 0)
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <input type="number" id="detailQty" class="qty-input" value="1" min="1" max="{{ $product->qte }}" style="width:80px;padding:.6rem;">
                        <button class="btn-primary-custom" style="padding:.7rem 2rem;" onclick='addToCart({{ $product->id }}, @json($product->name), {{ $product->price_ttc ?? $product->price }}, parseInt($("#detailQty").val()), @json(($product->image && $product->image !== "null") ? asset("images/".$product->image) : asset("icons/product-placeholder.svg")))'>
                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                        </button>
                    </div>
                @endif
                <a href="{{ url('/shop/checkout') }}" class="btn-outline-custom" style="padding:.65rem 1.5rem;">
                    <i class="bi bi-bag-check"></i> Commander directement
                </a>
            </div>
        </div>
    </div>
@endsection
