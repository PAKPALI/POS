<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rapport de vente</title>
    @include('pdf.design.pdfStyle')
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>{{ $company->name ?? config('app.name') }}</h2>
            <p style="color:red;"><h3>Rapport des ventes</h3></p>
        </div>

        @foreach($sales as $sale)
            <div class="info">
                <h3>Vente #{{ $sale->code }}</h3>
                <p>Vendeur : {{ $sale->cashier }}</p>
                <p>Heure : {{ $sale->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <table >
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
        @endforeach
        <table>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">
                        Montant global:
                    </td>
                    <td style="font-weight: bold; color: Blue;">
                        {{ $sales->sum('total_amount') }} FCFA
                    </td>
                </tr>
            </tbody>
        </table>

        {{--<table>
            <tr>
                <td colspan="4" style="background:#eee; font-weight:bold;">
                    Vente #{{ $sale->code }} - {{ $sale->cashier }} - {{ $sale->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Qté</th>
                    <th>PU (FCFA)</th>
                    <th>PT (FCFA)</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sales as $sale)
                    
                    @foreach($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->name ?? 'N/A' }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->unit_price }}</td>
                            <td>{{ $detail->total_price }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:bold;">
                            Total vente
                        </td>
                        <td style="color:red;font-weight:bold;">
                            {{ $sale->total_amount }} FCFA
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align:right;font-weight:bold;">
                        TOTAL GLOBAL
                    </td>
                    <td style="color:blue;font-weight:bold;">
                        {{ $sales->sum('total_amount') }} FCFA
                    </td>
                </tr>
            </tbody>
        </table>--}}

        @include('pdf.design.pdfFooter')
    </div>
</body>

</html>