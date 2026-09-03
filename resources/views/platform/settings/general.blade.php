@extends('layouts.platform')
@section('title','Paramètres généraux')
@section('page-title','Paramètres généraux')
@section('content')
<div class="platform-settings-page">
    <nav class="platform-settings-nav" aria-label="Paramètres plateforme">
        <a class="platform-settings-tab active" href="{{ route('platform.settings.general') }}" aria-current="page"><i class="bi bi-sliders2" aria-hidden="true"></i> Général</a>
        <a class="platform-settings-tab" href="{{ route('platform.settings.edit') }}"><i class="bi bi-tags" aria-hidden="true"></i> Tarifs et coûts</a>
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
                        <p>Activez uniquement les canaux déjà configurés dans l’environnement de la plateforme.</p>
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
