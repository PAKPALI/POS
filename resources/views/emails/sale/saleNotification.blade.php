<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification de vente</title>
    @include('emails.design.emailStyle')
</head>

<body>
<div class="container">

    <div class="header">
        <h2>{{ $company->name ?? config('app.name') }}</h2>
        <p style="color:red;">Notification de vente</p>
    </div>

    <div class="info">
        <h3>Nouvelle vente effectuée</h3>
        <p><strong>Vendeur(e) :</strong> {{ $sale->cashier }}</p>
        <p><strong>Code vente :</strong> {{ $sale->code }}</p>
        
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Qté</th>
                <th>PU (FCFA)</th>
                <th>PT (FCFA)</th>
            </tr>
        </thead>

        <tbody>
            @foreach($sale->saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name ?? 'N/A' }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ $detail->unit_price }}</td>
                    <td>{{ $detail->total_price }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">
                    Montant total:
                </td>
                <td style="font-weight: bold; color: red;">
                    {{ $sale->total_amount }} FCFA
                </td>
            </tr>
        </tfoot>
    </table>

    @include('emails.design.emailFooter')

</div>
</body>
</html>