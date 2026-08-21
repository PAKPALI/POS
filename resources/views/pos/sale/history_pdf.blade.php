<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des ventes</title>
    <style>
        @page { margin: 25px 30px; }
        body { color: #222; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1, h2, h3, p { margin: 0; }
        .header { margin-bottom: 18px; text-align: center; }
        .header h1 { font-size: 20px; }
        .header h2 { margin-top: 4px; color: #b42318; font-size: 15px; }
        .header p { margin-top: 4px; color: #555; }
        .summary { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .summary td { width: 20%; padding: 8px 4px; border: 1px solid #bbb; text-align: center; }
        .summary strong { display: block; margin-bottom: 3px; font-size: 12px; }
        .sale { margin-bottom: 18px; page-break-inside: avoid; }
        .sale-title { padding: 7px; background: #292d32; color: #fff; }
        .sale-title table { width: 100%; color: #fff; }
        .sale-title td { border: 0; }
        table.details { width: 100%; border-collapse: collapse; }
        .details th, .details td { padding: 6px 5px; border: 1px solid #bbb; }
        .details th { background: #e9ecef; text-align: left; }
        .number { text-align: right; white-space: nowrap; }
        .sale-totals { width: 100%; border-collapse: collapse; }
        .sale-totals td { padding: 4px 6px; border: 1px solid #bbb; }
        .sale-totals .label { text-align: right; font-weight: bold; }
        .empty { padding: 30px; border: 1px solid #bbb; text-align: center; }
        .ranking { margin-top: 22px; page-break-before: auto; }
        .ranking h2 { margin-bottom: 8px; color: #b42318; font-size: 15px; }
        .ranking table { width: 100%; border-collapse: collapse; }
        .ranking th, .ranking td { padding: 7px 6px; border: 1px solid #bbb; }
        .ranking th { background: #292d32; color: #fff; text-align: left; }
        .rank { width: 8%; text-align: center; font-weight: bold; }
        .footer { margin-top: 15px; color: #666; text-align: center; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ strtoupper($company->name ?? config('app.name')) }}</h1>
        <h2>HISTORIQUE DES VENTES</h2>
        <p>Du {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</p>
        @if ($search !== '')
            <p>Recherche appliquée : « {{ $search }} »</p>
        @endif
    </div>

    <table class="summary">
        <tr>
            <td><strong>{{ $summary['sales_count'] }}</strong>Ventes</td>
            <td><strong>{{ $summary['products_quantity'] }}</strong>Produits vendus</td>
            <td><strong>{{ number_format($summary['total_amount'], 0, ',', ' ') }}</strong>Total FCFA</td>
            <td><strong>{{ number_format($summary['total_received'], 0, ',', ' ') }}</strong>Reçu FCFA</td>
            @if ($canViewFinancials)
                <td><strong>{{ number_format($summary['total_profit'], 0, ',', ' ') }}</strong>Bénéfice FCFA</td>
            @endif
        </tr>
    </table>

    @forelse ($sales as $sale)
        <div class="sale">
            <div class="sale-title">
                <table>
                    <tr>
                        <td><strong>Vente #{{ $sale->code }}</strong></td>
                        <td style="text-align:center;">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align:right;">Caissier : {{ $sale->cashier ?? 'Non renseigné' }}</td>
                    </tr>
                    @if ($sale->client)
                        <tr>
                            <td colspan="3">Client : {{ $sale->client->name }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <table class="details">
                <thead>
                    <tr>
                        <th style="width:45%;">Produit</th>
                        <th style="width:10%;" class="number">Quantité</th>
                        <th style="width:22%;" class="number">P.U (FCFA)</th>
                        <th style="width:23%;" class="number">P.T (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->name ?? 'Produit supprimé' }}</td>
                            <td class="number">{{ $detail->quantity }}</td>
                            <td class="number">{{ number_format($detail->unit_price, 0, ',', ' ') }}</td>
                            <td class="number">{{ number_format($detail->total_price, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="sale-totals">
                <tr>
                    <td class="label">Montant total :</td>
                    <td class="number">{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</td>
                    <td class="label">Montant reçu :</td>
                    <td class="number">{{ number_format($sale->received_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td class="label">Monnaie rendue :</td>
                    <td class="number">{{ number_format($sale->remaining_amount, 0, ',', ' ') }} FCFA</td>
                    @if ($canViewFinancials)
                        <td class="label">Bénéfice :</td>
                        <td class="number">{{ number_format($sale->total_profit, 0, ',', ' ') }} FCFA</td>
                    @endif
                </tr>
            </table>
        </div>
    @empty
        <div class="empty">Aucune vente ne correspond aux filtres sélectionnés.</div>
    @endforelse

    @if ($topProducts->isNotEmpty())
        <div class="ranking">
            <h2>CLASSEMENT DES PRODUITS LES PLUS VENDUS</h2>
            <table>
                <thead>
                    <tr>
                        <th class="rank">Rang</th>
                        <th>Produit</th>
                        <th class="number">Quantité vendue</th>
                        <th class="number">Chiffre d’affaires (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topProducts as $index => $product)
                        <tr>
                            <td class="rank">{{ $index + 1 }}</td>
                            <td>{{ $product['name'] }}</td>
                            <td class="number">{{ $product['quantity'] }}</td>
                            <td class="number">{{ number_format($product['total_amount'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
