<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu POS</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow-wrap: break-word;
            background: #fff;
            color: #202124;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .receipt {
            box-sizing: border-box;
            width: 100%;
            margin: 0 auto;
            padding: 14px;
            background: #fff;
        }

        .header,
        .footer {
            text-align: center;
        }

        .header {
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 4px 0;
            font-size: 19px;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
            line-height: 1.4;
        }

        .items-table {
            width: 100%;
            margin-top: 5px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 12px;
        }

        .items-table th,
        .items-table td {
            padding: 6px 4px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }

        .items-table th {
            font-weight: bold;
            border-bottom: 1px solid #aaa;
        }

        .total {
            margin-top: 5px;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        .total p {
            margin: 4px 0;
        }

        .footer {
            margin-top: 10px;
            font-size: 12px;
        }

        .footer h2 {
            margin: 0 0 4px;
            font-size: 15px;
        }

        @media print {
            @page {
                margin: 0;
            }

            .receipt {
                width: 80mm;
                margin: 0 auto;
            }

            .items-table th,
            .items-table td {
                padding: 5px 3px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h1>{{ strtoupper($company->name ?? config('app.name')) }}</h1>

        @if ($company && $company->adress)
            <p><strong>Adresse :</strong> {{ $company->adress }}</p>
        @endif
        @if ($company && ($company->number1 || $company->number2))
            <p><strong>Tél :</strong> {{ $company->number1 }}{{ $company->number2 ? ' / '.$company->number2 : '' }}</p>
        @endif
        @if ($company && $company->email)
            <p><strong>E-mail :</strong> {{ $company->email }}</p>
        @endif
    </div>

    <hr>

    <table class="items-table">
        <tbody>
            <tr>
                <td><strong>Date :</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td><strong>Réf :</strong> #{{ $sale->code }}</td>
                <td><strong>Caissier :</strong> {{ $sale->cashier ?? 'Non renseigné' }}</td>
            </tr>
            @if ($sale->client)
                <tr>
                    <td colspan="3"><strong>Client :</strong> {{ $sale->client->name }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <hr>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 37%;">Nom</th>
                <th style="width: 11%;">Qté</th>
                <th style="width: 24%;">P.U (FCFA)</th>
                <th style="width: 28%;">P.T (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product ? $detail->product->name : 'Produit non disponible' }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ number_format($detail->unit_price, 2) }}</td>
                    <td>{{ number_format($detail->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <div class="total">
        @if ($sale->discount)
            <p>Montant initial : {{ number_format($sale->amount_init) }} FCFA</p>
            <p>Réduction : {{ number_format($sale->discount) }} FCFA</p>
        @endif
        <p>Montant payé : {{ number_format($sale->total_amount) }} FCFA</p>
        <p>Montant donné : {{ number_format($sale->received_amount) }} FCFA</p>
        <p>Monnaie rendue : {{ number_format($sale->remaining_amount) }} FCFA</p>
    </div>

    <hr>

    <div class="footer">
        <h2>Merci pour votre achat</h2>
        @if ($company && $company->message)
            <p>{{ $company->message }}</p>
        @endif
        <p>{{ strtoupper($company->name ?? config('app.name')) }}</p>
    </div>
</div>
</body>
</html>
