<style>
    #receiptPreview .receipt { box-sizing: border-box; width: min(100%, 520px); margin: 0 auto; padding: clamp(12px, 3vw, 24px); overflow-wrap: anywhere; background: #fff; color: #202124; font: 14px Arial, sans-serif; }
    #receiptPreview .receipt-header, #receiptPreview .receipt-footer { text-align: center; }
    #receiptPreview h1 { margin: 4px 0; font-size: clamp(20px, 5vw, 27px); }
    #receiptPreview .company-info { margin: 4px 0; line-height: 1.45; }
    #receiptPreview .sale-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 12px 0; font-size: 13px; }
    #receiptPreview table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    #receiptPreview th, #receiptPreview td { padding: 8px 4px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; word-break: break-word; }
    #receiptPreview .receipt-totals { margin-top: 12px; text-align: right; font-weight: 700; }
    #receiptPreview .receipt-totals p { margin: 5px 0; }
    #receiptPreview .receipt-footer { margin-top: 18px; }
    @media (max-width: 480px) {
        #receiptPreview .receipt { font-size: 12px; }
        #receiptPreview .sale-meta { grid-template-columns: 1fr; }
        #receiptPreview th, #receiptPreview td { padding: 6px 2px; font-size: 11px; }
    }
</style>

<div class="receipt">
    <header class="receipt-header">
        <h1>{{ strtoupper($company->name ?? config('app.name')) }}</h1>
        @if ($company && $company->adress)<p class="company-info"><strong>Adresse :</strong> {{ $company->adress }}</p>@endif
        @if ($company && ($company->number1 || $company->number2))
            <p class="company-info"><strong>Tél :</strong> {{ $company->number1 }}{{ $company->number2 ? ' / '.$company->number2 : '' }}</p>
        @endif
        @if ($company && $company->email)<p class="company-info"><strong>E-mail :</strong> {{ $company->email }}</p>@endif
    </header>

    <hr>
    <div class="sale-meta">
        <div><strong>Date :</strong><br>{{ $sale->created_at->format('d/m/Y H:i') }}</div>
        <div><strong>Référence :</strong><br>#{{ $sale->code }}</div>
        <div><strong>Caissier :</strong><br>{{ $sale->cashier ?? 'Non renseigné' }}</div>
    </div>

    @if ($sale->client)
        <div class="sale-client">
            <strong>Client :</strong> {{ $sale->client->name }}
        </div>
    @endif

    <table>
        <thead><tr><th style="width:37%">Nom</th><th style="width:11%">Qté</th><th style="width:24%">P.U</th><th style="width:28%">P.T</th></tr></thead>
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

    <div class="receipt-totals">
        @if ($sale->discount)
            <p>Montant initial : {{ number_format($sale->amount_init) }} FCFA</p>
            <p>Réduction : {{ number_format($sale->discount) }} FCFA</p>
        @endif
        <p>Montant payé : {{ number_format($sale->total_amount) }} FCFA</p>
        <p>Montant donné : {{ number_format($sale->received_amount) }} FCFA</p>
        <p>Monnaie rendue : {{ number_format($sale->remaining_amount) }} FCFA</p>
    </div>

    <footer class="receipt-footer">
        <hr><h3>Merci pour votre achat</h3>
        @if ($company && $company->message)<p>{{ $company->message }}</p>@endif
    </footer>
</div>
