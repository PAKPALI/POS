<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body text-center">
            <table class="table table-striped border mb-0 text-center">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Image</th>
                        <th scope="col">Produit</th>
                        <th scope="col">Quantité</th>
                        <th scope="col">prix unitaire</th>
                        <th scope="col">prix total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $n = 1;
                    @endphp
                    @foreach ($Sale->saleDetails as $detail)
                        <tr style="text-align: center;">
                            <th scope="row">{{$n++}}</th>
                            <th scope="row">
                                @if ($detail->product->image)
                                    <img class="mb-0" src="{{ asset('images/' . $detail->product->image) }}" alt="Image du produit" style="width: 75px; height: auto;">
                                @else
                                    Pas d'image
                                @endif
                            </th>
                            <td>{{$detail->product->name}}</td>
                            <td>{{$detail->quantity}}</td>
                            <td>{{$detail->unit_price}}</td>
                            <td>{{$detail->total_price}}</td>
                        </tr>
                    @endforeach
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
            <pre><code class="xml" data-url="assets/data/table-elements/code-3.json"></code></pre>
        </div>
    </div>
</div>