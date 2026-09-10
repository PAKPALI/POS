@extends('layouts.platform')
@section('title', 'Programme partenaires')
@section('page-title', 'Programme partenaires')
@section('content')
<div class="platform-settings-page">
    <nav class="platform-settings-nav" aria-label="Paramètres plateforme">
        <a class="platform-settings-tab" href="{{ route('platform.settings.general') }}"><i class="bi bi-sliders2" aria-hidden="true"></i> Général</a>
        <a class="platform-settings-tab" href="{{ route('platform.settings.edit') }}"><i class="bi bi-tags" aria-hidden="true"></i> Tarifs et coûts</a>
        <a class="platform-settings-tab active" href="{{ route('platform.settings.partners.edit') }}" aria-current="page"><i class="bi bi-people" aria-hidden="true"></i> Partenaires</a>
        <a class="platform-settings-tab" href="{{ route('platform.subscriptions.preflight') }}"><i class="bi bi-check2-circle" aria-hidden="true"></i> Pré-contrôle abonnements</a>
    </nav>

    <header class="platform-settings-intro">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-people" aria-hidden="true"></i> Programme partenaires</p>
            <h2>Ouverture du portail et pays disponibles</h2>
            <p>Les pays sélectionnés sont les seuls proposés à l’inscription. Leur indicatif est appliqué automatiquement au numéro local.</p>
        </div>
        <span class="platform-settings-intro-badge"><i class="bi bi-shield-check" aria-hidden="true"></i> Contrôle administrateur</span>
    </header>

    <form method="POST" action="{{ route('platform.settings.partners.update') }}">
        @csrf @method('PUT')
        <div class="platform-settings-layout">
            <div class="platform-settings-column">
                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-toggle-on" aria-hidden="true"></i> Disponibilité</p>
                        <h2>Accès au programme</h2>
                        <p>Gardez l’inscription fermée pendant la recette si vous ne souhaitez pas encore accepter de nouveaux partenaires.</p>
                    </header>
                    <label class="platform-settings-toggle-card">
                        <span class="saas-switch-line">
                            <input type="checkbox" name="partners_enabled" value="1" class="saas-switch-input" @checked($partnersEnabled)>
                            <span class="saas-switch-control"></span>
                        </span>
                        <span><strong>Activer le portail partenaire</strong><small>Autorise la connexion et l’accès des partenaires actifs.</small></span>
                    </label>
                    <label class="platform-settings-toggle-card">
                        <span class="saas-switch-line">
                            <input type="checkbox" name="registration_enabled" value="1" class="saas-switch-input" @checked($registrationEnabled)>
                            <span class="saas-switch-control"></span>
                        </span>
                        <span><strong>Ouvrir les inscriptions</strong><small>Exige que le portail soit lui-même activé ; l’e-mail reste obligatoirement vérifié.</small></span>
                    </label>
                </section>

                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-geo-alt" aria-hidden="true"></i> Pays</p>
                        <h2>Pays de résidence actifs</h2>
                        <p>Le Togo est activé par défaut. Les indicatifs et longueurs de numéro sont contrôlés côté serveur ; ils ne peuvent pas être modifiés ici par erreur.</p>
                    </header>
                    <div class="platform-settings-service-list">
                        @foreach($catalog as $code => $country)
                            <label class="platform-settings-service-row">
                                <span class="platform-settings-service-icon"><i class="bi bi-globe-africa" aria-hidden="true"></i></span>
                                <span class="platform-settings-service-copy"><strong>{{ $country['name'] }}</strong><span>{{ $country['dial_code'] }} · {{ $country['phone_min_length'] }}@if($country['phone_min_length'] !== $country['phone_max_length'])–{{ $country['phone_max_length'] }}@endif chiffres</span></span>
                                <span class="platform-status-chip {{ in_array($code, $activeCodes, true) ? 'is-success' : 'is-muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ in_array($code, $activeCodes, true) ? 'Actif' : 'Inactif' }}</span>
                                <span class="saas-switch-line">
                                    <input type="checkbox" name="countries[]" value="{{ $code }}" class="saas-switch-input" @checked(in_array($code, $activeCodes, true))>
                                    <span class="saas-switch-control"></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('countries')<small class="saas-field-error" role="alert">{{ $message }}</small>@enderror
                </section>
            </div>

            <div class="platform-settings-column">
                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Codes partenaires</p>
                        <h2>Règle de personnalisation</h2>
                        <p>Après un changement, l’ancien code est retiré et un nouveau choix est bloqué pendant ce délai.</p>
                    </header>
                    <div class="platform-settings-field">
                        <label class="form-label" for="partner-code-cooldown">Délai avant un nouveau changement <span>(jours)</span></label>
                        <input id="partner-code-cooldown" class="form-control" type="number" name="code_cooldown_days" min="1" max="365" value="{{ old('code_cooldown_days', $codeCooldownDays) }}" required>
                        <small class="form-text">De 1 à 365 jours. La valeur par défaut est de 30 jours.</small>
                        @error('code_cooldown_days')<small class="saas-field-error" role="alert">{{ $message }}</small>@enderror
                    </div>
                </section>

                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-shield-lock" aria-hidden="true"></i> Garde-fou</p>
                        <h2>Retraits partenaires</h2>
                        <p>Les transferts restent désactivés pendant la préparation KPrimePay, mais leurs garde-fous sont configurables et journalisés ici.</p>
                    </header>
                    <div class="platform-settings-preview">
                        <div class="platform-settings-preview-head"><span><i class="bi bi-lock-fill" aria-hidden="true"></i> Payouts</span><small>Hors périmètre</small></div>
                        <p class="mb-0">État opérationnel : <strong>{{ $payoutsEnabled ? 'activé' : 'désactivé' }}</strong>. Aucun appel KPrimePay n’est effectué par cet écran.</p>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6 platform-settings-field"><label class="form-label" for="payout-min-xof">Montant minimum (XOF)</label><input id="payout-min-xof" class="form-control" type="number" name="payout_min_xof" min="1" value="{{ old('payout_min_xof', $payoutMinXof) }}"><small class="form-text">Seuil de demande, sans frais KPrimePay.</small></div>
                        <div class="col-md-6 platform-settings-field"><label class="form-label" for="payout-min-clients">Clients qualifiés minimum</label><input id="payout-min-clients" class="form-control" type="number" name="payout_min_qualified_clients" min="0" value="{{ old('payout_min_qualified_clients', $payoutMinQualifiedClients) }}"></div>
                        <div class="col-md-6 platform-settings-field"><label class="form-label" for="auto-approval-max">Approbation automatique max. (XOF)</label><input id="auto-approval-max" class="form-control" type="number" name="auto_approval_max_xof" min="0" value="{{ old('auto_approval_max_xof', $autoApprovalMaxXof) }}"><small class="form-text">0 = revue manuelle systématique.</small></div>
                        <div class="col-md-6 platform-settings-field"><label class="platform-settings-toggle-card h-100"><span class="saas-switch-line"><input type="checkbox" name="risk_review_enabled" value="1" class="saas-switch-input" @checked($riskReviewEnabled)><span class="saas-switch-control"></span></span><span><strong>Revue de risque obligatoire</strong><small>Conserve les demandes en attente d’un contrôle.</small></span></label></div>
                    </div>
                </section>

                <section class="platform-settings-section platform-settings-action-card">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-check2-circle" aria-hidden="true"></i> Validation</p>
                        <h2>Appliquer les changements</h2>
                        <p>Chaque modification est journalisée avec son motif et confirmée par votre mot de passe plateforme.</p>
                    </header>
                    <div class="platform-settings-field"><label class="form-label" for="partner-settings-reason">Motif de la modification</label><textarea id="partner-settings-reason" class="form-control" name="reason" minlength="5" maxlength="500" rows="3" required>{{ old('reason') }}</textarea></div>
                    <div class="platform-settings-field platform-settings-field-spaced"><label class="form-label" for="partner-settings-password">Votre mot de passe plateforme</label><input id="partner-settings-password" class="form-control" type="password" name="current_password" required></div>
                    <div class="platform-settings-action"><button class="btn btn-warning" data-loading-text="Enregistrement…"><i class="bi bi-save2" aria-hidden="true"></i> Enregistrer la configuration</button></div>
                </section>
            </div>
        </div>
    </form>
</div>
@endsection
