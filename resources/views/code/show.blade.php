<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body">
            <table class="table table-striped border mb-0">
                <div class=" mb-5 bg-light text-center">
                    <img src="{{ asset('storage/' . $CodePromo->qr_code) }}" alt="Code Barre">
                </div>
                
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Nom : </td>
                        <td>{{$CodePromo->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Code : </td>
                        <td>{{$CodePromo->code}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Pourcentage : </td>
                        <td>{{$CodePromo->percents}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Description : </td>
                        <td>{{$CodePromo->comments}}</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Créer par :</td>
                        <td>{{$CodePromo->user->name}}</td>
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