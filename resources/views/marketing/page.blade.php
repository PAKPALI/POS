@extends('layouts.marketing')
@section('title', $content['title'].' — Maxanou')
@section('meta-description', $content['intro'])
@section('content')
<section class="marketing-page-hero">
    <div class="marketing-container">
        <span class="marketing-eyebrow">{{ $content['eyebrow'] }}</span>
        <h1>{{ $content['title'] }}</h1>
        <p class="marketing-lead">{{ $content['intro'] }}</p>
        @if($page === 'partenaires')
            <div class="partner-program-status {{ $partnerProgram['enabled'] ? 'is-open' : 'is-upcoming' }}">
                <i class="bi {{ $partnerProgram['enabled'] ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}" aria-hidden="true"></i>
                <span>{{ $partnerProgram['enabled'] ? 'Programme partenaire ouvert' : 'Programme en préparation' }}</span>
            </div>
            <div class="marketing-hero-actions">
                @if($partnerProgram['enabled'] && $partnerProgram['registration_enabled'])
                    <a class="marketing-button marketing-button-primary" href="{{ route('partner.register') }}">Devenir partenaire @include('marketing.components.icon', ['name' => 'arrow'])</a>
                    <a class="marketing-button marketing-button-secondary" href="{{ route('partner.login') }}">Se connecter à mon espace</a>
                @elseif($partnerProgram['enabled'])
                    <a class="marketing-button marketing-button-primary" href="{{ route('partner.login') }}">Accéder à mon espace @include('marketing.components.icon', ['name' => 'arrow'])</a>
                    <a class="marketing-button marketing-button-secondary" href="#partenaire-regles">Voir les conditions</a>
                @else
                    <a class="marketing-button marketing-button-primary" href="#partenaire-comment-ca-marche">Découvrir le parcours @include('marketing.components.icon', ['name' => 'arrow'])</a>
                    <a class="marketing-button marketing-button-secondary" href="#partenaire-regles">Consulter les conditions</a>
                @endif
            </div>
            <p class="marketing-login-note">{{ $partnerProgram['enabled'] ? ($partnerProgram['registration_enabled'] ? 'Les inscriptions sont actuellement ouvertes.' : 'Les inscriptions sont temporairement fermées ; les partenaires actifs peuvent toujours se connecter.') : 'Les accès seront proposés après l’ouverture officielle du programme.' }}</p>
        @else
            <div class="marketing-hero-actions"><a class="marketing-button marketing-button-primary" href="{{ route('marketing.register') }}">Essayer gratuitement @include('marketing.components.icon', ['name' => 'arrow'])</a><a class="marketing-button marketing-button-secondary" href="{{ route('marketing.login') }}">Se connecter</a></div>
        @endif
    </div>
</section>

<section class="marketing-section marketing-page-content"><div class="marketing-container"><div class="marketing-detail-grid">@foreach($content['sections'] as $item)<article class="marketing-detail-card"><span class="feature-card-icon">@include('marketing.components.icon', ['name' => $item['icon']])</span><h2>{{ $item['title'] }}</h2><p>{{ $item['text'] }}</p></article>@endforeach</div></div></section>

@if($page === 'partenaires')
<section class="marketing-section partner-example-section" id="partenaire-exemple">
    <div class="marketing-container marketing-ecommerce-panel">
        <div><span class="marketing-eyebrow">Exemple concret · simulation</span><h2>Une recommandation utile crée de la valeur des deux côtés.</h2><p>Un commerçant choisit le plan Basic à 2 500 XOF avec votre code. Avec une remise de {{ $partnerProgram['discount_percent'] }} %, il économise {{ number_format(2500 * $partnerProgram['discount_percent'] / 100, 0, ',', ' ') }} XOF sur son premier abonnement éligible. Votre taux personnel est confirmé dans votre espace et devient définitif lors de l’attribution du client.</p><a class="marketing-button marketing-button-secondary" href="#partenaire-comment-ca-marche">Voir le parcours @include('marketing.components.icon', ['name' => 'arrow'])</a></div>
        <div class="partner-value-flow"><div><span>01</span><strong>Vous partagez</strong><small>Votre code unique</small></div><i class="bi bi-arrow-right"></i><div><span>02</span><strong>Le client économise</strong><small>Sur son premier paiement</small></div><i class="bi bi-arrow-right"></i><div><span>03</span><strong>Vous gagnez</strong><small>Selon votre taux acquis</small></div></div>
    </div>
</section>

