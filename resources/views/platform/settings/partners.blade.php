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

                <section class="platform-settings-section platform-settings-payout-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-shield-lock" aria-hidden="true"></i> Garde-fou</p>
                        <h2>Retraits partenaires</h2>
                        <p>Activez les retraits uniquement lorsque le compte KPrimePay Payout et les contrôles de recette sont prêts. Chaque changement est journalisé.</p>
                    </header>
                    <label class="platform-settings-toggle-card platform-settings-payout-toggle">
                        <span class="saas-switch-line">
                            <input type="checkbox" name="payouts_enabled" value="1" class="saas-switch-input" @checked($payoutsEnabled)>
                            <span class="saas-switch-control"></span>
                        </span>
                        <span><strong>Activer les retraits partenaires</strong><small>Autorise les partenaires éligibles à préparer une demande de retrait. Les contrôles de solde, de seuil, de compte vérifié et de 2FA restent obligatoires.</small></span>
                    </label>
                    <div class="platform-settings-payout-state {{ $payoutsEnabled ? 'is-enabled' : 'is-disabled' }}">
                        <div class="platform-settings-payout-state-head"><span><i class="bi {{ $payoutsEnabled ? 'bi-unlock-fill' : 'bi-lock-fill' }}" aria-hidden="true"></i> État opérationnel</span><strong>{{ $payoutsEnabled ? 'Activé' : 'Désactivé' }}</strong></div>
                        <p class="mb-0">{{ $payoutsEnabled ? 'Les demandes peuvent être préparées selon les garde-fous configurés ci-dessous.' : 'Les demandes restent bloquées pour tous les partenaires tant que ce réglage est désactivé.' }}</p>
                    </div>
                    <div class="platform-settings-payout-grid">
                        <div class="platform-settings-field"><label class="form-label" for="payout-min-xof">Montant minimum (XOF)</label><input id="payout-min-xof" class="form-control" type="number" name="payout_min_xof" min="1" value="{{ old('payout_min_xof', $payoutMinXof) }}"><small class="form-text">Seuil de demande, sans frais KPrimePay.</small></div>
                        <div class="platform-settings-field"><label class="form-label" for="payout-min-clients">Clients qualifiés minimum</label><input id="payout-min-clients" class="form-control" type="number" name="payout_min_qualified_clients" min="0" value="{{ old('payout_min_qualified_clients', $payoutMinQualifiedClients) }}"></div>
                        <div class="platform-settings-field"><label class="form-label" for="payout-fee-percent">Plafond des frais KPrimePay (%)</label><input id="payout-fee-percent" class="form-control" type="number" name="payout_fee_percent" min="0" max="50" step="0.01" value="{{ old('payout_fee_percent', number_format($payoutFeeBps / 100, 2, '.', '')) }}"><small class="form-text">Réglez-le au moins au tarif KPrimePay actif. Il détermine la réserve maximale : le partenaire reçoit le montant demandé et seul le coût réel lui est facturé ; l’excédent est restitué.</small></div>
                        <div class="platform-settings-field"><label class="form-label" for="auto-approval-max">Plafond d’envoi automatique (XOF)</label><input id="auto-approval-max" class="form-control" type="number" name="auto_approval_max_xof" min="1" value="{{ old('auto_approval_max_xof', max(1, $autoApprovalMaxXof)) }}"><small class="form-text">Toute demande éligible jusqu’à ce plafond est envoyée automatiquement après confirmation e-mail.</small></div>
                    </div>
                    <div class="platform-settings-field mt-4">
                        <label class="form-label">Opérateurs de retrait disponibles</label>
                        <small class="form-text d-block mb-2">Seuls les opérateurs activés ici seront proposés au partenaire lors de l’enregistrement de son compte Mobile Money.</small>
                        @foreach($payoutGatewayCatalog as $country => $gateways)
                            @if(in_array($country, $activeCodes, true))
                                <div class="platform-settings-service-list mb-2">
                                    @foreach($gateways as $gateway => $details)
                                        <label class="platform-settings-service-row">
                                            <span class="platform-settings-service-icon"><i class="bi bi-phone" aria-hidden="true"></i></span>
                                            <span class="platform-settings-service-copy"><strong>{{ $details['label'] }}</strong><span>{{ $country }} · préfixes {{ implode(', ', $details['prefixes']) }} · 8 chiffres</span></span>
                                            <span class="saas-switch-line"><input type="checkbox" name="payout_gateways[{{ $country }}][]" value="{{ $gateway }}" class="saas-switch-input" @checked(in_array($gateway, old('payout_gateways.'.$country, $activePayoutGateways[$country] ?? []), true))><span class="saas-switch-control"></span></span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
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
