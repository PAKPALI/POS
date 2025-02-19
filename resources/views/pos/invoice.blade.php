<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 100%;
            margin: 0 auto;
            color: #333;
            page-break-inside: avoid;
        }
        .container { page-break-inside: avoid; }

        .receipt {
            border: 1px solid #ddd;
            padding: 8px;
            max-width: 100%;
        }

        .header {
            text-align: center;
            background-color: white;
            color: #000;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .receipt-info {
            margin: 8px 0;
            font-size: 12px;
        }

        .items-table {
            width: 100%;
            margin-top: 3px;
            font-size: 12px;
        }

        .items-table th, .items-table td {
            /* border: 1px solid #333; */
            padding: 8px;
            text-align: right;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
        }

        .items-table .item-details {
            text-align: center;
        }

        .total {
            font-size: 13px;
            font-weight: bold;
            text-align: right;
            margin-top: 2px;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 5px;
            background-color: white;
            color: #000;
        }
    </style>
    <!-- <style>
        body, .receipt {
            margin: 0;
            padding: 0;
            font-size: 10px; /* Réduction de la taille de police */
            width: 80mm; /* largeur typique pour une imprimante thermique */
        }
        .header, .footer {
            font-size: 10px;
            margin: 0;
            padding: 5px 0;
        }
        .items-table th, .items-table td {
            padding: 2px 5px;
        }
        .total p {
            margin: 2px 0;
            font-size: 10px;
        }
        .container, .receipt, .items-table, .total, .footer {
            page-break-inside: avoid;
            page-break-after: auto;
        }

    </style> -->

</head>
<body>

<div class="receipt">
    <div class="header">
        <h1>{{config('app.name')}}</h1>
        <p>Lomé-Togo</p>
        <p>Tél : 0123456789</p>
    </div>

    <hr />

    <div class="receipt-info">
        <!-- <p>Date : {{ $sale->created_at->format('d/m/Y') }}</p>
        <p></p>
        <p>Caissier : {{ $sale->cashier ?? 'Nom du Caissier' }}</p> -->
        <table class="items-table">
            <tbody>
                    <tr>
                        <td class="item-details"> Date : {{ $sale->created_at->format('d/m/Y') }}</td>
                        <td class="item-details">Réf : #{{ $sale->code }}</td>
                        <td class="item-details">Caissier : {{ $sale->cashier ?? 'Nom du Caissier' }}</td>
                    </tr>
            </tbody>
        </table>
    </div>

    <hr />

    <table class="items-table">
        <thead>
            <tr>
                <th class="item-details">Produit</th>
                <th class="item-details">Quantité</th>
                <th class="item-details">P.U (FCFA)</th>
                <th class="item-details">P.T (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saleDetails as $detail)
                <tr>
                    <td class="item-details"> 
                        {{ $detail->product ? $detail->product->name : 'Produit non disponible' }}
                    </td>
                    <td class="item-details">{{ $detail->quantity }}</td>
                    <td class="item-details">{{ number_format($detail->unit_price, 2) }} </td>
                    <td class="item-details">{{ number_format($detail->total_price, 2) }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr />

    <div class="total">
        <!-- <p>Total : {{ number_format($sale->total_amount, 2) }} FCFA</p>
        <p>Taxe : {{ 0 }} FCFA</p> -->
        @if ($sale->code_promo)
            <p>Montant initial : {{ number_format($sale->amount_init) }} FCFA</p>
            <p>Réduction : {{ number_format($sale->discount) }} FCFA</p>
            <p>Montant payé : {{ number_format($sale->total_amount) }} FCFA</p>
            <p>Montant donné : {{ number_format($sale->received_amount) }} FCFA</p>
            <p>Monnaie rendue : {{ number_format($sale->remaining_amount) }} FCFA</p>
        @else
            <p>Montant payé : {{ number_format($sale->total_amount) }} FCFA</p>
            <p>Montant donné : {{ number_format($sale->received_amount) }} FCFA</p>
            <p>Monnaie rendue : {{ number_format($sale->remaining_amount) }} FCFA</p>
        @endif
        
    </div>

    <hr />

    <div class="footer">
        <p>Merci pour votre achat !</p>
        <p>Conservez ce reçu pour référence.</p>
    </div>
</div>

</body>
</html>

<!-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 100%;
            margin: 0 auto;
            color: #333;
            page-break-inside: avoid;
        }
        .container { page-break-inside: avoid; }

        .receipt {
            border: 1px solid #ddd;
            padding: 10px;
            max-width: 100%;
        }

        .header {
            text-align: center;
            background-color: white;
            color: #000;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .receipt-info {
            margin: 10px 0;
            font-size: 12px;
        }

        .items-table {
            width: 100%;
            margin-top: 10px;
            font-size: 12px;
        }

        .items-table th, .items-table td {
            /* border: 1px solid #333; */
            padding: 5px;
            text-align: right;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
        }

        .items-table .item-details {
            text-align: center;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
            background-color: white;
            color: #000;
        }
    </style>
</head>
<body>

<div class="receipt">
    <div class="header">
        <h1>Nom du Magasin</h1>
        <p>Adresse du Magasin</p>
        <p>Tél : 0123456789</p>
    </div>

    <div class="receipt-info">
        <p>Date : 01/10/2024</p>
        <p>Réf : #123456</p>
        <p>Caissier : Nom du Caissier</p>
        <table class="items-table">
            <tbody>
                    <tr>
                        <td class="item-details"> Date : 01/10/2024</td>
                        <td class="item-details">Réf : #123456</td>
                        <td class="item-details">Caissier : Nom du Caissier</td>
                    </tr>
            </tbody>
        </table>
    </div>
    <hr />
    <table class="items-table">
        <thead>
            <tr>
                <th class="item-details">Produit</th>
                <th>Qte</th>
                <th>PU</th>
                <th>PT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="item-details">Produit A</td>
                <td>2</td>
                <td>5.00 €</td>
                <td>10.00 €</td>
            </tr>
            <tr>
                <td class="item-details">Produit B</td>
                <td>1</td>
                <td>15.00 €</td>
                <td>15.00 €</td>
            </tr>
            <tr>
                <td class="item-details">Produit C</td>
                <td>3</td>
                <td>8.00 €</td>
                <td>24.00 €</td>
            </tr>
        </tbody>
    </table>
    <hr />
    <div class="total">
        <p>Net à Payer : 51.50 €</p>
    </div>

    <div class="footer">
        <p>Merci pour votre achat !</p>
        <p>Conservez ce reçu pour référence.</p>
    </div>
</div>

</body>
</html> -->