<section class="marketing-section partner-rules-section" id="partenaire-regles">
    <div class="marketing-container">
        <div class="marketing-section-heading"><span class="marketing-eyebrow">Règles essentielles</span><h2>Un programme compréhensible avant même de s’inscrire.</h2><p>Les montants et statuts sont vérifiés côté serveur. Votre tableau de bord présente le détail de chaque attribution, commission et retrait.</p></div>
        <div class="partner-rule-grid">
            <article><span>@include('marketing.components.icon', ['name' => 'percent'])</span><strong>{{ $partnerProgram['discount_percent'] }} % pour le nouveau client</strong><p>La remise s’applique uniquement au premier abonnement payant éligible.</p></article>
            <article><span>@include('marketing.components.icon', ['name' => 'share'])</span><strong>Attribution durable</strong><p>Le client vous est attribué après confirmation de son premier paiement avec votre code.</p></article>
            <article><span>@include('marketing.components.icon', ['name' => 'wallet'])</span><strong>Retrait encadré</strong><p>À partir de {{ number_format($partnerProgram['payout_min_xof'], 0, ',', ' ') }} XOF et {{ $partnerProgram['required_clients'] }} {{ $partnerProgram['required_clients'] > 1 ? 'clients qualifiés' : 'client qualifié' }}, lorsque les retraits sont ouverts.</p></article>
            <article><span>@include('marketing.components.icon', ['name' => 'shield'])</span><strong>Suivi auditable</strong><p>Chaque commission indique son calcul, son statut et la vente d’abonnement concernée.</p></article>
        </div>
        <p class="partner-availability-note"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Zone prévue au lancement : {{ implode(', ', $partnerProgram['countries']) ?: 'à confirmer' }}. Les retraits Mobile Money sont {{ $partnerProgram['payouts_enabled'] ? 'actuellement activés' : 'ouverts progressivement par l’administration' }}.</span></p>
    </div>
</section>

<section class="marketing-section marketing-results-section" id="partenaire-comment-ca-marche"><div class="marketing-container"><div class="marketing-section-heading"><span class="marketing-eyebrow">Comment s’y prendre</span><h2>Commencez avec les commerçants que vous comprenez.</h2><p>Le meilleur partenaire aide le bon commerce à franchir une première étape concrète.</p></div><div class="marketing-metric-grid"><div class="marketing-metric-card"><span class="metric-icon"><strong>01</strong></span><strong>Créez votre espace</strong><p>Inscrivez-vous lorsque le programme est ouvert et confirmez vos coordonnées.</p></div><div class="marketing-metric-card"><span class="metric-icon metric-icon-green"><strong>02</strong></span><strong>Présentez un besoin</strong><p>Partez d’un problème réel : vente, stock, caisse, équipe ou reçus mobiles.</p></div><div class="marketing-metric-card"><span class="metric-icon metric-icon-blue"><strong>03</strong></span><strong>Partagez votre code</strong><p>Le commerçant l’utilise avant son premier abonnement payant éligible.</p></div><div class="marketing-metric-card"><span class="metric-icon metric-icon-purple"><strong>04</strong></span><strong>Suivez la relation</strong><p>Clients, commissions, portefeuille et retraits restent visibles depuis votre espace.</p></div></div></div></section>
@endif

@if($page === 'factures-sms-whatsapp')<section class="marketing-section marketing-phone-callout"><div class="marketing-container marketing-phone-panel"><div><span class="marketing-eyebrow">Exemple fictif</span><h2>Un reçu qui ne reste pas dans la caisse.</h2><p>Les messages consomment un quota et dépendent du canal configuré. La démonstration n’utilise aucun numéro réel.</p></div><div class="phone-receipt-card"><span>Reçu · Boutique Démo</span><strong>Merci pour votre achat</strong><small>SMS · WhatsApp selon configuration</small><b>2 500 FCFA</b></div></div></section>@endif
@if(in_array($page, ['fonctionnalites','securite'], true))<section class="marketing-section marketing-page-band"><div class="marketing-container marketing-page-band-inner"><div><span class="marketing-eyebrow">Gardez le fil</span><h2>Un même produit, un même langage.</h2><p>Le site public et l’espace connecté partagent les mêmes fondations visuelles, sans charger les écrans métier dans la page marketing.</p></div><a class="marketing-button marketing-button-secondary" href="{{ route('marketing.pricing') }}">Voir les offres @include('marketing.components.icon', ['name' => 'arrow'])</a></div></section>@endif
@endsection
