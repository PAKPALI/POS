<div id="stripedRows" class="mb-5">

    <div class="card">
        <div class="card-body text-center">

            <!-- ICON TRANSACTION -->
            <div class="mb-4">
                @if($transaction->type == 'IN')
                    <i class="bi bi-arrow-left-right text-success" style="font-size: 60px;"></i>
                @elseif($transaction->type == 'OUT')
                    <i class="bi bi-arrow-left-right text-danger" style="font-size: 60px;"></i>
                @else
                    <i class="bi bi-arrow-left-right text-info" style="font-size: 60px;"></i>
                @endif
            </div>

            <!-- TABLE INFOS -->
            <table class="table table-striped border mb-0 text-center">
                <tbody>

                    <tr>
                        <th scope="row">1</th>
                        <td>Type :</td>
                        <td>
                            @if($transaction->type == 'IN')
                                <span class="badge bg-success">Entrée</span>
                            @elseif($transaction->type == 'OUT')
                                <span class="badge bg-danger">Sortie</span>
                            @else
                                <span class="badge bg-primary">Transfert</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">2</th>
                        <td>Montant :</td>
                        <td>
                            <span class="badge bg-warning">
                                {{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">3</th>
                        <td>Caisse source :</td>
                        <td>
                            {{ $transaction->fromCash->name ?? '---' }}
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">4</th>
                        <td>Caisse destination :</td>
                        <td>
                            {{ $transaction->toCash->name ?? '---' }}
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">5</th>
                        <td>Utilisateur :</td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ $transaction->user->name }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">6</th>
                        <td>Date :</td>
                        <td>
                            {{ $transaction->created_at->format('d-m-Y H:i:s') }}
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">7</th>
                        <td>Description :</td>
                        <td>
                            <div style=" padding:15px; border-radius:8px; max-height:150px; overflow-y:auto;">
                                <strong>{{ $transaction->description ?? '---' }}</strong> 
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>

        <!-- ARROWS STYLE -->
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>

    </div>
</div>