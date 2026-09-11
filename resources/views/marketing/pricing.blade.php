@extends('layouts.marketing')
@section('title', 'Tarifs et comparaison des offres — Maxanou')
@section('meta-description', 'Découvrez les tarifs prévisionnels Maxanou, l’essai de 14 jours, les quotas SMS et WhatsApp et les fonctions fournisseurs et e-commerce incluses par plan.')
@push('styles')
<style>
    .pricing-feature-line { align-items: center; min-height: 30px; }
    .pricing-feature-status { display: inline-grid; place-items: center; width: 15px; height: 15px; flex: 0 0 15px; border-radius: 50%; font-size: .66rem; font-weight: 900; line-height: 1; }
    .pricing-feature-line.is-available .pricing-feature-status { color: var(--ds-success, #35c98b); background: rgba(53, 201, 139, .12); }
    .pricing-feature-line.is-unavailable .pricing-feature-status { color: var(--ds-danger, #ff626e); background: rgba(255, 98, 110, .12); }
    .pricing-feature-line.is-unavailable { color: var(--ds-text-muted); }
</style>
@endpush
@section('content')
<section class="marketing-page-hero"><div class="marketing-container"><span class="marketing-eyebrow">Des limites claires, sans surprise</span><h1>Choisissez le niveau adapté à votre commerce.</h1><p class="marketing-lead">{{ $pricingNote }} À 12 mois, vous payez 11 mensualités pour 12 mois d’accès.</p><div class="marketing-hero-actions"><a class="marketing-button marketing-button-primary" href="{{ route('marketing.register') }}">Essayer gratuitement @include('marketing.components.icon', ['name' => 'arrow'])</a><a class="marketing-button marketing-button-secondary" href="#comparatif-plans">Voir le comparatif</a></div></div></section>
<section class="marketing-section pricing-full-section" id="pricing-grid"><div class="marketing-container"><div class="pricing-toggle" role="group" aria-label="Période de tarification"><button type="button" class="is-active" data-pricing-period="monthly" data-event="pricing_toggle">Mensuel</button><button type="button" data-pricing-period="annual" data-event="pricing_toggle">Annuel <span>- 1 mois offert</span></button></div><div class="pricing-full-grid">
    @foreach($pricing as $plan)
        <article class="pricing-card pricing-card-full @if($plan['featured']) is-featured @endif">
            <div class="pricing-card-top"><span>{{ $plan['name'] }}</span>@if($plan['featured'])<em>Recommandé</em>@endif</div>
            <div class="pricing-price"><strong data-price-monthly="{{ $plan['price'] }}" data-price-annual="{{ $plan['annual'] }}">{{ number_format($plan['price'], 0, ',', ' ') }}</strong><small>{{ $plan['currency'] ?? 'XOF' }} HT / <span data-period-label>{{ $plan['period'] }}</span></small></div>
            <p>{{ $plan['description'] }}</p>
            <div class="pricing-detail-line">@include('marketing.components.icon', ['name' => 'layers'])<span>{{ $plan['limits'] }}</span></div>
            <div class="pricing-detail-line">@include('marketing.components.icon', ['name' => 'message'])<span>{{ $plan['quota'] }}</span></div>
            <div class="pricing-detail-line pricing-feature-line {{ $plan['suppliers'] ? 'is-available' : 'is-unavailable' }}"><span class="pricing-feature-status" aria-hidden="true">{{ $plan['suppliers'] ? '✓' : '×' }}</span><span>Fournisseurs {{ $plan['suppliers'] ? 'inclus' : 'non inclus' }}</span></div>
            <div class="pricing-detail-line pricing-feature-line {{ $plan['ecommerce'] ? 'is-available' : 'is-unavailable' }}"><span class="pricing-feature-status" aria-hidden="true">{{ $plan['ecommerce'] ? '✓' : '×' }}</span><span>E-commerce {{ $plan['ecommerce'] ? 'inclus' : 'non inclus' }}</span></div>
            <a class="marketing-button {{ $plan['featured'] ? 'marketing-button-primary' : 'marketing-button-secondary' }}" data-event="plan_select" href="{{ route('marketing.register') }}">{{ $plan['key'] === 'trial' ? 'Essayer 14 jours' : 'Choisir ce plan' }}</a>
        </article>
    @endforeach
</div><div class="pricing-rules"><p><strong>À retenir</strong> Les crédits annuels sont crédités à l’activation et restent cumulables. SMS et WhatsApp restent distincts.</p><p>Une limite atteinte ne supprime aucune donnée. Vous pouvez faire évoluer votre plan lorsque votre activité grandit.</p></div></div></section>

<section class="marketing-section pricing-comparison-section" id="comparatif-plans">
    <div class="marketing-container">
        <div class="marketing-section-heading"><span class="marketing-eyebrow">Comparaison complète</span><h2>Voyez immédiatement ce qui change d’un plan à l’autre.</h2><p>Les coches et les croix reprennent les fonctions réellement publiées dans l’application.</p></div>
        <div class="pricing-comparison-shell" role="region" aria-label="Comparaison des plans" tabindex="0">
            <table class="pricing-comparison-table">
                <thead><tr><th scope="col">Plan</th><th scope="col">Entreprises</th><th scope="col">Utilisateurs</th><th scope="col">Produits</th><th scope="col">SMS / mois</th><th scope="col">WhatsApp / mois</th><th scope="col">Fournisseurs</th><th scope="col">E-commerce</th></tr></thead>
                <tbody>
                    @foreach($pricing as $plan)
                        <tr class="{{ $plan['featured'] ? 'is-featured' : '' }}">
                            <th scope="row"><strong>{{ $plan['name'] }}</strong><small>{{ number_format($plan['price'], 0, ',', ' ') }} {{ $plan['currency'] ?? 'XOF' }}</small></th>
                            <td>{{ $plan['company_limit'] }}</td><td>{{ $plan['user_limit'] }}</td><td>{{ number_format($plan['product_limit'], 0, ',', ' ') }}</td><td>{{ number_format($plan['sms_quota'], 0, ',', ' ') }}</td><td>{{ number_format($plan['whatsapp_quota'], 0, ',', ' ') }}</td>
                            <td><span class="pricing-table-status {{ $plan['suppliers'] ? 'is-available' : 'is-unavailable' }}"><i class="bi {{ $plan['suppliers'] ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i><span class="visually-hidden">{{ $plan['suppliers'] ? 'Inclus' : 'Non inclus' }}</span></span></td>
                            <td><span class="pricing-table-status {{ $plan['ecommerce'] ? 'is-available' : 'is-unavailable' }}"><i class="bi {{ $plan['ecommerce'] ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i><span class="visually-hidden">{{ $plan['ecommerce'] ? 'Inclus' : 'Non inclus' }}</span></span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
