<style>
    #receiptPreview .receipt { width:min(100%,560px); margin:0 auto; padding:clamp(16px,4vw,28px); color:#172033; background:#fff; font:13px Arial,sans-serif; overflow-wrap:anywhere; }
    #receiptPreview .receipt * { box-sizing:border-box; }
    #receiptPreview .receipt-header { display:flex; align-items:flex-start; gap:12px; padding-bottom:16px; border-bottom:4px solid #2f6fed; }
    #receiptPreview .receipt-logo { width:52px; height:52px; object-fit:contain; border-radius:9px; }
    #receiptPreview .receipt-brand { flex:1; }
    #receiptPreview .receipt-brand h1 { margin:0 0 5px; color:#12233f; font-size:clamp(18px,4vw,24px); }
    #receiptPreview .receipt-brand p { margin:2px 0; color:#667085; line-height:1.4; }
    #receiptPreview .receipt-title { color:#2f6fed; font-size:12px; font-weight:800; letter-spacing:1px; text-align:right; }
    #receiptPreview .receipt-meta { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; margin:18px 0; }
    #receiptPreview .receipt-meta div { padding:10px; background:#f5f8fc; border:1px solid #e3e9f2; }
    #receiptPreview .receipt-meta strong { display:block; margin-bottom:5px; color:#7a8699; font-size:9px; text-transform:uppercase; }
    #receiptPreview .receipt-client { margin-bottom:16px; padding:10px 12px; border-left:3px solid #2f6fed; background:#f5f8fc; }
    #receiptPreview table { width:100%; border-collapse:collapse; table-layout:fixed; }
    #receiptPreview th { padding:9px 5px; color:#fff; background:#12233f; font-size:10px; text-align:left; text-transform:uppercase; }
    #receiptPreview td { padding:9px 5px; border-bottom:1px solid #e6ebf2; vertical-align:top; word-break:break-word; }
    #receiptPreview tbody tr:nth-child(even) td { background:#f8fafc; }
    #receiptPreview th:nth-child(n+2),#receiptPreview td:nth-child(n+2) { text-align:right; }
    #receiptPreview .receipt-totals { width:60%; margin:16px 0 0 auto; text-align:right; font-weight:700; }
    #receiptPreview .receipt-totals p { margin:6px 0; color:#667085; }
    #receiptPreview .receipt-totals p strong { color:#12233f; font-size:15px; }
    #receiptPreview .receipt-footer { margin-top:22px; padding-top:14px; border-top:1px solid #e3e9f2; color:#7a8699; text-align:center; line-height:1.5; }
    #receiptPreview .receipt-footer h3 { margin:0 0 5px; color:#2f6fed; font-size:15px; }
    @media (max-width:480px) { #receiptPreview .receipt-header { display:block; text-align:center; } #receiptPreview .receipt-title { margin-top:10px; text-align:center; } #receiptPreview .receipt-meta { grid-template-columns:1fr; } #receiptPreview .receipt-totals { width:100%; } #receiptPreview th,#receiptPreview td { padding:7px 3px; font-size:11px; } }
</style>

<div class="receipt">
    @php($currency = app(\App\Services\AfricanMarketProfile::class)->forCompany($company)['currency'])
    <header class="receipt-header">
        @if($company && $company->logo)<img class="receipt-logo" src="{{ asset($company->logo) }}" alt="Logo">@endif
        <div class="receipt-brand"><h1>{{ strtoupper($company->name ?? config('app.name')) }}</h1>@if($company && $company->adress)<p>{{ $company->adress }}</p>@endif @if($company && ($company->number1 || $company->number2))<p>Tél : {{ $company->number1 }}{{ $company->number2 ? ' / '.$company->number2 : '' }}</p>@endif @if($company && $company->email)<p>{{ $company->email }}</p>@endif</div>
        <div class="receipt-title">REÇU DE VENTE<br><small>{{ $currency }}</small></div>
    </header>
    <div class="receipt-meta"><div><strong>Date</strong>{{ $sale->created_at->format('d/m/Y H:i') }}</div><div><strong>Référence</strong>#{{ $sale->code }}</div><div><strong>Caissier</strong>{{ $sale->cashier ?? 'Non renseigné' }}</div></div>
    @if($sale->client)<div class="receipt-client"><strong>Client :</strong> {{ $sale->client->name }}</div>@endif
    <table><thead><tr><th style="width:43%">Produit</th><th style="width:13%">Qté</th><th style="width:22%">P.U</th><th style="width:22%">Total</th></tr></thead><tbody>
        @foreach($saleDetails as $detail)<tr><td>{{ $detail->product ? $detail->product->name : 'Produit non disponible' }}</td><td>{{ $detail->quantity }}</td><td>@money($detail->unit_price)</td><td>@money($detail->total_price)</td></tr>@endforeach
    </tbody></table>
    <div class="receipt-totals">@if($sale->discount)<p>Montant initial : @money($sale->amount_init)</p><p>Réduction : - @money($sale->discount)</p>@endif <p><strong>Montant payé : @money($sale->total_amount)</strong></p><p>Montant donné : @money($sale->received_amount)</p><p>Monnaie rendue : @money($sale->remaining_amount)</p></div>
    <footer class="receipt-footer"><h3>Merci pour votre achat</h3>@if($company && $company->message)<div>{{ $company->message }}</div>@endif</footer>
</div>
