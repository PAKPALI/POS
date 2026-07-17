@extends('ecommerce.public.layout')
@section('title', 'Commande - '.($company->name ?? 'Boutique'))
@push('styles')
<style>
    .checkout-cart-item { display:grid; grid-template-columns:64px minmax(0,1fr) auto; gap:14px; align-items:center; padding:14px 0; border-bottom:1px solid var(--border); animation:cartItemIn .28s ease both; }
    .checkout-cart-item:last-child { border-bottom:0; }
    .checkout-product-image { width:64px; height:64px; border-radius:11px; background:#f1f5f9; overflow:hidden; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:1.35rem; }
    .checkout-product-image img { width:100%; height:100%; object-fit:cover; }
    .checkout-product-name { font-weight:700; font-size:.9rem; color:var(--dark); margin:0 0 5px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .checkout-product-meta { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
    .checkout-unit-price { color:var(--muted); font-size:.76rem; }
    .checkout-qty { display:inline-flex; align-items:center; border:1px solid var(--border); border-radius:8px; overflow:hidden; background:#fff; }
    .checkout-qty button { width:28px; height:28px; border:0; background:transparent; color:var(--text); transition:.2s; }
    .checkout-qty button:hover { background:#eff6ff; color:var(--acc); }
    .checkout-qty span { min-width:25px; text-align:center; font-size:.78rem; font-weight:700; }
    .checkout-item-side { text-align:right; display:flex; align-items:center; gap:10px; }
    .checkout-line-total { min-width:92px; font-weight:800; color:var(--dark); font-size:.86rem; }
    .checkout-remove { border:0; background:#f8fafc; border-radius:9px; width:34px; height:34px; color:#94a3b8; transition:.2s; }
    .checkout-remove:hover { background:#fef2f2; color:#dc2626; transform:rotate(6deg); }
    [data-theme="dark"] .checkout-product-image,
    [data-theme="dark"] .checkout-qty,
    [data-theme="dark"] .checkout-remove { background:#1e293b; }
    [data-theme="dark"] .checkout-qty button:hover { background:#172554; }
    [data-theme="dark"] .checkout-remove:hover { background:#451a1a; }
    @keyframes cartItemIn { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:none} }
    @media(max-width:575px) {
        .checkout-cart-item { grid-template-columns:52px minmax(0,1fr); gap:10px; }
        .checkout-product-image { width:52px;height:52px; }
        .checkout-item-side { grid-column:2; justify-content:space-between; text-align:left; }
    }
</style>
@endpush
@section('content')
    <h2 class="section-title">Finaliser ma commande</h2>
    <p class="section-subtitle">Verifiez votre panier et completez vos informations</p>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card" style="border:none;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;">
                <div class="card-header" style="background:transparent;border-bottom:1px solid var(--border);padding:16px 20px;">
                    <h5 style="font-weight:700;margin:0;display:flex;align-items:center;gap:8px;font-size:1rem;"><i class="bi bi-bag"></i> Articles</h5>
                </div>
                <div class="card-body" style="padding:20px;">
                    <div id="emptyCartMessage" class="empty-state" style="display:none;">
                        <i class="bi bi-cart-x"></i><h5>Panier vide</h5><a href="{{ url('/shop') }}" class="btn-primary-custom mt-2">Decouvrir nos produits</a>
                    </div>
                    <div id="cartItemsList"></div>
                </div>
            </div>

            <div class="card" style="border:none;border-radius:var(--radius);box-shadow:var(--shadow);">
                <div class="card-header" style="background:transparent;border-bottom:1px solid var(--border);padding:16px 20px;">
                    <h5 style="font-weight:700;margin:0;display:flex;align-items:center;gap:8px;font-size:1rem;"><i class="bi bi-person"></i> Livraison</h5>
                </div>
                <div class="card-body" style="padding:20px;">
                    <form id="orderForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label style="font-weight:600;font-size:.82rem;color:var(--text);margin-bottom:4px;display:block;">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" required placeholder="Votre nom" style="border:2px solid var(--border);border-radius:8px;padding:.55rem .85rem;font-size:.88rem;">
                            </div>
                            <div class="col-sm-6">
                                <label style="font-weight:600;font-size:.82rem;color:var(--text);margin-bottom:4px;display:block;">Telephone <span class="text-danger">*</span></label>
                                <input type="tel" name="customer_phone" class="form-control" required placeholder="Votre telephone" style="border:2px solid var(--border);border-radius:8px;padding:.55rem .85rem;font-size:.88rem;">
                            </div>
                            <div class="col-sm-6">
                                <label style="font-weight:600;font-size:.82rem;color:var(--text);margin-bottom:4px;display:block;">Email</label>
                                <input type="email" name="customer_email" class="form-control" placeholder="Optionnel" style="border:2px solid var(--border);border-radius:8px;padding:.55rem .85rem;font-size:.88rem;">
                            </div>
                            <div class="col-sm-6">
                                <label style="font-weight:600;font-size:.82rem;color:var(--text);margin-bottom:4px;display:block;">Adresse</label>
                                <input type="text" name="customer_address" class="form-control" placeholder="Optionnel" style="border:2px solid var(--border);border-radius:8px;padding:.55rem .85rem;font-size:.88rem;">
                            </div>
                            <div class="col-12">
                                <label style="font-weight:600;font-size:.82rem;color:var(--text);margin-bottom:4px;display:block;">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Instructions particulieres..." style="border:2px solid var(--border);border-radius:8px;padding:.55rem .85rem;font-size:.88rem;"></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="cart" id="cartInput">
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card" style="border:none;border-radius:var(--radius);box-shadow:var(--shadow);position:sticky;top:92px;">
                <div class="card-header" style="background:transparent;border-bottom:1px solid var(--border);padding:16px 20px;">
                    <h5 style="font-weight:700;margin:0;display:flex;align-items:center;gap:8px;font-size:1rem;"><i class="bi bi-receipt"></i> Resume</h5>
                </div>
                <div class="card-body" style="padding:20px;">
                    <div class="d-flex justify-content-between py-2" style="color:var(--muted);font-size:.88rem;"><span>Sous-total</span><span id="summarySubtotal" style="font-weight:600;color:var(--text);">0 FCFA</span></div>
                    <div class="d-flex justify-content-between py-2" style="color:var(--muted);font-size:.88rem;"><span>Taxe</span><span id="summaryTax" style="font-weight:600;color:var(--text);">0 FCFA</span></div>
                    <hr style="border-color:var(--border);margin:12px 0;">
                    <div class="d-flex justify-content-between" style="font-weight:800;font-size:1.15rem;"><span>Total</span><span id="summaryTotal" style="color:var(--acc);">0 FCFA</span></div>
                    <button type="submit" class="btn-primary-custom w-100 justify-content-center mt-4" id="submitOrderBtn" form="orderForm" style="font-size:.95rem;padding:.8rem;border:none;cursor:pointer;">
                        <span id="orderBtnText"><i class="bi bi-check2-circle"></i> Passer la commande</span>
                        <span id="orderBtnLoader" class="spinner-border spinner-border-sm" style="display:none;"></span>
                    </button>
                    <p style="color:var(--muted);font-size:.78rem;text-align:center;margin-top:14px;margin-bottom:0;">
                        <i class="bi bi-shield-check text-success me-1"></i> Paiement a la livraison
                    </p>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
    var CHECKOUT_PRODUCT_IMAGES = @json($productImages ?? []);

    function checkoutProductImage(item) {
        return CHECKOUT_PRODUCT_IMAGES[String(item.product_id)]
            || item.image
            || DEFAULT_PRODUCT_IMAGE;
    }

    function renderCheckoutCart() {
        var cart = getCart(), list = $('#cartItemsList'), empty = $('#emptyCartMessage');
        if (cart.length === 0) { empty.show(); list.empty(); updateSummary(cart); return; }
        empty.hide();
        list.empty();
        cart.forEach(function(item, idx) {
            var total = item.price * item.quantity;
            var row = $('<div>', {class:'checkout-cart-item'});
            var visual = $('<div>', {class:'checkout-product-image'});
            $('<img>', {src:checkoutProductImage(item), alt:item.name, loading:'lazy'})
                .on('error', function(){ this.onerror = null; this.src = DEFAULT_PRODUCT_IMAGE; })
                .appendTo(visual);
            var info = $('<div>');
            $('<h6>', {class:'checkout-product-name', text:item.name}).appendTo(info);
            var meta = $('<div>', {class:'checkout-product-meta'}).appendTo(info);
            $('<span>', {class:'checkout-unit-price', text:fmt(item.price)+' FCFA / unité'}).appendTo(meta);
            var qty = $('<div>', {class:'checkout-qty', 'aria-label':'Quantité de '+item.name}).appendTo(meta);
            $('<button>', {type:'button', html:'<i class="bi bi-dash"></i>', 'aria-label':'Diminuer'}).on('click', function(){ changeCheckoutQty(idx, -1); }).appendTo(qty);
            $('<span>', {text:item.quantity}).appendTo(qty);
            $('<button>', {type:'button', html:'<i class="bi bi-plus"></i>', 'aria-label':'Augmenter'}).on('click', function(){ changeCheckoutQty(idx, 1); }).appendTo(qty);
            var side = $('<div>', {class:'checkout-item-side'});
            $('<div>', {class:'checkout-line-total', text:fmt(total)+' FCFA'}).appendTo(side);
            $('<button>', {type:'button', class:'checkout-remove', html:'<i class="bi bi-trash3"></i>', 'aria-label':'Retirer '+item.name}).on('click', function(){ removeCheckoutItem(idx); }).appendTo(side);
            row.append(visual, info, side);
            list.append(row);
        });
        updateSummary(cart);
    }
    function changeCheckoutQty(idx, delta) {
        var c = getCart();
        if (!c[idx]) return;
        c[idx].quantity = Math.max(1, c[idx].quantity + delta);
        saveCart(c);
        renderCheckoutCart();
    }
    function removeCheckoutItem(idx) { var c = getCart(); c.splice(idx, 1); saveCart(c); renderCheckoutCart(); }
    function updateSummary(cart) {
        var subtotal = cart.reduce(function(s,i){return s+(i.price*i.quantity);}, 0);
        $('#summarySubtotal').text(fmt(subtotal)+' FCFA');
        $('#summaryTax').text('0 FCFA');
        $('#summaryTotal').text(fmt(subtotal)+' FCFA');
    }
    function fmt(n) { return Math.round(Number(n) || 0).toLocaleString('fr-FR').replace(/\u202f/g, ' '); }
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        var cart = getCart();
        if (!cart.length) { alert('Panier vide'); return; }
        if (!$('[name="customer_name"]').val().trim()) { alert('Entrez votre nom'); return; }
        if (!$('[name="customer_phone"]').val().trim()) { alert('Entrez votre telephone'); return; }
        $('#cartInput').val(JSON.stringify(cart));
        $('#orderBtnText').hide(); $('#orderBtnLoader').show(); $('#submitOrderBtn').prop('disabled',true);
        $.ajax({
            type: 'POST', url: '{{ url("/shop/order/place") }}', data: $(this).serialize(),
            success: function(data) {
                $('#orderBtnText').show(); $('#orderBtnLoader').hide(); $('#submitOrderBtn').prop('disabled',false);
                if (data.status) { localStorage.removeItem(CART_KEY); window.location.href = '{{ url("/shop/success") }}?code=' + data.code; }
                else { alert(data.msg || 'Erreur'); }
            },
            error: function() { $('#orderBtnText').show(); $('#orderBtnLoader').hide(); $('#submitOrderBtn').prop('disabled',false); alert('Erreur serveur'); }
        });
    });
    $(function() { renderCheckoutCart(); });
    </script>
@endpush
@endsection
