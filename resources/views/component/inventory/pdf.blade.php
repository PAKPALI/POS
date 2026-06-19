<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Liste des inventaires</title>
    <style>
        body {
            font-family: Arial;
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
            @if($start_date && $end_date)
                <h3 style="text-align:center;">LISTE DES INVENTAIRES DU {{\Carbon\Carbon::parse($start_date)->format('d-m-Y') ?? '' }} AU {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') ?? '' }}</h3>
            @else
                <h3 style="text-align:center;"><u>LISTE DES INVENTAIRES</u></h3>
            @endif
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Type</th>
                <th>Qté avant</th>
                <th>Qté saisie</th>
                <th>Qté après</th>
                <!-- <th>Créé par</th> -->
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inv)
            <tr class="align-middle">
                <td>{{ $inv->product->name ?? '' }}</td>
                <td>{{ $inv->type == 1 ? 'Entrée' : 'Sortie' }}</td>
                <td>{{ $inv->qte_before }}</td>
                <td>{{ $inv->qte_added }}</td>
                <td>{{ $inv->qte_after }}</td>
                <!-- <td>{{ $inv->user->name ?? '' }}</td> -->
                <td>{{ $inv->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 50px;">
        <p style="margin: 0; font-size: 12px; color: #555;"><h3><u> {{strtoupper(config('app.name'))}}</u></h3></p>
    </div>

</body>

</html>