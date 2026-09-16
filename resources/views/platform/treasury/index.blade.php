@extends('layouts.platform')

@section('title', 'Trésorerie et retraits')
@section('page-title', 'Trésorerie et retraits')

@section('content')
@php($canManage = auth('platform')->user()->hasPlatformPermission('platform.treasury.manage'))
<div class="platform-page-stack platform-treasury-page">
    <section class="platform-card platform-finance-panel" aria-labelledby="treasury-title">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow"><i class="bi bi-safe2-fill" aria-hidden="true"></i> Coffre plateforme</p>
                <h2 id="treasury-title">Capacité de retrait protégée</h2>
                <p>Les paiements confirmés sont rapprochés des engagements partenaires et des sorties déjà réalisées avant toute proposition de retrait.</p>
            </div>
            <span class="platform-profit-chip"><i class="bi bi-wallet2" aria-hidden="true"></i><span><small>Capacité admin proposée</small><strong>{{ number_format($overview['admin_withdrawable'], 0, ',', ' ') }} XOF</strong></span></span>
        </header>
        <div class="alert alert-info platform-treasury-note mb-0"><i class="bi bi-shield-check" aria-hidden="true"></i><div><strong>Garde-fou financier</strong><span>Cette capacité tient compte des engagements partenaires et des frais effectifs. KPrimePay vérifie la balance de collecte réelle avant chaque envoi.</span></div></div>
    </section>

    <section class="platform-summary-grid" aria-label="Indicateurs de trésorerie">
        @foreach([
            ['Encaissements confirmés', number_format($overview['collections_total'], 0, ',', ' ').' XOF', 'bi-bank', 'success'],
            ['Quotas achetés', number_format($overview['quota_collections'], 0, ',', ' ').' XOF', 'bi-chat-dots', 'accent'],
            ['Abonnements', number_format($overview['subscription_collections'], 0, ',', ' ').' XOF', 'bi-calendar-check', 'violet'],
            ['Partenaires disponibles', number_format($overview['partner_available'], 0, ',', ' ').' XOF', 'bi-people', 'warning'],
            ['Partenaires réservés', number_format($overview['partner_reserved'], 0, ',', ' ').' XOF', 'bi-lock-fill', 'danger'],
            ['Retraits admin exécutés', number_format($overview['admin_paid'], 0, ',', ' ').' XOF', 'bi-send-check', 'accent'],
        ] as [$label, $value, $icon, $tone])
            <article class="platform-summary-metric is-{{ $tone }}"><span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span><span>{{ $label }}</span><strong>{{ $value }}</strong></article>
        @endforeach
    </section>

    <section class="platform-card platform-data-panel" aria-labelledby="allocation-title">
        <header class="platform-panel-head"><div><p class="platform-eyebrow">Répartition protégée</p><h2 id="allocation-title">Avant de sortir un franc du coffre</h2><p>Les commissions déjà versées, les fonds partenaires retirables et les réservations en cours sont exclus de la capacité administrateur.</p></div></header>
        <div class="table-responsive"><table class="table table-dark align-middle mb-0"><thead><tr><th>Élément</th><th>Montant</th><th>Traitement</th></tr></thead><tbody>
            <tr><td>Commissions partenaires retirables</td><td class="text-warning fw-bold">{{ number_format($overview['partner_available'], 0, ',', ' ') }} XOF</td><td><span class="platform-status-chip is-warning"><i class="bi bi-circle-fill" aria-hidden="true"></i> À préserver</span></td></tr>
            <tr><td>Commissions partenaires réservées</td><td class="text-danger fw-bold">{{ number_format($overview['partner_reserved'], 0, ',', ' ') }} XOF</td><td><span class="platform-status-chip is-danger"><i class="bi bi-circle-fill" aria-hidden="true"></i> Déjà engagées</span></td></tr>
            <tr><td>Commissions partenaires déjà versées</td><td>{{ number_format($overview['partner_paid'], 0, ',', ' ') }} XOF</td><td>Sorties historiques</td></tr>
            <tr><td>Retraits admin en attente de confirmation</td><td>{{ number_format($overview['admin_reserved'], 0, ',', ' ') }} XOF</td><td>Réservés jusqu’au statut KPrimePay</td></tr>
            <tr class="fw-bold"><td>Capacité nette proposée</td><td class="text-success">{{ number_format($overview['admin_withdrawable'], 0, ',', ' ') }} XOF</td><td>Disponible sous réserve de la balance KPrimePay</td></tr>
        </tbody></table></div>
    </section>

    @if($canManage)
    <div class="row g-4">
        <div class="col-xl-5">
            <section class="platform-card platform-filter-card platform-treasury-account-card h-100" aria-labelledby="account-title">
                <header class="platform-treasury-section-head"><div class="platform-treasury-section-title"><span class="platform-treasury-step">01</span><div><p class="platform-eyebrow"><i class="bi bi-phone" aria-hidden="true"></i> Bénéficiaire</p><h2 id="account-title">Compte Mobile Money administrateur</h2><p>Il est vérifié une seule fois par un code reçu à l’e-mail de l’administrateur connecté.</p></div></div><span class="platform-status-chip is-info"><i class="bi bi-shield-check" aria-hidden="true"></i> Vérification 2FA</span></header>
                <form method="POST" action="{{ route('platform.treasury.accounts.store') }}" class="platform-filter-grid platform-treasury-account-form mt-3">
                    @csrf
                    <div class="platform-filter-field"><label class="form-label" for="treasury-country">Pays</label><select class="form-select" id="treasury-country" name="country_code" required>@foreach($countries as $country)<option value="{{ $country['code'] }}" data-dial="{{ $country['dial_code'] }}" @selected(old('country_code') === $country['code'])>{{ $country['name'] }} ({{ $country['dial_code'] }})</option>@endforeach</select></div>
                    <div class="platform-filter-field"><label class="form-label" for="treasury-gateway">Opérateur</label><select class="form-select" id="treasury-gateway" name="gateway" required></select></div>
                    <div class="platform-filter-field is-wide"><label class="form-label" for="treasury-phone">Numéro Mobile Money</label><div class="input-group"><span class="input-group-text" id="treasury-dial">+228</span><input class="form-control" id="treasury-phone" name="phone_number" inputmode="numeric" autocomplete="tel" maxlength="8" placeholder="8 chiffres" value="{{ old('phone_number') }}" required></div><small class="form-text" id="treasury-prefix-help">Choisissez d’abord un opérateur.</small></div>
                    <div class="platform-filter-field is-wide"><label class="form-label" for="treasury-name">Prénom et nom du bénéficiaire</label><input class="form-control" id="treasury-name" name="beneficiary_name" maxlength="120" autocomplete="name" placeholder="Ex. Ama Doe" value="{{ old('beneficiary_name', $defaultBeneficiaryName) }}" required><small class="form-text">Saisissez les deux noms tels qu’ils sont enregistrés chez l’opérateur Mobile Money.</small></div>
                    <button class="btn btn-warning platform-filter-submit" data-loading-text="Envoi du code…"><i class="bi bi-envelope-check" aria-hidden="true"></i> Enregistrer le compte</button>
                </form>
                @if($accounts->contains('status', 'pending_verification'))
                    <div class="platform-treasury-confirm-box"><div class="platform-treasury-confirm-intro"><span class="platform-treasury-confirm-icon"><i class="bi bi-envelope-open" aria-hidden="true"></i></span><div><strong>Code de vérification envoyé</strong><small>Saisissez le code reçu par e-mail pour activer ce bénéficiaire.</small></div></div><form method="POST" action="{{ route('platform.treasury.accounts.verify') }}" class="platform-treasury-confirm-form">@csrf<label class="visually-hidden" for="treasury-account-code">Code de vérification du compte</label><input class="form-control" id="treasury-account-code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Code e-mail à 6 chiffres" required><button class="btn btn-outline-light" data-loading-text="Vérification…"><i class="bi bi-check2-circle" aria-hidden="true"></i> Valider le compte</button></form></div>
                @endif
                <section class="platform-treasury-account-list" aria-labelledby="account-list-title">
                    <div class="platform-treasury-account-list-head"><div><strong id="account-list-title">Numéros enregistrés</strong><small>Les numéros sont masqués pour protéger les bénéficiaires.</small></div><span class="platform-treasury-count">{{ $accounts->count() }}</span></div>
                    @forelse($accounts as $account)
                        @php($accountVariant = $account->status === 'verified' ? 'success' : ($account->status === 'pending_verification' ? 'warning' : 'muted'))
                        <article class="platform-treasury-account-item"><span class="platform-treasury-account-icon"><i class="bi bi-phone" aria-hidden="true"></i></span><div class="platform-treasury-account-copy"><strong>{{ $account->beneficiary_name }}</strong><small>{{ $account->gateway }} · {{ $account->maskedPhone() }} · {{ $account->country_code }}</small></div><div class="platform-treasury-account-meta"><span class="platform-status-chip is-{{ $accountVariant }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $account->status === 'verified' ? 'Vérifié' : ($account->status === 'pending_verification' ? 'En attente' : 'Indisponible') }}</span>@if($account->is_primary)<small>Principal</small>@endif</div></article>
                    @empty
                        <div class="platform-treasury-account-empty"><i class="bi bi-phone" aria-hidden="true"></i><span>Aucun numéro enregistré pour le moment.</span></div>
                    @endforelse
                </section>
            </section>
        </div>
        <div class="col-xl-7">
            <section class="platform-card platform-filter-card platform-treasury-request-card h-100" aria-labelledby="request-title">
                <header class="platform-treasury-section-head"><div class="platform-treasury-section-title"><span class="platform-treasury-step">02</span><div><p class="platform-eyebrow"><i class="bi bi-send-check" aria-hidden="true"></i> Sortie sécurisée</p><h2 id="request-title">Demander un retrait de trésorerie</h2><p>Choisissez le bénéficiaire, indiquez le montant, puis confirmez avec votre mot de passe et un code e-mail.</p></div></div><span class="platform-status-chip is-warning"><i class="bi bi-lock-fill" aria-hidden="true"></i> Double contrôle</span></header>
                @if($withdrawalAccounts->isEmpty())
                    <div class="alert alert-warning platform-treasury-empty-note mt-3 mb-0"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Aucun bénéficiaire disponible</strong><span>Ajoutez puis vérifiez un compte Mobile Money dont l’opérateur est activé pour pouvoir créer un retrait.</span></div></div>
                @else
                    <div class="platform-treasury-security-note"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>La demande est préparée sans débit immédiat. Le versement ne part qu’après validation du code e-mail.</span></div>
                    <form method="POST" action="{{ route('platform.treasury.withdrawals.start') }}" class="platform-filter-grid platform-treasury-request-form mt-3">@csrf
                        <div class="platform-filter-field is-wide"><label class="form-label" for="treasury-account">Compte Mobile Money vérifié</label><select class="form-select" id="treasury-account" name="platform_withdrawal_account_id" required>@foreach($withdrawalAccounts as $account)<option value="{{ $account->id }}">{{ $account->beneficiary_name }} · {{ $account->gateway }} · {{ $account->maskedPhone() }}</option>@endforeach</select></div>
                        <div class="platform-filter-field"><label class="form-label" for="treasury-amount">Montant à recevoir (XOF)</label><input class="form-control" id="treasury-amount" name="amount" type="number" min="1" max="{{ $overview['admin_withdrawable'] }}" inputmode="numeric" placeholder="Ex. 50 000" required><small class="platform-field-help">Le montant doit rester dans la capacité disponible.</small></div>
                        <div class="platform-filter-field"><label class="form-label" for="treasury-password">Mot de passe plateforme</label><input class="form-control" id="treasury-password" name="current_password" type="password" autocomplete="current-password" placeholder="Votre mot de passe" required><small class="platform-field-help">Une confirmation supplémentaire sera demandée par e-mail.</small></div>
                        <div class="platform-filter-field is-wide"><div class="alert alert-secondary platform-treasury-fee-summary mb-0"><i class="bi bi-calculator" aria-hidden="true"></i><div><strong id="treasury-total">Saisissez un montant pour calculer le plafond de frais.</strong><small>Plafond configuré : {{ number_format($overview['fee_bps'] / 100, 2, ',', ' ') }} %. Capacité proposée : {{ number_format($overview['admin_withdrawable'], 0, ',', ' ') }} XOF.</small></div></div></div>
                        <button class="btn btn-warning platform-filter-submit" id="treasury-send-code" data-loading-text="Envoi du code…"><i class="bi bi-envelope-check" aria-hidden="true"></i> Envoyer le code de confirmation</button>
                    </form>
                    <div class="platform-treasury-confirm-box"><div class="platform-treasury-confirm-intro"><span class="platform-treasury-confirm-icon"><i class="bi bi-envelope-open" aria-hidden="true"></i></span><div><strong>Étape 2 · Confirmer par e-mail</strong><small>Le code est valable quelques minutes et protège la sortie de trésorerie.</small></div></div><form method="POST" action="{{ route('platform.treasury.withdrawals.confirm') }}" class="platform-treasury-confirm-form">@csrf<label class="visually-hidden" for="treasury-withdrawal-code">Code de confirmation du retrait</label><input class="form-control" id="treasury-withdrawal-code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Code de confirmation à 6 chiffres" required><button class="btn btn-outline-light" data-loading-text="Création…"><i class="bi bi-check2-circle" aria-hidden="true"></i> Confirmer le retrait</button></form></div>
                @endif
            </section>
        </div>
    </div>
    @else
        <section class="platform-card"><div class="alert alert-info mb-0"><i class="bi bi-eye me-2" aria-hidden="true"></i>Votre rôle Finance est en lecture seule : seul un super-administrateur peut modifier un compte bénéficiaire ou initier une sortie de trésorerie.</div></section>
    @endif

    <section class="platform-card platform-data-panel" aria-labelledby="withdrawal-history-title">
        <header class="platform-panel-head"><div><p class="platform-eyebrow">Traçabilité</p><h2 id="withdrawal-history-title">Historique des retraits administrateur</h2><p>Chaque ligne correspond à une demande confirmée par code e-mail et journalisée.</p></div></header>
        <div class="table-responsive"><table class="table table-dark table-hover align-middle"><thead><tr><th>Référence</th><th>Bénéficiaire</th><th>Montant demandé</th><th>Frais KPrimePay</th><th>Statut</th><th>Date</th></tr></thead><tbody>@forelse($withdrawals as $withdrawal)
            @php($variant = $withdrawal->status === 'succeeded' ? 'success' : ($withdrawal->status === 'failed' ? 'danger' : ($withdrawal->status === 'unknown' ? 'warning' : 'warning')))
            <tr>
                <td><small>#{{ $withdrawal->id }} · {{ $withdrawal->transaction_id }}</small><br><small class="text-secondary">{{ $withdrawal->kpp_reference ?: 'Référence KPrimePay en attente' }}</small></td>
                <td>{{ $withdrawal->account_snapshot['beneficiary_name'] ?? '—' }}<br><small class="text-secondary">{{ $withdrawal->account_snapshot['gateway'] ?? '' }} · {{ $withdrawal->account_snapshot['phone'] ?? '' }}</small></td>
                <td>{{ number_format($withdrawal->amount, 0, ',', ' ') }} XOF</td>
                <td>{{ number_format($withdrawal->status === 'succeeded' ? $withdrawal->fees : $withdrawal->estimated_fees, 0, ',', ' ') }} XOF</td>
                <td>
                    <span class="platform-status-chip is-{{ $variant }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ ['otp_verified' => 'À envoyer', 'processing' => 'En cours', 'unknown' => 'À vérifier', 'succeeded' => 'Succès', 'failed' => 'Échoué'][$withdrawal->status] ?? $withdrawal->status }}</span>
                    @if($withdrawal->failure_reason)<br><small class="text-danger">{{ $withdrawal->failure_reason }}</small>@endif
                    @if($canManage && $withdrawal->status === 'unknown')
                        <form method="POST" action="{{ route('platform.treasury.withdrawals.retry', $withdrawal) }}" class="platform-treasury-retry-form mt-2" data-retry-unknown>
                            @csrf
                            <input type="hidden" name="reason" value="">
                            <button type="submit" class="btn btn-sm btn-outline-warning platform-table-action"><i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> Réautoriser l’envoi</button>
                        </form>
                    @endif
                </td>
                <td>{{ $withdrawal->requested_at?->format('d/m/Y H:i') }}</td>
            </tr>
        @empty<tr><td colspan="6" class="text-center text-secondary py-5">Aucun retrait de trésorerie n’a encore été demandé.</td></tr>@endforelse</tbody></table></div>
        <div class="platform-pagination">{{ $withdrawals->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const countries = @json($countries), catalog = @json($gatewayCatalog), active = @json($activeGateways), country = document.getElementById('treasury-country'), gateway = document.getElementById('treasury-gateway'), dial = document.getElementById('treasury-dial'), help = document.getElementById('treasury-prefix-help');
    const refreshGateways = () => {
        if (!country || !gateway) return;
        const code = country.value, current = gateway.value, options = active[code] || [];
        gateway.innerHTML = options.map(value => `<option value="${value}">${(catalog[code] && catalog[code][value] && catalog[code][value].label) || value}</option>`).join('');
        if (options.includes(current)) gateway.value = current;
        const c = countries.find(item => item.code === code); dial.textContent = c ? c.dial_code : '';
        const refreshPrefix = () => { const prefixes = (((catalog[code] || {})[gateway.value] || {}).prefixes || []); help.textContent = prefixes.length ? `Préfixes autorisés : ${prefixes.join(', ')} · 8 chiffres.` : 'Aucun opérateur n’est actuellement activé.'; };
        gateway.onchange = refreshPrefix; refreshPrefix();
    };
    if (country) { country.addEventListener('change', refreshGateways); refreshGateways(); }
    const amount = document.getElementById('treasury-amount'), total = document.getElementById('treasury-total'), button = document.getElementById('treasury-send-code'), capacity = {{ (int) $overview['admin_withdrawable'] }}, bps = {{ (int) $overview['fee_bps'] }};
    const refreshAmount = () => { if (!amount || !total || !button) return; const value = Math.max(0, parseInt(amount.value || '0', 10) || 0), fees = Math.ceil(value * bps / 10000), debit = value + fees, okay = value > 0 && debit <= capacity; total.textContent = value ? `Montant envoyé : ${value.toLocaleString('fr-FR')} XOF · plafond de frais KPrimePay : ${fees.toLocaleString('fr-FR')} XOF · capacité mobilisée : ${debit.toLocaleString('fr-FR')} XOF.` : 'Saisissez un montant pour calculer le plafond de frais.'; button.disabled = !okay; total.closest('.alert').classList.toggle('alert-danger', value > 0 && !okay); total.closest('.alert').classList.toggle('alert-secondary', !(value > 0 && !okay)); };
    if (amount) { amount.addEventListener('input', refreshAmount); refreshAmount(); }
    document.querySelectorAll('[data-retry-unknown]').forEach(form => form.addEventListener('submit', event => {
        if (!window.Swal) return;
        event.preventDefault();
        const reason = form.querySelector('[name="reason"]');
        Swal.fire({
            title: 'Réautoriser cet envoi ?',
            html: '<p class="mb-2">KPrimePay doit avoir confirmé <strong>TRANSACTION_NOT_FOUND</strong>. L’envoi sera placé dans la file avec la même clé d’idempotence.</p><p class="mb-0 text-warning">N’utilisez cette action que si aucun transfert n’apparaît chez KPrimePay.</p>',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motif obligatoire',
            inputPlaceholder: 'Ex. KPrimePay confirme l’absence de la transaction.',
            inputAttributes: { maxlength: 500 },
            showCancelButton: true,
            confirmButtonText: 'Oui, réautoriser',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: value => {
                if (!value || value.trim().length < 10) {
                    Swal.showValidationMessage('Indiquez un motif d’au moins 10 caractères.');
                    return false;
                }
                reason.value = value.trim();
                return true;
            },
        }).then(result => {
            if (result.isConfirmed) HTMLFormElement.prototype.submit.call(form);
        });
    }));
})();
</script>
@endpush
