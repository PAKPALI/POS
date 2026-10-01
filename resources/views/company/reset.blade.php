@extends('layouts.saas')
@section('title', 'Réinitialiser l’entreprise')
@push('styles')
<link href="{{ asset('hub/assets/css/company-reset.css') }}?v=20261001-2" rel="stylesheet">
@endpush
@section('content')
<div class="saas-page-heading">
    <div><span class="saas-eyebrow">Réservé au propriétaire</span><h1>Réinitialiser {{ $company->name }}</h1><p>Choisissez les données à effacer et vérifiez leurs conséquences avant de confirmer.</p></div>
    <a href="{{ route('profil') }}" class="saas-btn saas-btn-ghost"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour au profil</a>
</div>
<x-ui.alert variant="danger"><div><strong>Suppression définitive</strong><p>Les données sélectionnées et leurs dépendances seront supprimées sans possibilité de restauration depuis l’application. Exportez d’abord les informations que vous souhaitez conserver. Cette action concerne uniquement {{ $company->name }}.</p></div></x-ui.alert>
<x-ui.alert variant="info"><div><strong>Ce que vous conservez</strong><p>Votre compte propriétaire, votre abonnement et ses dates, vos quotas SMS/WhatsApp restants, vos autres entreprises, les paramètres, le logo et l’adresse de votre boutique. Les preuves de paiement et de sécurité restent conservées par la plateforme.</p></div></x-ui.alert>
<div id="companyResetFeedback" class="saas-alert saas-alert-danger" role="alert" hidden></div>
@if(count($catalog))
<form id="companyResetForm" class="company-reset-layout" novalidate>
    @csrf
    <section class="saas-card company-reset-options" aria-labelledby="resetOptionsHeading">
        <h2 id="resetOptionsHeading">1. Données à réinitialiser</h2>
        <p>Seules les rubriques contenant des données sont proposées. Les éléments nécessaires à une suppression cohérente se cochent automatiquement.</p>
        <p>Si vous réinitialisez les produits, packs, ventes, stocks ou commandes, les paniers et brouillons sauvegardés sur les appareils seront vidés lors de leur prochaine ouverture.</p>
        <div class="company-reset-grid">
            @foreach($catalog as $key => $item)
            <div class="company-reset-option" data-reset-card="{{ $key }}">
                <label class="company-reset-option-label" for="reset-{{ $key }}">
                    <input type="checkbox" id="reset-{{ $key }}" value="{{ $key }}" data-reset-option>
                    <span><strong>{{ $item['label'] }}</strong><small>{{ $item['count'] }} élément(s) présent(s)</small></span>
                </label>
                <p>{{ $item['impact'] }}</p>
                <p class="company-reset-dependency" data-reset-reason hidden></p>
            </div>
            @endforeach
        </div>
    </section>
    <aside class="saas-card company-reset-confirmation" aria-labelledby="resetConfirmHeading">
        <h2 id="resetConfirmHeading">2. Votre confirmation</h2>
        <div id="companyResetSummary" class="company-reset-summary" aria-live="polite">Aucune rubrique sélectionnée.</div>
        <label class="company-reset-terms" for="resetTerms"><input id="resetTerms" type="checkbox"><span>J’accepte les conditions de réinitialisation : j’ai lu les impacts directs et indirects, je suis autorisé à effacer ces données et j’accepte leur suppression définitive sans possibilité de restauration depuis l’application. Je comprends que les achats de quotas seront retirés de mon journal, mais que leurs preuves de paiement restent conservées.</span></label>
        <x-ui.input id="resetPassword" name="current_password" type="password" label="Votre mot de passe actuel" autocomplete="current-password" required />
        <button type="submit" id="requestResetCode" class="saas-btn saas-btn-danger" disabled data-loading-text="Envoi du code…"><i class="bi bi-shield-lock" aria-hidden="true"></i>Supprimer les données sélectionnées</button>
        <small>Une vérification en deux étapes par email est obligatoire avant toute suppression.</small>
    </aside>
</form>
<section id="companyResetVerification" class="saas-card company-reset-verification" hidden>
    <h2>3. Vérification en deux étapes</h2>
    <p id="companyResetCodeMessage" role="status"></p>
    <div id="companyResetFinalSummary" class="company-reset-summary"></div>
    <form id="companyResetCodeForm">
        @csrf
        <x-ui.input id="resetCode" name="code" label="Code reçu par email" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required />
        <div class="saas-modal-actions">
            <button type="button" id="cancelCompanyReset" class="saas-btn saas-btn-ghost">Annuler et revoir la sélection</button>
            <button type="submit" class="saas-btn saas-btn-danger" data-loading-text="Réinitialisation…"><i class="bi bi-trash" aria-hidden="true"></i>Confirmer la suppression définitive</button>
        </div>
    </form>
</section>
@else
<x-ui.empty-state title="Aucune donnée à réinitialiser" description="Cette entreprise ne contient aucune donnée proposée à la réinitialisation." />
@endif
@endsection
@push('scripts')
<script src="{{ asset('hub/assets/js/company-reset.js') }}?v=20261001-2" defer></script>
<script type="application/json" id="companyResetConfig">@json(['catalog' => $catalog, 'challengeUrl' => route('company.reset.challenge'), 'executeUrl' => route('company.reset.execute')])</script>
@endpush
