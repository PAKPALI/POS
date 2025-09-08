<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px; /* Ajuster la taille de la police pour l'impression */
        }

        .receipt {
            width: 100%; /* Ajuster pour un format d'impression standard (80mm de large pour les imprimantes thermiques) */
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
        }

        .header p {
            font-size: 12px;
            margin: 3px 0;
        }

        .receipt-info {
            font-size: 12px;
            margin: 0px 0;
        }

        .items-table {
            width: 100%;
            font-size: 12px;
            margin-top: 5px;
            border-collapse: collapse;
        }

        .items-table th, .items-table td {
            padding: 6px;
            text-align: left;
        }

        .items-table th {
            font-weight: bold;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 10px;
        }

        /* Styles spécifiques pour l'impression */
        @media print {
            body {
                font-size: 12px;
            }
            .receipt {
                width: 80mm;
                margin: 0 auto;
            }
            .items-table th, .items-table td {
                font-size: 12px;
                padding: 6px;
            }
        }
    </style>
</head>
<body>

<div class="receipt">
    <div class="header">
        <h1>{{strtoupper($company->name ?? config('app.name'))}}</h1>
        <p><strong>Email :</strong> {{$company->email}}</p>
        <p><strong>Adresse :</strong> {{$company->adress}}</p>
        <p><strong>Tél :</strong> {{ $company->number1 }}{{ $company->number2 ? ' / ' . $company->number2 : '' }}</p>
    </div>

    <hr />

    <div class="receipt-info">
        <table class="items-table">
            <tbody>
                <tr>
                    <td class="item-details"> <strong>Date : {{ $sale->created_at->format('d/m/Y') }}</strong> </td>
                    <td class="item-details"> <strong>Réf : #{{ $sale->code }}</strong></td>
                    <td class="item-details"> <strong>Caissier : {{ $sale->cashier ?? 'Nom du Caissier' }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <hr />

    <table class="items-table">
        <thead>
            <tr>
                <th class="item-details">Nom</th>
                <th class="item-details">Qté</th>
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
        <!-- <h3>Les meilleurs wings de la capitale</h3> -->
        <h1>{{strtoupper($company->name ?? config('app.name'))}}</h1>
        <p>{{$company->message??''}}</p>
    </div>
</div>

</body>
</html>
