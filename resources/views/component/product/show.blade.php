<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body text-center">
            @if ($Product->image)
                <img class="mb-5" src="{{ asset('images/' . $Product->image) }}" alt="Image du produit" style="width: 400px; height: auto;">
            @else
                Pas d'image
            @endif
        
            <table class="table table-striped border mb-0 text-center">
                <!-- <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">First</th>
                        <th scope="col">Last</th>
                        <th scope="col">Handle</th>
                    </tr>
                </thead> -->
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Catégorie : </td>
                        <td>{{$Product->category->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Nom : </td>
                        <td>{{$Product->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>Quantité : </td>
                        <td>{{$Product->qte}}</td>
                    </tr>
                    <tr>
                        <th scope="row">4</th>
                        <td>Marge : </td>
                        <td>{{$Product->margin}}</td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Prix unitaire : </td>
                        <td>{{$Product->price}}</td>
                    </tr>
                    <tr>
                        <th scope="row">6</th>
                        <td>Prix d'achat : </td>
                        <td>{{$Product->purchase_price}}</td>
                    </tr>
                    <tr>
                        <th scope="row">7</th>
                        <td>Bénefice: </td>
                        <td>{{$Product->profit}}</td>
                    </tr>
                    <tr>
                        <th scope="row">8</th>
                        <td>Créer par :</td>
                        <td>{{$Product->user->name}}</td>
                    </tr>
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