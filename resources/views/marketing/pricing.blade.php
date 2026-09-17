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
    .pricing-feature-accordions { display: grid; gap: 8px; margin-top: 4px; }
    .pricing-feature-accordion { border: 1px solid rgba(53, 201, 139, .24); border-radius: 10px; background: rgba(53, 201, 139, .07); }
    .pricing-feature-accordion summary { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 36px; padding: 8px 10px; color: var(--ds-success, #35c98b); cursor: pointer; font-size: .78rem; font-weight: 800; list-style: none; }
    .pricing-feature-accordion summary::-webkit-details-marker { display: none; }
    .pricing-feature-accordion summary span { display: flex; align-items: center; gap: 7px; }
    .pricing-feature-accordion summary > i { transition: transform .18s ease; }
    .pricing-feature-accordion[open] summary > i { transform: rotate(180deg); }
    .pricing-feature-accordion p { margin: 0; padding: 0 10px 10px; color: var(--ds-text-secondary); font-size: .75rem; font-weight: 600; line-height: 1.5; }
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
            @php
                $featureDetails = [
                    ['key' => 'suppliers', 'label' => 'Fournisseurs', 'icon' => 'truck', 'description' => 'Enregistrez vos fournisseurs, conservez leurs coordonnées et utilisez-les lors des inventaires pour suivre l’origine de vos produits.'],
                    ['key' => 'ecommerce', 'label' => 'E-commerce', 'icon' => 'store', 'description' => 'Disposez d’un site e-commerce dédié à votre entreprise : vos produits y sont présentés et vos clients peuvent commander en ligne.'],
                    ['key' => 'promo_codes', 'label' => 'Codes promo clients', 'icon' => 'tag', 'description' => 'Choisissez un pourcentage et une date d’expiration. Chaque client fidèle ou nouveau client qui utilise le code lors d’un achat dans votre entreprise bénéficie de la même réduction. Le code peut être partagé pour faire connaître votre entreprise et développer votre clientèle.'],
                ];
            @endphp
            <div class="pricing-feature-accordions">
                @foreach($featureDetails as $feature)
                    @if($plan[$feature['key']])
                        <details class="pricing-feature-accordion">
                            <summary><span>@include('marketing.components.icon', ['name' => $feature['icon']]) {{ $feature['label'] }} inclus</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                            <p>{{ $feature['description'] }}</p>
                        </details>
                    @else
                        <div class="pricing-detail-line pricing-feature-line is-unavailable"><span class="pricing-feature-status" aria-hidden="true">×</span><span>{{ $feature['label'] }} non inclus</span></div>
                    @endif
                @endforeach
            </div>
            <a class="marketing-button {{ $plan['featured'] ? 'marketing-button-primary' : 'marketing-button-secondary' }}" data-event="plan_select" href="{{ route('marketing.register') }}">{{ $plan['key'] === 'trial' ? 'Essayer 14 jours' : 'Choisir ce plan' }}</a>
        </article>
    @endforeach
</div><div class="pricing-rules"><p><strong>À retenir</strong> Les crédits annuels sont crédités à l’activation et restent cumulables. SMS et WhatsApp restent distincts.</p><p>Une limite atteinte ne supprime aucune donnée. Vous pouvez faire évoluer votre plan lorsque votre activité grandit.</p></div></div></section>

<section class="marketing-section pricing-comparison-section" id="comparatif-plans">
    <div class="marketing-container">
        <div class="marketing-section-heading"><span class="marketing-eyebrow">Comparaison complète</span><h2>Voyez immédiatement ce qui change d’un plan à l’autre.</h2><p>Les coches et les croix reprennent les fonctions réellement publiées dans l’application.</p></div>
        <div class="pricing-comparison-shell" role="region" aria-label="Comparaison des plans" tabindex="0">
            <table class="pricing-comparison-table">
                <thead><tr><th scope="col">Plan</th><th scope="col">Entreprises</th><th scope="col">Utilisateurs</th><th scope="col">Produits</th><th scope="col">SMS / mois</th><th scope="col">WhatsApp / mois</th><th scope="col">Fournisseurs</th><th scope="col">E-commerce</th><th scope="col">Codes promo</th></tr></thead>
                <tbody>
                    @foreach($pricing as $plan)
                        <tr class="{{ $plan['featured'] ? 'is-featured' : '' }}">
                            <th scope="row"><strong>{{ $plan['name'] }}</strong><small>{{ number_format($plan['price'], 0, ',', ' ') }} {{ $plan['currency'] ?? 'XOF' }}</small></th>
                            <td>{{ $plan['company_limit'] }}</td><td>{{ $plan['user_limit'] }}</td><td>{{ number_format($plan['product_limit'], 0, ',', ' ') }}</td><td>{{ number_format($plan['sms_quota'], 0, ',', ' ') }}</td><td>{{ number_format($plan['whatsapp_quota'], 0, ',', ' ') }}</td>
                            <td><span class="pricing-table-status {{ $plan['suppliers'] ? 'is-available' : 'is-unavailable' }}"><i class="bi {{ $plan['suppliers'] ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i><span class="visually-hidden">{{ $plan['suppliers'] ? 'Inclus' : 'Non inclus' }}</span></span></td>
                            <td><span class="pricing-table-status {{ $plan['ecommerce'] ? 'is-available' : 'is-unavailable' }}"><i class="bi {{ $plan['ecommerce'] ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i><span class="visually-hidden">{{ $plan['ecommerce'] ? 'Inclus' : 'Non inclus' }}</span></span></td>
                            <td><span class="pricing-table-status {{ $plan['promo_codes'] ? 'is-available' : 'is-unavailable' }}"><i class="bi {{ $plan['promo_codes'] ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i><span class="visually-hidden">{{ $plan['promo_codes'] ? 'Inclus' : 'Non inclus' }}</span></span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
