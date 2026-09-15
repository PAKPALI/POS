@extends('layouts.platform')
@section('title','Paramètres généraux')
@section('page-title','Paramètres généraux')
@section('content')
<div class="platform-settings-page">
    <nav class="platform-settings-nav" aria-label="Paramètres plateforme">
        <a class="platform-settings-tab active" href="{{ route('platform.settings.general') }}" aria-current="page"><i class="bi bi-sliders2" aria-hidden="true"></i> Général</a>
        <a class="platform-settings-tab" href="{{ route('platform.settings.edit') }}"><i class="bi bi-tags" aria-hidden="true"></i> Tarifs et coûts</a>
        <a class="platform-settings-tab" href="{{ route('platform.settings.social-networks.edit') }}"><i class="bi bi-share" aria-hidden="true"></i> Réseaux sociaux</a>
        @if(auth('platform')->user()->hasPlatformPermission('platform.partners.manage'))<a class="platform-settings-tab" href="{{ route('platform.settings.partners.edit') }}"><i class="bi bi-people" aria-hidden="true"></i> Partenaires</a>@endif
        <a class="platform-settings-tab" href="{{ route('platform.subscriptions.preflight') }}"><i class="bi bi-check2-circle" aria-hidden="true"></i> Pré-contrôle abonnements</a>
    </nav>

    <header class="platform-settings-intro">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-sliders2" aria-hidden="true"></i> Centre de configuration</p>
            <h2>Configurez l’identité et les règles de votre plateforme</h2>
            <p>Centralisez les informations visibles, les services connectés et les garde-fous de la console SaaS.</p>
        </div>
        <span class="platform-settings-intro-badge"><i class="bi bi-shield-check" aria-hidden="true"></i> Configuration globale</span>
    </header>

    <form method="POST" action="{{ route('platform.settings.general.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="platform-settings-layout">
            <div class="platform-settings-column">
                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-building" aria-hidden="true"></i> Identité</p>
                        <h2>Identité et support</h2>
                        <p>Ces informations structurent l’identité de MAXANOU et les points de contact proposés aux utilisateurs.</p>
                    </header>
                    <div class="platform-settings-form-grid">
                        <div class="platform-settings-field"><label class="form-label" for="platform-app-name">Nom de l’application</label><input id="platform-app-name" class="form-control" name="app_name" value="{{ old('app_name',$values['identity.app_name']) }}" required></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-logo">Logo</label><input id="platform-logo" class="form-control" type="file" name="logo" accept="image/png,image/jpeg,image/webp"></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-support-email">E-mail du support</label><input id="platform-support-email" class="form-control" type="email" name="support_email" value="{{ old('support_email',$values['support.email']) }}"></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-support-phone">Téléphone du support</label><input id="platform-support-phone" class="form-control" name="support_phone" value="{{ old('support_phone',$values['support.phone']) }}"></div>
                        <div class="platform-settings-field platform-settings-field-wide"><label class="form-label" for="platform-support-hours">Horaires du support</label><input id="platform-support-hours" class="form-control" name="support_hours" value="{{ old('support_hours',$values['support.hours']) }}"></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-currency">Devise par défaut</label><input id="platform-currency" class="form-control" name="currency" maxlength="3" value="{{ old('currency',$values['defaults.currency']) }}" required></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-country">Pays par défaut</label><input id="platform-country" class="form-control" name="country" maxlength="2" value="{{ old('country',$values['defaults.country']) }}" required></div>
                    </div>
                </section>

                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-plug" aria-hidden="true"></i> Services</p>
                        <h2>Services externes</h2>
                        <p>Activez uniquement les canaux déjà configurés dans l’environnement de la plateforme. Les secrets restent stockés côté serveur et ne sont jamais affichés ici.</p>
                    </header>
                    <div class="platform-settings-service-list">
                        @foreach(['email'=>['label'=>'E-mail','icon'=>'bi-envelope-at'],'sms'=>['label'=>'SMS','icon'=>'bi-chat-text'],'whatsapp'=>['label'=>'WhatsApp','icon'=>'bi-whatsapp'],'kprimepay'=>['label'=>'KPrimePay','icon'=>'bi-credit-card-2-front']] as $key=>$service)
                            <div class="platform-settings-service-row">
                                <span class="platform-settings-service-icon"><i class="bi {{ $service['icon'] }}" aria-hidden="true"></i></span>
                                <div class="platform-settings-service-copy"><strong>{{ $service['label'] }}</strong><span>Canal de communication de la plateforme</span></div>
                                <span class="platform-status-chip {{ $serviceStatus[$key]?'is-success':'is-danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $serviceStatus[$key]?'Configuré':'Non configuré' }}</span>
                                <label class="saas-switch-line platform-settings-switch" aria-label="Activer {{ $service['label'] }}">
                                    <input type="checkbox" name="{{ $key }}_enabled" value="1" class="saas-switch-input" @checked(filter_var($values['services.'.$key.'.enabled'],FILTER_VALIDATE_BOOLEAN))>
                                    <span class="saas-switch-control"></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> Accès</p>
                        <h2>Application des abonnements</h2>
                        <p>Déterminez si la plateforme doit contrôler l’abonnement avant d’autoriser les accès métier.</p>
                    </header>
                    <label class="platform-settings-toggle-card">
                        <span class="saas-switch-line">
                            <input type="checkbox" name="subscriptions_enforcement_enabled" value="1" class="saas-switch-input" @checked(filter_var($values['subscriptions.enforcement_enabled'],FILTER_VALIDATE_BOOLEAN))>
                            <span class="saas-switch-control"></span>
                        </span>
                        <span><strong>Vérifier les abonnements avant les accès métier</strong><small>Ce contrôle reste désactivé par défaut pour le travail local et ne contourne ni les permissions ni les contrôles de paiement.</small></span>
                    </label>
                </section>

                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-clock-history" aria-hidden="true"></i> Délais</p>
                        <h2>Délais de sécurité</h2>
                        <p>Réglez la durée de validité des invitations, des codes 2FA et des paiements en attente.</p>
                    </header>
                    <div class="platform-settings-form-grid platform-settings-form-grid-three">
                        <div class="platform-settings-field"><label class="form-label" for="platform-invitation-expiry">Invitation <span>(heures)</span></label><input id="platform-invitation-expiry" class="form-control" type="number" name="invitation_expiry_hours" min="1" max="720" value="{{ $values['security.invitation_expiry_hours'] }}" required></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-2fa-expiry">Code 2FA <span>(minutes)</span></label><input id="platform-2fa-expiry" class="form-control" type="number" name="two_factor_expiry_minutes" min="2" max="60" value="{{ $values['security.two_factor_expiry_minutes'] }}" required></div>
                        <div class="platform-settings-field"><label class="form-label" for="platform-payment-expiry">Paiement <span>(heures)</span></label><input id="platform-payment-expiry" class="form-control" type="number" name="payment_expiry_hours" min="1" max="720" value="{{ $values['security.payment_expiry_hours'] }}" required></div>
                    </div>
                </section>
            </div>

            <div class="platform-settings-column">
                <section class="platform-settings-section">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-wrench-adjustable" aria-hidden="true"></i> Disponibilité</p>
                        <h2>Mode maintenance</h2>
                        <p>Informez les utilisateurs lorsqu’une intervention bloque temporairement l’application POS.</p>
                    </header>
                    <label class="platform-settings-toggle-card platform-settings-toggle-card-compact">
                        <span class="saas-switch-line">
                            <input type="checkbox" name="maintenance_enabled" value="1" class="saas-switch-input" @checked(filter_var($values['maintenance.enabled'],FILTER_VALIDATE_BOOLEAN))>
                            <span class="saas-switch-control"></span>
                        </span>
                        <span><strong>Bloquer temporairement l’application POS</strong><small>La console SaaS et les webhooks API restent accessibles.</small></span>
                    </label>
                    <div class="platform-settings-field platform-settings-field-spaced"><label class="form-label" for="platform-maintenance-message">Message affiché aux utilisateurs</label><textarea id="platform-maintenance-message" class="form-control" name="maintenance_message" minlength="10" maxlength="500" rows="4" required>{{ old('maintenance_message',$values['maintenance.message']) }}</textarea></div>
                </section>

                <section class="platform-settings-section platform-settings-action-card">
                    <header class="platform-settings-section-head">
                        <p class="platform-eyebrow"><i class="bi bi-check2-circle" aria-hidden="true"></i> Validation</p>
                        <h2>Appliquer les changements</h2>
                        <p>Ajoutez un motif et confirmez avec votre mot de passe pour journaliser cette modification.</p>
                    </header>
                    <div class="platform-settings-field"><label class="form-label" for="platform-reason">Motif de la modification</label><textarea id="platform-reason" class="form-control" name="reason" minlength="5" maxlength="500" rows="3" required></textarea></div>
                    <div class="platform-settings-field platform-settings-field-spaced"><label class="form-label" for="platform-current-password">Votre mot de passe plateforme</label><input id="platform-current-password" class="form-control" type="password" name="current_password" required></div>
                    <div class="platform-settings-action"><button class="btn btn-warning" data-loading-text="Enregistrement…"><i class="bi bi-save2" aria-hidden="true"></i> Enregistrer les paramètres</button></div>
                </section>

            </div>
        </div>
    </form>

    <section class="platform-settings-section platform-settings-company-enforcement">
        <header class="platform-settings-section-head">
            <p class="platform-eyebrow"><i class="bi bi-buildings" aria-hidden="true"></i> Exception par entreprise</p>
            <h2>Contrôle d’abonnement individuel</h2>
            <p>« Hériter » suit le réglage global. « Activer » ou « Désactiver » crée une exception propre à cette entreprise. Chaque changement est protégé par le mot de passe plateforme et inscrit au journal d’audit.</p>
        </header>
        <div class="table-responsive platform-table-scroll platform-company-enforcement-table-wrap">
            <table class="table platform-data-table platform-company-enforcement-table align-middle mb-0">
                <thead><tr><th>Entreprise</th><th>Statut</th><th>Réglage actuel</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @forelse($companies as $company)
                    @php($mode = $company->subscription_enforcement_enabled === null ? 'inherit' : ($company->subscription_enforcement_enabled ? 'enabled' : 'disabled'))
                    <tr>
                        <td><strong>{{ $company->name }}</strong><small class="platform-table-subtext">{{ $company->email }}</small></td>
                        <td><span class="platform-status-chip {{ $company->status === 'active' ? 'is-success' : 'is-danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $company->status === 'active' ? 'Active' : 'Suspendue' }}</span></td>
                            <td><span class="platform-status-chip {{ $mode === 'enabled' ? 'is-warning' : ($mode === 'disabled' ? 'is-danger' : 'is-muted') }}"><i class="bi bi-shield-check" aria-hidden="true"></i>{{ $mode === 'enabled' ? 'Contrôle activé pour cette entreprise' : ($mode === 'disabled' ? 'Contrôle désactivé (exception)' : 'Hérite du réglage global') }}</span></td>
                        <td class="text-end"><button type="button" class="platform-action-btn btn-warning" data-bs-toggle="modal" data-bs-target="#companyEnforcementModal-{{ $company->id }}" aria-label="Configurer l’abonnement de {{ $company->name }}" title="Configurer"><i class="bi bi-sliders2" aria-hidden="true"></i></button></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="platform-table-empty"><i class="bi bi-buildings" aria-hidden="true"></i><span>Aucune entreprise enregistrée.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @foreach($companies as $company)
            @php($mode = $company->subscription_enforcement_enabled === null ? 'inherit' : ($company->subscription_enforcement_enabled ? 'enabled' : 'disabled'))
            @php($modeLabel = $mode === 'enabled' ? 'Contrôle activé pour cette entreprise' : ($mode === 'disabled' ? 'Contrôle désactivé (exception)' : 'Hérite du réglage global'))
            <div class="modal fade platform-company-enforcement-dialog" id="companyEnforcementModal-{{ $company->id }}" tabindex="-1" aria-labelledby="companyEnforcementModalTitle-{{ $company->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                    <form method="POST" action="{{ route('platform.settings.general.companies.subscription-enforcement', $company) }}" class="modal-content saas-modal-content platform-company-enforcement-modal">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <div class="platform-company-enforcement-modal-heading">
                                <span class="platform-company-enforcement-modal-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                                <div class="platform-company-enforcement-modal-copy"><span class="platform-eyebrow"><i class="bi bi-sliders2" aria-hidden="true"></i> Exception d’abonnement</span><h2 class="modal-title" id="companyEnforcementModalTitle-{{ $company->id }}">{{ $company->name }}</h2><p class="platform-modal-subtitle"><i class="bi bi-envelope" aria-hidden="true"></i> {{ $company->email }}</p></div>
                            </div>
                            <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="platform-company-enforcement-current">
                                <span class="platform-company-enforcement-current-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
                                <div><span>Réglage actuel</span><strong>{{ $modeLabel }}</strong><small>Cette exception agit uniquement sur le contrôle d’accès de l’entreprise.</small></div>
                            </div>
                            <div class="platform-company-enforcement-modal-grid">
                                <div class="platform-settings-field"><label for="company-mode-{{ $company->id }}"><i class="bi bi-toggles" aria-hidden="true"></i> Mode de contrôle</label><select id="company-mode-{{ $company->id }}" name="mode" class="form-select" required><option value="inherit" @selected($mode === 'inherit')>Hériter du réglage global</option><option value="enabled" @selected($mode === 'enabled')>Activer pour cette entreprise</option><option value="disabled" @selected($mode === 'disabled')>Désactiver le contrôle (autoriser malgré l’expiration)</option></select><small>Le mode choisi ne modifie ni l’abonnement ni les paiements. L’exception désactivée autorise les actions métier malgré un abonnement expiré.</small></div>
                                <div class="platform-settings-field"><label for="company-reason-{{ $company->id }}"><i class="bi bi-chat-left-text" aria-hidden="true"></i> Motif de la modification</label><textarea id="company-reason-{{ $company->id }}" name="reason" class="form-control" minlength="5" maxlength="500" rows="3" placeholder="Expliquez la raison de cette exception…" required></textarea></div>
                                <div class="platform-settings-field"><label for="company-password-{{ $company->id }}"><i class="bi bi-key" aria-hidden="true"></i> Mot de passe plateforme</label><input id="company-password-{{ $company->id }}" name="current_password" type="password" class="form-control" autocomplete="current-password" required><small>Votre mot de passe confirme cette action sensible et sera journalisé.</small></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg" aria-hidden="true"></i> Annuler</button><button class="btn btn-warning" data-loading-text="Enregistrement…"><i class="bi bi-save2" aria-hidden="true"></i> Enregistrer l’exception</button></div>
                    </form>
                </div>
            </div>
        @endforeach
    </section>

    @php($settingLabels=['identity.app_name'=>'Nom de l’application','support.email'=>'E-mail du support','support.phone'=>'Téléphone du support','support.hours'=>'Horaires du support','defaults.currency'=>'Devise par défaut','defaults.country'=>'Pays par défaut','services.email.enabled'=>'Service e-mail','services.sms.enabled'=>'Service SMS','services.whatsapp.enabled'=>'Service WhatsApp','services.kprimepay.enabled'=>'Service KPrimePay','subscriptions.enforcement_enabled'=>'Contrôle des abonnements','security.invitation_expiry_hours'=>'Expiration des invitations','security.two_factor_expiry_minutes'=>'Expiration 2FA','security.payment_expiry_hours'=>'Expiration des paiements','maintenance.enabled'=>'Mode maintenance','maintenance.message'=>'Message de maintenance','identity.logo_path'=>'Logo'])
    <section class="platform-settings-section platform-settings-history platform-settings-history-panel">
        <header class="platform-settings-section-head">
            <p class="platform-eyebrow"><i class="bi bi-clock-history" aria-hidden="true"></i> Traçabilité</p>
            <h2>Historique des paramètres</h2>
            <p>Retrouvez les changements de configuration enregistrés dans la console.</p>
        </header>
        <form method="GET" class="platform-settings-history-toolbar">
            <div class="platform-settings-history-toolbar-copy"><strong>Journal des modifications</strong><small>Recherche par paramètre, valeur, administrateur ou motif.</small></div>
            <div class="platform-settings-history-toolbar-controls">
                <div class="platform-settings-history-search"><label for="general-history-search">Rechercher</label><div class="platform-table-search-input"><i class="bi bi-search" aria-hidden="true"></i><input id="general-history-search" type="search" name="history_search" value="{{ $filters['history_search'] ?? '' }}" placeholder="Paramètre, valeur, motif…"></div></div>
                <div class="platform-settings-history-page-size"><label for="general-history-per-page">Lignes</label><select id="general-history-per-page" name="history_per_page"><option value="10" @selected((int) ($filters['history_per_page'] ?? 20) === 10)>10</option><option value="20" @selected((int) ($filters['history_per_page'] ?? 20) === 20)>20</option><option value="50" @selected((int) ($filters['history_per_page'] ?? 20) === 50)>50</option><option value="100" @selected((int) ($filters['history_per_page'] ?? 20) === 100)>100</option></select></div>
                <button class="btn btn-warning platform-settings-history-search-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Rechercher</button>
                @if(!empty($filters['history_search']))<a class="platform-table-clear-search" href="{{ route('platform.settings.general') }}">Effacer</a>@endif
            </div>
        </form>
        <div class="platform-datatable"><div class="platform-datatable-meta"><span>Résultats filtrés</span><small>Affichage de {{ $history->firstItem() ?? 0 }} à {{ $history->lastItem() ?? 0 }} sur {{ $history->total() }}</small></div><div class="table-responsive platform-table-scroll">
            <table class="table platform-data-table platform-settings-history-table">
                <thead><tr><th>Paramètre</th><th>Ancienne valeur</th><th>Nouvelle valeur</th><th>Administrateur</th><th>Motif</th></tr></thead>
                <tbody>
                @forelse($history as $entry)
                    <tr><td><strong>{{ $settingLabels[$entry->key] ?? $entry->key }}</strong><small class="platform-table-subtext">{{ $entry->key }}</small></td><td><span class="platform-table-code">{{ Str::limit($entry->old_value ?: '—', 48) }}</span></td><td><span class="platform-table-code">{{ Str::limit($entry->new_value ?: '—', 48) }}</span></td><td><strong>{{ $entry->admin?->name ?? 'Administrateur supprimé' }}</strong></td><td><span class="platform-table-reason">{{ Str::limit($entry->reason, 100) }}</span></td></tr>
                @empty
                    <tr><td colspan="5" class="platform-table-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Aucun changement ne correspond à ces filtres.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
        <div class="platform-pagination">{{ $history->links() }}</div>
    </section>
</div>
@endsection
