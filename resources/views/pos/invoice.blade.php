<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 80mm;
            margin: 0 auto;
            color: #333;
        }

        .receipt {
            border: 1px solid #ddd;
            padding: 10px;
            max-width: 80mm;
        }

        .header {
            text-align: center;
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
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        .items-table th, .items-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: right;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .items-table .item-details {
            text-align: left;
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
            color: #888;
        }
    </style>
</head>
<body>

<div class="receipt">
    <div class="header">
        <h1>{{config('app.name')}}</h1>
        <p>Lomé-Togo</p>
        <p>Tél : 0123456789</p>
    </div>

    <div class="receipt-info">
        <p>Date : {{ $sale->created_at->format('d/m/Y') }}</p>
        <p>Réf : #{{ $sale->code }}</p>
        <p>Caissier : {{ $sale->cashier ?? 'Nom du Caissier' }}</p>
    </div>

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
            @foreach ($saleDetails as $detail)
                <tr>
                    <td class="item-details">{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ number_format($detail->unit_price, 2) }} FCFA</td>
                    <td>{{ number_format($detail->total_price, 2) }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Total : {{ number_format($sale->total_amount, 2) }} FCFA</p>
        <p>Taxe : {{ 0 }} FCFA</p>
        <p>Net à Payer : {{ number_format($sale->total_amount + 0) }} FCFA</p>
    </div>

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
            width: 80mm;
            margin: 0 auto;
            color: #333;
        }

        .receipt {
            border: 1px solid #ddd;
            padding: 10px;
            max-width: 80mm;
        }

        .header {
            text-align: center;
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
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        .items-table th, .items-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: right;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .items-table .item-details {
            text-align: left;
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
            color: #888;
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
    </div>

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

    <div class="total">
        <p>Total : 49.00 €</p>
        <p>Taxe : 2.50 €</p>
        <p>Net à Payer : 51.50 €</p>
    </div>

    <div class="footer">
        <p>Merci pour votre achat !</p>
        <p>Conservez ce reçu pour référence.</p>
    </div>
</div>

</body>
</html> -->
