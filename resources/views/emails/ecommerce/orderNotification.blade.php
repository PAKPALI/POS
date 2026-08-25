<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle commande E-commerce</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ $company->name }}</h2>
        <p style="color:red;">Nouvelle commande E-commerce</p>
    </div>

    <div class="info">
        <h3>Commande #{{ $order->code }}</h3>
        <p><strong>Client :</strong> {{ $order->customer_name }}</p>
        <p><strong>Téléphone :</strong> {{ $order->customer_phone }}</p>
        @if($order->customer_email)<p><strong>E-mail :</strong> {{ $order->customer_email }}</p>@endif
        @if($order->customer_address)<p><strong>Adresse :</strong> {{ $order->customer_address }}</p>@endif
        @if($order->delivery_location_url)
            <p><strong>Localisation :</strong> <a href="{{ $order->delivery_location_url }}" target="_blank" rel="noopener noreferrer">Ouvrir dans Google Maps</a></p>
        @endif
        @if($order->notes)<p><strong>Instructions :</strong> {{ $order->notes }}</p>@endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Qté</th>
                <th>PU (FCFA)</th>
                <th>Total (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                    <td>{{ number_format($item->total_price, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;font-weight:bold;">Montant total :</td>
                <td style="font-weight:bold;color:red;">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>

    <p class="text-center" style="margin-top:20px;">
        <a class="btn" href="{{ route('ecommerce.orders.show', $order->id) }}">Consulter la commande</a>
    </p>

    @include('emails.design.emailFooter')
</div>
</body>
</html>
