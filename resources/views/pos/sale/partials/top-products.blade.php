@if ($mostSoldProducts->isNotEmpty())
    <section class="pos-top-sales" aria-labelledby="posTopSalesTitle">
        <header class="pos-top-sales-header">
            <div>
                <span class="pos-top-sales-eyebrow"><i class="bi bi-trophy" aria-hidden="true"></i> Classement du jour</span>
                <h3 id="posTopSalesTitle">Produits les plus vendus</h3>
            </div>
            <span class="pos-top-sales-total">{{ $mostSoldProducts->sum('total_quantity') }} unités</span>
        </header>
        <div class="pos-top-sales-table-wrap">
            <table class="pos-top-sales-table">
                <thead>
                    <tr>
                        <th scope="col" class="pos-top-sales-rank-column">#</th>
                        <th scope="col">Produit</th>
                        <th scope="col" class="pos-top-sales-price-column">Prix <small>FCFA</small></th>
                        <th scope="col" class="pos-top-sales-quantity-column">Qté</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mostSoldProducts as $productDetail)
                        @php
                            $product = $productDetail->product;
                            $soldProductImage = $product && $product->image && $product->image !== 'null'
                                && file_exists(public_path('images/'.$product->image))
                                    ? asset('images/'.$product->image)
                                    : asset('icons/product-placeholder.svg');
                        @endphp
                        <tr>
                            <td><span class="pos-top-sales-rank" aria-label="Position {{ $loop->iteration }}">{{ $loop->iteration }}</span></td>
                            <td>
                                <div class="pos-top-sales-product">
                                    <img class="pos-top-sales-image" src="{{ $soldProductImage }}" alt="" width="40" height="40">
                                    <strong>{{ $product->name ?? 'Produit supprimé' }}</strong>
                                </div>
                            </td>
                            <td class="pos-top-sales-price">{{ number_format((float) ($product->price ?? 0), 0, ',', ' ') }}</td>
                            <td><span class="pos-top-sales-quantity">{{ (int) $productDetail->total_quantity }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@else
    <div class="pos-sales-empty no-sale">
        <i class="bi bi-bar-chart" aria-hidden="true"></i>
        <strong>Aucune vente pour le moment</strong>
        <p>Le classement s’actualisera ici après la première vente du jour.</p>
    </div>
@endif
