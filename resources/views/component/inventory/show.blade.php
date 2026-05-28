<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body">
            <table class="table table-striped border mb-0">
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
                        <td>Type : </td>
                        <td><span class="badge bg-{{ $Inventory->type === 1 ? 'primary' : 'danger' }}">{{ $Inventory->type === 1 ? 'Entrée' : 'Sortie' }}</span></td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Nom : </td>
                        <td>{{$Inventory->product->name}}</td>
                    </tr>
                    
                    <tr>
                        <th scope="row">3</th>
                        <td>Quantité avant :</td>
                        <td>{{$Inventory->qte_before}}</td>
                    </tr>

                    <tr>
                        <th scope="row">4</th>
                        <td>Quantité ajoutée :</td>
                        <td>{{$Inventory->qte_added}}</td>
                    </tr>

                    <tr>
                        <th scope="row">5</th>
                        <td>Quantité après :</td>
                        <td>{{$Inventory->qte_after}}</td>
                    </tr>

                    <tr>
                        <th scope="row">6</th>
                        <td>Note :</td>
                        <td>{{$Inventory->note}}</td>
                    </tr>

                    <tr>
                        <th scope="row">7</th>
                        <td>Créé par :</td>
                        <td>{{$Inventory->user->name}}</td>
                    </tr>

                    <tr>
                        <th scope="row">8</th>
                        <td>Créé le :</td>
                        <td>{{$Inventory->created_at}}</td>
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