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
                        <td>Nom : </td>
                        <td>{{$Company->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Email : </td>
                        <td>{{$Company->email}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Adress: </td>
                        <td>{{$Company->adress}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Numéro 1: </td>
                        <td>{{$Company->number1}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Numéro 2: </td>
                        <td>{{$Company->number2?? '-'}}</td>
                    </tr>
                    <tr>
                        <th scope="row">1</th>
                        <td>Message: </td>
                        <td>{{$Company->message}}</td>
                    </tr>
                    {{-- <tr>
                        <th scope="row">2</th>
                        <td>Creer par :</td>
                        <td>{{$Category->user->name}}</td>
                    </tr> --}}
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