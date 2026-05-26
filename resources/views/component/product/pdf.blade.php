<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title>Liste des produits</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 6px;
                text-align: left;
            }

            th {
                background: #f2f2f2;
            }
        </style>
    </head>

    <body>
        <div style="text-align: center; margin-bottom: 10px;">
            <h1 style="margin: 0; font-size: 22px; font-weight: bold; text-transform: uppercase;">
                {{strtoupper($company->name ?? config('app.name'))}}
            </h1>
            <p style="margin: 0; font-size: 12px; color: #555;">
                <h3><u>Liste des produits</u></h3>
            </p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Quantité</th>
                    <th>Prix Achat</th>
                    <th>Prix Vente</th>
                    <th>Prix TTC</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                @php
                $profit = $product->price - $product->purchase_price;
                @endphp
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->qte }}</td>
                    <td>{{ number_format($product->purchase_price, 0, ',', ' ') }}</td>
                    <td>{{ number_format($product->price, 0, ',', ' ') }}</td>
                    <td>{{ number_format($product->price_ttc, 0, ',', ' ') }}</td>
                    <td>{{ number_format($profit, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="text-align: center; margin-top: 50px;">
            <p style="margin: 0; font-size: 12px; color: #555;"><h3><u>PRO-SELLER</u></h3></p>
        </div>

    </body>
</html>