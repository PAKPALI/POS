@extends('layouts.saas')

@section('title', 'Guide d’utilisation')
@section('eyebrow', 'Abonnement et accompagnement')
@section('page-title', 'Guide d’utilisation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('hub/assets/css/saas-guides.css') }}?v=20260918-1">
@endpush

@section('content')
<div class="saas-guide">
    <x-ui.page-header title="Bien utiliser {{ config('app.name') }}" eyebrow="Guide de votre entreprise" icon="bi-journal-richtext" description="Retrouvez les étapes utiles pour organiser votre activité, vendre, suivre vos données et gérer votre abonnement.">
        <x-slot:actions>
            <x-ui.button :href="route('subscriptions.guide.pdf')" variant="primary"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Télécharger le guide PDF</x-ui.button>
            <x-ui.button :href="route('subscriptions.index')" variant="secondary"><i class="bi bi-credit-card" aria-hidden="true"></i> Mon abonnement</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="saas-guide-hero" aria-labelledby="guide-welcome-title">
        <div class="saas-guide-hero-copy">
            <h2 id="guide-welcome-title">Votre espace de travail, expliqué simplement.</h2>
            <p>Vous utilisez actuellement l’espace de <strong>{{ $company->name }}</strong>. Les menus visibles dépendent de votre rôle et des fonctionnalités incluses dans le plan de la compagnie.</p>
        </div>
        <span class="saas-guide-hero-icon" aria-hidden="true"><i class="bi bi-compass"></i></span>
    </section>

    <nav class="saas-guide-quick-nav" aria-label="Accès rapide au guide">
        <a href="#demarrer"><i class="bi bi-rocket-takeoff" aria-hidden="true"></i> Démarrer</a>
        <a href="#activite"><i class="bi bi-shop" aria-hidden="true"></i> Activité</a>
        <a href="#gestion"><i class="bi bi-clipboard-data" aria-hidden="true"></i> Gestion</a>
        <a href="#equipe"><i class="bi bi-people" aria-hidden="true"></i> Équipe</a>
        <a href="#abonnement"><i class="bi bi-credit-card" aria-hidden="true"></i> Abonnement</a>
        <a href="#securite"><i class="bi bi-shield-check" aria-hidden="true"></i> Sécurité</a>
    </nav>

    <x-ui.notice variant="info">Un menu absent ne signifie pas une erreur : il peut être limité par votre rôle, par le plan de la compagnie ou par une configuration choisie par le gestionnaire.</x-ui.notice>

    <section class="saas-guide-section" id="demarrer" aria-labelledby="guide-start-title">
        <div class="saas-guide-section-heading"><i class="bi bi-rocket-takeoff" aria-hidden="true"></i><div><h2 id="guide-start-title">Démarrer en quelques étapes</h2><p>Cette séquence aide un nouveau gestionnaire à prendre ses repères.</p></div></div>
        <div class="saas-guide-start-grid">
            <article class="saas-guide-start-step"><span class="saas-guide-start-number">1</span><div><strong>Vérifiez la compagnie active</strong><span>Utilisez le sélecteur de compagnie en haut de l’écran lorsque vous gérez plusieurs entreprises.</span></div></article>
            <article class="saas-guide-start-step"><span class="saas-guide-start-number">2</span><div><strong>Préparez le catalogue</strong><span>Ajoutez catégories, produits, prix, stock et, si disponible, vos fournisseurs.</span></div></article>
            <article class="saas-guide-start-step"><span class="saas-guide-start-number">3</span><div><strong>Commencez à vendre</strong><span>Ouvrez le Point de vente, ajoutez les produits au panier et confirmez la vente.</span></div></article>
        </div>
    </section>

    <section class="saas-guide-section" id="activite" aria-labelledby="guide-activity-title">
        <div class="saas-guide-section-heading"><i class="bi bi-shop" aria-hidden="true"></i><div><h2 id="guide-activity-title">Gérer l’activité quotidienne</h2><p>Les données restent attachées à la compagnie actuellement sélectionnée.</p></div></div>

        <details class="saas-guide-accordion" open>
            <summary><span><i class="bi bi-grid-1x2" aria-hidden="true"></i> Tableau de bord</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body"><p>Le tableau de bord rassemble les chiffres utiles : ventes, activité récente, stock et indicateurs disponibles selon vos droits. Utilisez-le pour repérer rapidement une baisse de stock ou suivre votre activité.</p><div class="saas-guide-tip"><i class="bi bi-lightbulb" aria-hidden="true"></i><span>Vérifiez toujours le nom de la compagnie affiché avant d’interpréter les chiffres ou de créer une donnée.</span></div></div>
        </details>

        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-box-seam" aria-hidden="true"></i> Catalogue : catégories, produits, packs et fournisseurs</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body">
                <ul class="saas-guide-list">
                    <li><strong>Catégories :</strong> créez-les avant les produits pour garder un catalogue facile à parcourir au point de vente.</li>
                    <li><strong>Produits :</strong> indiquez au minimum le nom, le prix de vente et la quantité. Le prix d’achat et le seuil de sécurité restent facultatifs, mais ils améliorent le calcul du bénéfice et les alertes de stock.</li>
                    <li><strong>Packs :</strong> utilisez-les pour vendre ensemble plusieurs produits ou services, avec une présentation plus rapide en caisse.</li>
                    <li><strong>Fournisseurs :</strong> @if($hasSuppliers) cette fonctionnalité est disponible dans votre plan ; enregistrez vos fournisseurs puis utilisez-les lors des inventaires. @else elle devient disponible lorsque le plan de la compagnie l’inclut. @endif</li>
                </ul>
            </div>
        </details>

        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-cart3" aria-hidden="true"></i> Point de vente et historique</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body">
                <ul class="saas-guide-list">
                    <li>Recherchez un produit, ajoutez-le au panier puis ajustez la quantité si nécessaire.</li>
                    <li>Associez un client lorsque vous souhaitez conserver son historique ou préparer une facture personnalisée.</li>
                    <li>Dans « Remise et code promo », saisissez une remise ponctuelle ou un code valide. Le système vérifie toujours le code dans la compagnie active avant de calculer le total.</li>
                    <li>Confirmez la vente seulement après avoir vérifié le panier. La vente met à jour le stock et les mouvements liés côté serveur.</li>
                    <li>Consultez l’<strong>Historique</strong> pour retrouver les ventes, les montants et les opérations réalisées.</li>
                </ul>
                <div class="saas-guide-tip"><i class="bi bi-receipt" aria-hidden="true"></i><span>Après une vente, l’envoi de facture par SMS ou WhatsApp dépend des canaux autorisés dans la configuration de communication de la compagnie.</span></div>
            </div>
        </details>

        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-people" aria-hidden="true"></i> Clients, inventaires et codes promo</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body">
                <ul class="saas-guide-list">
                    <li><strong>Clients :</strong> enregistrez les informations utiles avec leur accord, puis retrouvez leur historique de ventes.</li>
                    <li><strong>Inventaires :</strong> utilisez-les pour corriger ou enregistrer les mouvements de stock. Ajoutez un motif clair afin de faciliter le contrôle ultérieur.</li>
                    <li><strong>Codes promo :</strong> @if($hasPromoCodes) votre plan les inclut. Créez un code, choisissez sa réduction et sa date d’expiration, puis partagez-le avec vos clients. @else cette fonctionnalité apparaît lorsque le plan de la compagnie l’inclut. @endif Un code expiré ou inactif ne peut pas réduire une vente.</li>
                </ul>
            </div>
        </details>
    </section>

    <section class="saas-guide-section" id="gestion" aria-labelledby="guide-management-title">
        <div class="saas-guide-section-heading"><i class="bi bi-clipboard-data" aria-hidden="true"></i><div><h2 id="guide-management-title">Suivre l’entreprise</h2><p>Organisez vos caisses, vos communications et votre boutique en ligne.</p></div></div>

        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-wallet2" aria-hidden="true"></i> Caisses et opérations comptables</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body"><p>La rubrique Comptabilité permet de consulter les caisses et les opérations enregistrées. Les ventes alimentent les mouvements prévus par le système ; les ajouts ou corrections manuels doivent être justifiés avec un libellé compréhensible.</p><div class="saas-guide-tip"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Ne partagez pas vos accès et ne modifiez pas une opération pour masquer une erreur : utilisez plutôt le flux de correction prévu par votre gestionnaire.</span></div></div>
        </details>

        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-chat-square-dots" aria-hidden="true"></i> Communications et quotas</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body"><p>Les gestionnaires peuvent activer les canaux de communication, consulter la consommation et acheter des quotas SMS/WhatsApp lorsque le plan le permet. Un paiement est confirmé par le serveur après vérification sécurisée auprès de KPrimePay ; un retour de navigateur seul ne suffit pas.</p></div>
        </details>

        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-shop-window" aria-hidden="true"></i> Boutique e-commerce</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body"><p>@if($hasEcommerce) Votre plan inclut l’e-commerce. Configurez la boutique, vérifiez les produits publiés puis suivez les commandes depuis le menu E-commerce. Les commandes sont converties en ventes selon le flux prévu afin de protéger le stock. @else L’e-commerce apparaît lorsque le plan de la compagnie l’inclut. Il permet de proposer une boutique dédiée où les clients consultent les produits et commandent en ligne. @endif</p></div>
        </details>
    </section>

    <section class="saas-guide-section" id="equipe" aria-labelledby="guide-team-title">
        <div class="saas-guide-section-heading"><i class="bi bi-people" aria-hidden="true"></i><div><h2 id="guide-team-title">Équipe et paramètres</h2><p>Les accès doivent refléter les responsabilités réelles de chaque personne.</p></div></div>
        <div class="saas-guide-callout-grid">
            <article class="saas-guide-callout"><i class="bi bi-person-gear" aria-hidden="true"></i><div><strong>Utilisateurs et rôles</strong><span>Invitez uniquement les personnes concernées, attribuez-leur le rôle adapté et désactivez un accès devenu inutile.</span></div></article>
            <article class="saas-guide-callout"><i class="bi bi-gear" aria-hidden="true"></i><div><strong>Paramètres de compagnie</strong><span>Gardez les coordonnées, l’identité et les préférences de la compagnie à jour afin que les documents et communications restent fiables.</span></div></article>
        </div>
    </section>

    <section class="saas-guide-section" id="abonnement" aria-labelledby="guide-subscription-title">
        <div class="saas-guide-section-heading"><i class="bi bi-credit-card" aria-hidden="true"></i><div><h2 id="guide-subscription-title">Abonnement et limites du plan</h2><p>Cette partie est accessible aux gestionnaires autorisés de la compagnie.</p></div></div>
        <details class="saas-guide-accordion" open>
            <summary><span><i class="bi bi-credit-card-2-front" aria-hidden="true"></i> Choisir, renouveler ou améliorer le plan</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body"><p>Depuis « Mon abonnement », comparez les plans, leurs limites, les fonctionnalités incluses et la durée souhaitée. La durée peut aller de 1 à 12 mois ; à 12 mois, le tarif annuel applique un mois offert. La descente de plan n’est pas proposée : un gestionnaire peut renouveler son plan actuel ou choisir une offre supérieure.</p><div class="saas-guide-tip"><i class="bi bi-credit-card" aria-hidden="true"></i><span>Avant de payer, vérifiez le plan, la durée et le montant. L’activation intervient uniquement après la confirmation sécurisée du paiement par KPrimePay.</span></div></div>
        </details>
        <details class="saas-guide-accordion">
            <summary><span><i class="bi bi-lock" aria-hidden="true"></i> Que se passe-t-il à expiration ?</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="saas-guide-accordion-body"><p>Lorsque le contrôle d’abonnement est activé, l’application reste consultable mais les écritures métier sont bloquées après expiration : nouvelles ventes, ajouts, modifications et achats ne peuvent plus être réalisés. Renouvelez depuis l’abonnement pour rétablir les actions concernées. Les données existantes restent visibles.</p></div>
        </details>
    </section>

    <section class="saas-guide-section" id="securite" aria-labelledby="guide-security-title">
        <div class="saas-guide-section-heading"><i class="bi bi-shield-check" aria-hidden="true"></i><div><h2 id="guide-security-title">Bonnes pratiques de sécurité</h2><p>Quelques habitudes simples protègent votre activité et vos données.</p></div></div>
        <div class="saas-guide-callout-grid">
            <article class="saas-guide-callout"><i class="bi bi-key" aria-hidden="true"></i><div><strong>Protégez votre compte</strong><span>Utilisez un mot de passe unique et robuste. Déconnectez-vous des appareils partagés.</span></div></article>
            <article class="saas-guide-callout"><i class="bi bi-person-check" aria-hidden="true"></i><div><strong>Respectez les rôles</strong><span>Chaque personne doit posséder son propre accès : ne partagez pas un compte entre collaborateurs.</span></div></article>
        </div>
        <div class="saas-guide-inline-links"><a href="{{ route('subscriptions.index') }}"><i class="bi bi-credit-card" aria-hidden="true"></i> Gérer mon abonnement</a><a href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2" aria-hidden="true"></i> Retour au tableau de bord</a></div>
    </section>
</div>
@endsection
