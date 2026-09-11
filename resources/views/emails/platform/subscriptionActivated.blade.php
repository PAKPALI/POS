<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement confirmé</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    @php
        $commissionStatus = match ($commission?->status) {
            'available' => 'Disponible',
            'pending' => 'En attente',
            'reserved' => 'Réservée',
            'paid' => 'Versée',
            default => $commission?->status ? ucfirst($commission->status) : null,
        };
        $commissionRate = $commission ? number_format(((int) $commission->commission_rate_bps) / 100, 2, ',', ' ').' %' : null;
    @endphp
    <div class="header">
        <h2 style="margin-bottom:8px;">{{ config('app.name') }}</h2>
        <p style="margin:0;color:#ff9f43;">Administration SaaS</p>
    </div>

    <div class="content" style="padding:24px 12px;">
        <h2 class="text-center">Abonnement confirmé</h2>
        <p>Un paiement d’abonnement a été confirmé pour l’entreprise <strong>{{ $companyName }}</strong>.</p>

        <table role="presentation">
            <tr><th colspan="2">Détails de l’abonnement</th></tr>
            <tr><td><strong>Plan</strong></td><td>{{ $plan }}</td></tr>
            <tr><td><strong>Durée</strong></td><td>{{ $months }} mois — {{ $operation }}</td></tr>
            <tr><td><strong>Période</strong></td><td>Du {{ $startsAt }} au {{ $endsAt }}</td></tr>
            <tr><td><strong>Client</strong></td><td>{{ $payment->user?->name ?: '—' }} — {{ $payment->user?->email ?: '—' }}</td></tr>
            @if(data_get($payment->snapshot, 'user_limit') !== null || data_get($payment->snapshot, 'product_limit') !== null)
                <tr><td><strong>Limites du plan</strong></td><td>{{ data_get($payment->snapshot, 'user_limit', '—') }} utilisateurs · {{ data_get($payment->snapshot, 'product_limit', '—') }} produits</td></tr>
            @endif
            @if(data_get($payment->snapshot, 'sms_quota') !== null || data_get($payment->snapshot, 'whatsapp_quota') !== null)
                <tr><td><strong>Quotas inclus</strong></td><td>{{ data_get($payment->snapshot, 'sms_quota', 0) }} SMS · {{ data_get($payment->snapshot, 'whatsapp_quota', 0) }} WhatsApp par mois</td></tr>
            @endif
        </table>

        <table role="presentation">
            <tr><th colspan="2">Détails financiers</th></tr>
            <tr><td><strong>Tarif brut</strong></td><td>{{ number_format($grossAmount, 0, ',', ' ') }} {{ $currency }}</td></tr>
            <tr><td><strong>Remise</strong></td><td>{{ number_format($discountAmount, 0, ',', ' ') }} {{ $currency }}</td></tr>
            <tr><td><strong>Montant payé</strong></td><td><strong>{{ number_format($netAmount, 0, ',', ' ') }} {{ $currency }}</strong></td></tr>
            <tr><td><strong>Montant partenaire</strong></td><td>{{ number_format((int) ($commission?->commission_amount ?? 0), 0, ',', ' ') }} {{ $currency }}{{ $commissionStatus ? ' — '.$commissionStatus : '' }}</td></tr>
            @if($commissionRate)
                <tr><td><strong>Taux partenaire</strong></td><td>{{ $commissionRate }}</td></tr>
            @endif
        </table>

        @if($isFirstSubscription && ($attribution?->partner || $attribution?->promoCode))
            <div class="info" style="text-align:left;">
                <p style="margin-bottom:8px;"><strong>Attribution du premier abonnement</strong></p>
                @if($attribution?->partner)
                    <p style="margin-bottom:5px;">Partenaire : <strong>{{ $attribution->partner->name }}</strong></p>
                @endif
                @if($attribution?->promoCode)
                    <p style="margin:0;">Code promo : <strong>{{ $attribution->promoCode->code }}</strong></p>
                @endif
            </div>
        @endif

        <table role="presentation">
            <tr><th colspan="2">Références</th></tr>
            <tr><td><strong>Transaction</strong></td><td style="word-break:break-all;">{{ $payment->transaction_id }}</td></tr>
            <tr><td><strong>Référence KPrimePay</strong></td><td style="word-break:break-all;">{{ $payment->kpp_reference ?: '—' }}</td></tr>
            <tr><td><strong>Confirmé le</strong></td><td>{{ $payment->paid_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i') }}</td></tr>
        </table>

        @if($actionUrl)
            <p class="text-center" style="margin:28px 0 4px;">
                <a class="btn" href="{{ $actionUrl }}" style="color:#ffffff !important;text-decoration:none !important;">{{ $actionLabel }}</a>
            </p>
        @endif
    </div>

    @include('emails.design.emailFooter', ['company' => null])
</div>
</body>
</html>
