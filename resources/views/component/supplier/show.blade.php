<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body">
            <table class="table table-striped border mb-0">
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Nom : </td>
                        <td>{{$Supplier->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Contact : </td>
                        <td>{{$Supplier->contact ?? '-'}}</td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>Téléphone : </td>
                        <td>{{$Supplier->phone ?? '-'}}</td>
                    </tr>
                    <tr>
                        <th scope="row">4</th>
                        <td>WhatsApp : </td>
                        <td>{{$Supplier->whatsapp ?? '-'}}</td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Nombre de produits : </td>
                        <td>{{$Supplier->products->count()}}</td>
                    </tr>
                    <tr>
                        <th scope="row">6</th>
                        <td>Créer par :</td>
                        <td>{{$Supplier->user->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">7</th>
                        <td>Créer le :</td>
                        <td>{{$Supplier->created_at->format('d-m-Y H:i:s')}}</td>
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