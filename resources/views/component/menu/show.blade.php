<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body text-center">
            @if ($MenuProduct->image)
                <img class="mb-5" src="{{ asset('images/' . $MenuProduct->image) }}" alt="Image du produit" style="width: 300px; height: auto;">
            @else
                Pas d'image
            @endif

            <div class="card">
                <div class="card-header text-center">
                    <h6>INFORMATIONS SUR LE MENU</h6>
                </div>
            </div>
            <table class="table table-striped border mb-0 text-center">
                {{-- <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">First</th>
                        <th scope="col">Last</th>
                        <th scope="col">Handle</th>
                    </tr>
                </thead> --}}
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Catégorie : </td>
                        <td>{{$MenuProduct->category->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Nom : </td>
                        <td>{{$MenuProduct->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>Quantité : </td>
                        <td>{{$MenuProduct->qte}}</td>
                    </tr>
                    <tr>
                        <th scope="row">4</th>
                        <td>Marge : </td>
                        <td>{{$MenuProduct->margin}}</td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Prix unitaire : </td>
                        <td>{{$MenuProduct->price}}</td>
                    </tr>
                    <tr>
                        <th scope="row">6</th>
                        <td>Prix d'achat : </td>
                        <td>{{$MenuProduct->purchase_price}}</td>
                    </tr>
                    <tr>
                        <th scope="row">7</th>
                        <td>Bénefice: </td>
                        <td>{{$MenuProduct->profit}}</td>
                    </tr>
                    <tr>
                        <th scope="row">8</th>
                        <td>Créer par :</td>
                        <td>{{$MenuProduct->user->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">9</th>
                        <td>Créer le :</td>
                        <td>{{$MenuProduct->created_at->format('d-m-Y H:i:s')}}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="card">
                <div class="card-header text-center mt-4">
                    <h6>INFORMATIONS SUR LES PRODUITS DU MENU</h6>
                </div>
            </div>
            <table class="table table-striped border text-center">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Quantité</th>
                        {{-- <th scope="col">Handle</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($MenuProduct->MenuProducts as $item)
                        <tr>
                            <td>
                                @if ($item->product->image)
                                    <img class="mb-0" src="{{ asset('images/' . $item->product->image) }}" alt="Image du produit" style="width: 40px; height: auto;">
                                @else
                                    Pas d'image
                                @endif
                            </td>
                            <td class="@if($item->product->qte<=$item->product->margin) bg-danger @endif">{{ $item->product->name }} ({{ $item->product->qte }})</td>
                            <td>{{ $item->quantity }}</td>
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