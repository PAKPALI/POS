<div id="stripedRows" class="mb-5">

    <div class="card">
        <div class="card-body text-center">

            <!-- ICON CAISSE -->
            <div class="mb-4">
                <i class="bi bi-cash-stack" style="font-size: 60px; color: #28a745;"></i>
            </div>

            <!-- TABLE INFOS -->
            <table class="table table-striped border mb-0 text-center">

                <tbody>

                    <tr>
                        <th scope="row">1</th>
                        <td>Code :</td>
                        <td><strong>{{ $cashAccount->code }}</strong></td>
                    </tr>

                    <tr>
                        <th scope="row">2</th>
                        <td>Nom :</td>
                        <td>{{ $cashAccount->name }}</td>
                    </tr>

                    <tr>
                        <th scope="row">3</th>
                        <td>Solde :</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ number_format($cashAccount->balance, 0, ',', ' ') }} {{ $cashAccount->currency }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">4</th>
                        <td>Devise :</td>
                        <td>{{ $cashAccount->currency }}</td>
                    </tr>

                    <tr>
                        <th scope="row">5</th>
                        <td>Caisse principale :</td>
                        <td>
                            @if($cashAccount->is_default)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">6</th>
                        <td>Statut :</td>
                        <td>
                            @if($cashAccount->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">7</th>
                        <td>Description :</td>
                        <td>{{ $cashAccount->description ?? '---' }}</td>
                    </tr>

                    <tr>
                        <th scope="row">8</th>
                        <td>Créé le :</td>
                        <td>{{ $cashAccount->created_at->format('d-m-Y H:i:s') }}</td>
                    </tr>

                </tbody>
            </table>

        </div>

        <!-- ARROWS (ton style POS conservé) -->
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>

    </div>
</div>