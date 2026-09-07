<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $sale->code }}</title>
    <style>
        @page { margin: 24px 28px 30px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; background: #fff; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .brand-table { margin-bottom: 22px; }
        .brand-logo { width: 58px; height: 58px; object-fit: contain; border-radius: 10px; }
        .brand-name { color: #12233f; font-size: 20px; font-weight: bold; letter-spacing: .2px; }
        .brand-contact { padding-top: 5px; color: #667085; font-size: 9px; line-height: 1.6; }
        .invoice-label { text-align: right; color: #2f6fed; font-size: 18px; font-weight: bold; letter-spacing: 1px; }
        .invoice-code { padding-top: 6px; text-align: right; color: #667085; font-size: 10px; }
        .accent-line { height: 4px; background: #2f6fed; border-radius: 3px; }
        .meta-table { margin: 22px 0 24px; }
        .meta-cell { width: 33.33%; padding: 11px 12px; background: #f5f8fc; border: 1px solid #e3e9f2; }
        .meta-cell + .meta-cell { border-left: 0; }
        .meta-label { display: block; margin-bottom: 5px; color: #7a8699; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: .6px; }
        .meta-value { color: #172033; font-size: 10px; font-weight: bold; }
        .client-row { margin-bottom: 18px; padding: 11px 13px; border-left: 3px solid #2f6fed; background: #f5f8fc; }
        .client-row strong { color: #172033; }
        .client-row span { color: #667085; }
        .items { margin-bottom: 18px; }
        .items th { padding: 10px 9px; color: #fff; background: #12233f; font-size: 8px; font-weight: bold; text-align: left; text-transform: uppercase; letter-spacing: .4px; }
        .items td { padding: 10px 9px; border-bottom: 1px solid #e6ebf2; color: #344054; }
        .items tbody tr:nth-child(even) td { background: #f8fafc; }
        .items .number { text-align: right; white-space: nowrap; }
        .items .product { color: #172033; font-weight: bold; }
        .totals-wrap { width: 52%; margin-left: 48%; }
        .totals td { padding: 6px 9px; border-bottom: 1px solid #edf0f5; }
        .totals .label { color: #667085; text-align: right; }
        .totals .amount { color: #172033; font-weight: bold; text-align: right; white-space: nowrap; }
        .totals .grand td { padding-top: 11px; border-top: 2px solid #2f6fed; border-bottom: 0; color: #12233f; font-size: 13px; font-weight: bold; }
        .footer { margin-top: 28px; padding-top: 12px; border-top: 1px solid #e3e9f2; color: #7a8699; text-align: center; font-size: 8px; line-height: 1.6; }
        .footer strong { color: #2f6fed; }
    </style>
</head>
<body>
    @php
        $currency = app(\App\Services\AfricanMarketProfile::class)->forCompany($company)['currency'];
        $logoData = null;
        if ($company && $company->logo) {
            $logoPath = public_path(ltrim($company->logo, '/'));
            if (is_file($logoPath)) {
                $logoMime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
                $logoData = 'data:'.$logoMime.';base64,'.base64_encode(file_get_contents($logoPath));
            }
        }
    @endphp
    <table class="brand-table"><tr>
        <td style="vertical-align:top;">@if($logoData)<img class="brand-logo" src="{{ $logoData }}" alt="Logo de {{ $company->name }}">@endif</td>
        <td style="padding-left:12px; vertical-align:top;"><div class="brand-name">{{ strtoupper($company->name ?? config('app.name')) }}</div><div class="brand-contact">@if($company && $company->adress){{ $company->adress }}<br>@endif @if($company && ($company->number1 || $company->number2))Tél : {{ $company->number1 }}{{ $company->number2 ? ' / '.$company->number2 : '' }}<br>@endif @if($company && $company->email){{ $company->email }}@endif</div></td>
        <td style="vertical-align:top;"><div class="invoice-label">FACTURE</div><div class="invoice-code">Référence : <strong>{{ $sale->code }}</strong></div></td>
    </tr></table>
    <div class="accent-line"></div>
    <table class="meta-table"><tr>
        <td class="meta-cell"><span class="meta-label">Date de vente</span><span class="meta-value">{{ $sale->created_at->format('d/m/Y H:i') }}</span></td>
        <td class="meta-cell"><span class="meta-label">Caissier</span><span class="meta-value">{{ $sale->cashier ?? 'Non renseigné' }}</span></td>
        <td class="meta-cell"><span class="meta-label">Devise</span><span class="meta-value">{{ $currency }}</span></td>
    </tr></table>
    @if($sale->client)<div class="client-row"><strong>Client :</strong> <span>{{ $sale->client->name }}</span></div>@endif
    <table class="items"><thead><tr><th style="width:46%;">Produit</th><th style="width:12%;" class="number">Qté</th><th style="width:21%;" class="number">Prix unitaire</th><th style="width:21%;" class="number">Total</th></tr></thead><tbody>
    @foreach($saleDetails as $detail)<tr><td class="product">{{ $detail->product ? $detail->product->name : 'Produit non disponible' }}</td><td class="number">{{ $detail->quantity }}</td><td class="number">@money($detail->unit_price)</td><td class="number">@money($detail->total_price)</td></tr>@endforeach
    </tbody></table>
    <div class="totals-wrap"><table class="totals">
        @if($sale->discount)<tr><td class="label">Montant initial</td><td class="amount">@money($sale->amount_init)</td></tr><tr><td class="label">Réduction</td><td class="amount">- @money($sale->discount)</td></tr>@endif
        <tr class="grand"><td class="label">Montant payé</td><td class="amount">@money($sale->total_amount)</td></tr>
        <tr><td class="label">Montant donné</td><td class="amount">@money($sale->received_amount)</td></tr><tr><td class="label">Monnaie rendue</td><td class="amount">@money($sale->remaining_amount)</td></tr>
    </table></div>
    <div class="footer"><strong>Merci pour votre achat.</strong><br>@if($company && $company->message){{ $company->message }}<br>@endif Document généré par {{ strtoupper($company->name ?? config('app.name')) }}</div>
</body>
</html>
