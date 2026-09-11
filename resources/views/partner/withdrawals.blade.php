@extends('layouts.partner')

@section('title', 'Mes retraits')
@section('page-title', 'Mes retraits')

@section('content')
@php
    $xof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF';
    $gatewayLabels = ['MIXX-YAS-TG' => 'Mixx by Yas (TMoney)', 'MOOV-MONEY-TG' => 'Moov Money (Flooz)'];
@endphp
<x-ui.page-header title="Retraits Mobile Money" eyebrow="Portefeuille sécurisé" icon="bi-send-check" description="Enregistrez un compte vérifié et confirmez chaque demande par e-mail. Votre solde reste protégé jusqu’au résultat du versement." />

<div class="saas-metric-grid saas-metric-grid-3 partner-dashboard-metrics">
    <x-ui.stat-card label="Solde disponible" :value="$xof($eligibility['balances']['available'])" hint="Montant réservé aux retraits futurs" icon="bi-wallet2" />
    <x-ui.stat-card label="Montant minimum" :value="$xof($eligibility['minimum'])" hint="Seuil configuré par la plateforme" icon="bi-arrow-down-circle" />
    <x-ui.stat-card label="Clients qualifiés" :value="(int) $partner->qualified_clients_count" :hint="'Minimum requis : '.$eligibility['required_clients']" icon="bi-people" />
</div>

<div class="partner-withdrawal-layout">
<x-ui.card title="Compte de versement" description="Le numéro est chiffré et affiché partiellement. Toute modification invalide sa vérification.">
        @forelse($accounts as $account)
            <div class="partner-withdrawal-account-item">
                <div class="partner-withdrawal-account-icon"><i class="bi bi-phone-vibrate" aria-hidden="true"></i></div>
                <div class="partner-withdrawal-account-copy"><strong>{{ $gatewayLabels[$account->gateway] ?? $account->gateway }}</strong><span>{{ $account->maskedPhone() }} · {{ $account->beneficiary_name }}</span></div>
                @if($account->status === 'verified' && $disabledVerifiedAccounts->contains('id', $account->id))
                    <x-ui.status variant="danger">Retrait indisponible</x-ui.status>
                @elseif($account->status === 'verified')
                    <x-ui.status variant="success">Vérifié</x-ui.status>
                @elseif($pendingAccounts->contains('id', $account->id))
                    <button type="button" class="partner-withdrawal-status-action" data-bs-toggle="modal" data-bs-target="#partnerAccountConfirmModal" data-account-id="{{ $account->id }}" data-account-label="{{ ($gatewayLabels[$account->gateway] ?? $account->gateway).' · '.$account->maskedPhone() }}" aria-controls="partnerAccountConfirmModal">
                        <span class="partner-withdrawal-status-main"><i class="bi bi-envelope-exclamation" aria-hidden="true"></i><span class="partner-withdrawal-status-label">Confirmation e-mail requise</span></span>
                    </button>
                @else
                    <x-ui.status variant="neutral">Confirmation e-mail requise</x-ui.status>
                @endif
            </div>
        @empty
            <x-ui.empty-state class="partner-withdrawal-empty" icon="bi-phone" title="Aucun compte enregistré" description="Ajoutez le numéro Mobile Money qui recevra vos futurs versements." />
        @endforelse
        @if($disabledVerifiedAccounts->isNotEmpty())
            <x-ui.notice variant="danger" class="mt-3"><strong>Moyen de retrait indisponible.</strong> {{ $disabledVerifiedAccounts->count() }} compte vérifié est conservé, mais ne peut pas être utilisé tant que son opérateur est désactivé par la plateforme.</x-ui.notice>
        @endif
        @php($accountFormOpen = old('country_code') !== null || $errors->has('country_code') || $errors->has('gateway') || $errors->has('phone_number') || $errors->has('beneficiary_name'))
        <details class="partner-withdrawal-account-editor" @if($accountFormOpen) open @endif>
            <summary>
                <span class="partner-withdrawal-editor-icon"><i class="bi bi-plus-circle" aria-hidden="true"></i></span>
                <span class="partner-withdrawal-editor-copy"><strong>Ajouter un compte</strong><small>Configurez le compte Mobile Money de vos futurs versements.</small></span>
                <i class="bi bi-chevron-down partner-withdrawal-editor-chevron" aria-hidden="true"></i>
            </summary>
            <form method="POST" action="{{ route('partner.withdrawals.accounts.store') }}" class="partner-withdrawal-account-form">@csrf
                <div class="partner-withdrawal-form-notice"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Ce compte est réservé à vos versements. Son numéro est chiffré avant enregistrement.</span></div>
                <div class="saas-form-grid">
                    <div class="saas-form-group">
                        <label for="withdrawalCountry">Pays</label>
                        <select id="withdrawalCountry" name="country_code" required @if($errors->has('country_code')) aria-invalid="true" aria-describedby="withdrawalCountry-error" @endif>
                            @foreach($countries as $country)<option value="{{ $country['code'] }}" @selected(old('country_code', 'TG') === $country['code'])>{{ $country['name'] }}</option>@endforeach
                        </select>
                        @error('country_code')<small id="withdrawalCountry-error" class="saas-field-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>{{ $message }}</span></small>@enderror
                    </div>
                    <div class="saas-form-group">
                        <label for="withdrawalGateway">Opérateur</label>
                        <select id="withdrawalGateway" name="gateway" required @if($errors->has('gateway')) aria-invalid="true" aria-describedby="withdrawalGateway-error" @endif>
                            @foreach($gateways['TG'] ?? [] as $gateway)<option value="{{ $gateway }}" @selected(old('gateway') === $gateway)>{{ $gatewayLabels[$gateway] ?? $gateway }}</option>@endforeach
                        </select>
                        @error('gateway')<small id="withdrawalGateway-error" class="saas-field-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>{{ $message }}</span></small>@enderror
                    </div>
                    <div class="saas-form-group">
                        <label for="withdrawalPhone">Numéro Mobile Money</label>
                        <input id="withdrawalPhone" name="phone_number" value="{{ old('phone_number') }}" inputmode="numeric" autocomplete="tel-national" maxlength="8" pattern="[0-9]{8}" placeholder="Ex. 96 00 00 00" required @if($errors->has('phone_number')) aria-invalid="true" @endif aria-describedby="withdrawalPhoneHelp">
                        <small id="withdrawalPhoneHelp" class="{{ $errors->has('phone_number') ? 'saas-field-error' : '' }}" role="{{ $errors->has('phone_number') ? 'alert' : 'status' }}"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>{{ $errors->first('phone_number') ?: '8 chiffres requis.' }}</span></small>
                    </div>
                    <div class="saas-form-group">
                        <label for="withdrawalBeneficiary">Nom du bénéficiaire</label>
                        <input id="withdrawalBeneficiary" name="beneficiary_name" value="{{ old('beneficiary_name') }}" maxlength="120" autocomplete="name" placeholder="Ex. Adjo Koffi" required @if($errors->has('beneficiary_name')) aria-invalid="true" aria-describedby="withdrawalBeneficiary-error" @endif>
                        @error('beneficiary_name')<small id="withdrawalBeneficiary-error" class="saas-field-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>{{ $message }}</span></small>@enderror
                    </div>
                </div>
                <div class="partner-withdrawal-form-actions"><span><i class="bi bi-info-circle" aria-hidden="true"></i> Une modification demandera une nouvelle vérification.</span><x-ui.button type="submit" loading-text="Enregistrement…"><i class="bi bi-shield-check" aria-hidden="true"></i> Enregistrer le compte</x-ui.button></div>
            </form>
        </details>
</x-ui.card>

@if($pendingAccounts->isNotEmpty())
    <x-ui.modal id="partnerAccountConfirmModal" title="Confirmer votre compte" eyebrow="Vérification sécurisée" variant="primary" size="md">
        <div class="partner-account-confirm-intro">
            <span class="partner-account-confirm-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
            <div>
                <h3>Confirmation e-mail requise</h3>
                <p>Nous avons envoyé un code à six chiffres à votre adresse e-mail pour valider le compte <strong id="partnerAccountConfirmLabel">{{ ($gatewayLabels[$pendingAccount->gateway] ?? $pendingAccount->gateway).' · '.$pendingAccount->maskedPhone() }}</strong>.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('partner.withdrawals.accounts.confirm.submit') }}" class="partner-account-confirm-form" id="partnerAccountConfirmForm">
            @csrf
            <input type="hidden" name="account_id" id="partnerAccountConfirmAccountId" value="{{ $pendingAccount->id }}">
            <div class="saas-form-group">
                <label for="partnerAccountConfirmCode">Code reçu par e-mail</label>
                <input id="partnerAccountConfirmCode" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" value="{{ old('code') }}" placeholder="000000" required @if($errors->has('code')) aria-invalid="true" aria-describedby="partnerAccountConfirmCode-error" @endif>
                <small class="partner-account-confirm-help">Le code est valable pendant 10 minutes.</small>
                @error('code')
                    <small id="partnerAccountConfirmCode-error" class="saas-field-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>{{ $message }}</span></small>
                @enderror
            </div>
        </form>
        <div class="partner-account-confirm-resend">
                <span>Vous n’avez rien reçu ?</span>
                <form method="POST" action="{{ route('partner.withdrawals.accounts.confirm.resend') }}" id="partnerAccountConfirmResendForm">
                    @csrf
                    <input type="hidden" name="account_id" id="partnerAccountConfirmResendAccountId" value="{{ $pendingAccount->id }}">
                    <button type="submit" class="saas-btn saas-btn-outline partner-account-confirm-resend-button" data-account-id="{{ $pendingAccount->id }}" data-cooldown-kind="resend">
                        <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                        <span class="partner-account-confirm-resend-label">Renvoyer le code</span>
                        <span class="partner-account-confirm-resend-countdown" aria-live="polite"></span>
                    </button>
                </form>
        </div>
        <div class="partner-account-confirm-actions">
                <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                <x-ui.button type="submit" form="partnerAccountConfirmForm" loading-text="Vérification…"><i class="bi bi-check2-circle" aria-hidden="true"></i> Confirmer le compte</x-ui.button>
        </div>
    </x-ui.modal>
@endif

<x-ui.card title="Demander un retrait" description="Vous recevez toujours le montant demandé. Les frais réels KPrimePay sont à votre charge et ne sont jamais absorbés par la plateforme.">
    @if($eligibility['eligible'] && $availableVerifiedAccounts->isNotEmpty())
        <form method="POST" action="{{ route('partner.withdrawals.request') }}" class="partner-withdrawal-request-form" id="partnerWithdrawalRequest" data-available="{{ $eligibility['balances']['available'] }}" data-fee-bps="{{ $payoutFeeBps }}">@csrf
            <div class="partner-withdrawal-request-fields">
                <div class="saas-form-group"><label for="withdrawalAccount">Compte Mobile Money vérifié</label><div class="saas-select-wrap partner-withdrawal-select-wrap"><i class="bi bi-phone-vibrate saas-select-icon" aria-hidden="true"></i><select id="withdrawalAccount" name="account_id" required @if($errors->has('account_id')) aria-invalid="true" aria-describedby="withdrawalAccount-error" @endif>@foreach($availableVerifiedAccounts as $account)<option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>{{ $gatewayLabels[$account->gateway] ?? $account->gateway }} · {{ $account->maskedPhone() }}</option>@endforeach</select></div><small>Seuls les opérateurs activés par la plateforme sont proposés.</small>@error('account_id')<small id="withdrawalAccount-error" class="saas-field-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>{{ $message }}</span></small>@enderror</div>
                <div class="saas-form-group"><label for="withdrawalAmount">Montant à recevoir <span class="partner-withdrawal-field-unit">XOF</span></label><input id="withdrawalAmount" name="amount" type="number" data-minimum="{{ $eligibility['minimum'] }}" inputmode="numeric" placeholder="Ex. 5 000" required aria-describedby="withdrawalFeeHelp"><small>Minimum configuré : {{ $xof($eligibility['minimum']) }}.</small></div>
                <div class="saas-form-group"><label for="withdrawalPassword">Mot de passe partenaire</label><div class="partner-withdrawal-password-wrap"><input id="withdrawalPassword" name="current_password" type="password" autocomplete="current-password" placeholder="Votre mot de passe" required><i class="bi bi-shield-lock" aria-hidden="true"></i></div><small>Une confirmation par e-mail sera demandée à l’étape suivante.</small></div>
            </div>
            <div class="partner-withdrawal-request-summary">
                <div id="withdrawalFeeHelp" class="partner-withdrawal-estimate" role="status" aria-live="polite">
                    <div><span>Montant envoyé</span><strong id="withdrawalRequestedAmount">0 XOF</strong></div>
                    <div><span>Estimation des frais KPrimePay (jusqu’à {{ number_format($payoutFeeBps / 100, 2, ',', ' ') }} %)</span><strong id="withdrawalFeeAmount">0 XOF</strong></div>
                    <div class="partner-withdrawal-estimate-total"><span>Montant maximal réservé</span><strong id="withdrawalTotalAmount">0 XOF</strong></div>
                    <p id="withdrawalEstimateMessage">Saisissez le montant que vous souhaitez recevoir sur votre Mobile Money.</p>
                </div>
                </div>
                <div class="partner-withdrawal-request-action"><x-ui.button id="withdrawalRequestButton" type="submit" loading-text="Envoi du code…" disabled><i class="bi bi-envelope-lock" aria-hidden="true"></i> Recevoir le code e-mail</x-ui.button><small><i class="bi bi-lock" aria-hidden="true"></i> Aucun versement n’est lancé à cette étape.</small></div>
        </form>
    @else
        @if(!$eligibility['eligible'])
            <x-ui.notice variant="warning">Votre demande est encore indisponible. Corrigez les conditions suivantes avant de pouvoir la préparer :</x-ui.notice>
            <ul class="partner-withdrawal-blockers" aria-label="Conditions restantes pour demander un retrait">
                @foreach($eligibility['reasons'] as $reason)
                    <li><i class="bi bi-exclamation-circle" aria-hidden="true"></i><span>{{ $reason }}</span></li>
                @endforeach
            </ul>
        @elseif($disabledVerifiedAccounts->isNotEmpty())
            <x-ui.notice variant="danger"><strong>Aucun moyen de retrait autorisé.</strong> Vos comptes vérifiés utilisent un opérateur actuellement désactivé. Ajoutez un compte d’un opérateur activé ou réactivez cet opérateur dans l’administration.</x-ui.notice>
        @else
            <x-ui.notice variant="info">La demande sera disponible dès qu’un compte Mobile Money vérifié sera enregistré.</x-ui.notice>
        @endif
    @endif
</x-ui.card>

</div>

<x-ui.card title="Suivi de vos retraits" description="Les dix dernières demandes et leur état comptable.">
    @php($withdrawalStatuses = ['otp_verified' => ['neutral', 'En contrôle'], 'approved' => ['info', 'En cours d’envoi'], 'processing' => ['info', 'En traitement'], 'succeeded' => ['success', 'Versé'], 'failed' => ['danger', 'Refusé'], 'unknown' => ['warning', 'À vérifier'], 'cancelled' => ['neutral', 'Annulé']])
    @forelse($withdrawals as $withdrawal)
        @php([$variant, $label] = $withdrawalStatuses[$withdrawal->status] ?? ['neutral', ucfirst($withdrawal->status)])
        <div class="partner-withdrawal-account-item">
            <div class="partner-withdrawal-account-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></div>
            <div class="partner-withdrawal-account-copy">
                <strong>{{ $xof($withdrawal->amount) }} · {{ $gatewayLabels[$withdrawal->account_snapshot['gateway'] ?? ''] ?? ($withdrawal->account_snapshot['gateway'] ?? 'Mobile Money') }}</strong>
                <span>Demande du {{ optional($withdrawal->requested_at)->format('d/m/Y à H:i') }}@if($withdrawal->failure_reason && in_array($withdrawal->status, ['failed', 'unknown'], true)) · {{ $withdrawal->failure_reason }}@endif</span>
            </div>
            <x-ui.status :variant="$variant">{{ $label }}</x-ui.status>
        </div>
    @empty
        <x-ui.empty-state class="partner-withdrawal-empty" icon="bi-clock-history" title="Aucun retrait demandé" description="Vos demandes confirmées apparaîtront ici avec leur état." />
    @endforelse
</x-ui.card>

<x-ui.card title="Prochaine étape" description="Dès que vos conditions d’éligibilité sont réunies, vous pourrez préparer une demande de retrait.">
    <div class="d-flex gap-3 align-items-start"><i class="bi bi-info-circle fs-4 text-primary"></i><p class="mb-0">Le formulaire de demande apparaîtra automatiquement. Vous choisirez votre compte de versement, indiquerez le montant à retirer, puis confirmerez la demande avec votre mot de passe et un code reçu par e-mail.</p></div>
</x-ui.card>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const country = document.getElementById('withdrawalCountry');
    const gateway = document.getElementById('withdrawalGateway');
    if (!country || !gateway) return;
    const gateways = @json($gateways);
    const catalog = @json($gatewayCatalog);
    const phone = document.getElementById('withdrawalPhone');
    const help = document.getElementById('withdrawalPhoneHelp');
    const sync = () => {
        const values = gateways[country.value] || [];
        gateway.replaceChildren(...values.map(value => new Option(catalog[country.value]?.[value]?.label || value, value)));
        gateway.disabled = values.length === 0;
        validatePhone();
    };
    const validatePhone = () => {
        const digits = (phone.value || '').replace(/\D/g, '').slice(0, 8);
        if (phone.value !== digits) phone.value = digits;
        const prefixes = catalog[country.value]?.[gateway.value]?.prefixes || [];
        let message = '';
        if (digits.length > 0 && digits.length < 8) {
            message = 'Le numéro doit contenir 8 chiffres.';
        } else if (digits.length === 8 && prefixes.length === 0) {
            message = 'Aucun préfixe n’est configuré pour cet opérateur.';
        } else if (digits.length === 8 && !prefixes.includes(digits.slice(0, 2))) {
            message = `Les deux premiers chiffres (${digits.slice(0, 2)}) ne correspondent pas aux préfixes acceptés : ${prefixes.join(', ')}.`;
        }
        phone.setCustomValidity(message);
        help.querySelector('span').textContent = message || (prefixes.length ? `8 chiffres. Préfixes acceptés : ${prefixes.join(', ')}.` : '8 chiffres requis.');
        help.classList.toggle('saas-field-error', Boolean(message));
        help.setAttribute('role', message ? 'alert' : 'status');
    };
    country.addEventListener('change', sync);
    gateway.addEventListener('change', validatePhone);
    phone.addEventListener('input', validatePhone);
    sync();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('partnerWithdrawalRequest');
    if (!form) return;
    const amount = document.getElementById('withdrawalAmount');
    const password = document.getElementById('withdrawalPassword');
    const button = document.getElementById('withdrawalRequestButton');
    const requested = document.getElementById('withdrawalRequestedAmount');
    const fees = document.getElementById('withdrawalFeeAmount');
    const total = document.getElementById('withdrawalTotalAmount');
    const message = document.getElementById('withdrawalEstimateMessage');
    const available = Number(form.dataset.available || 0);
    const feeBps = Number(form.dataset.feeBps || 0);
    const format = value => `${new Intl.NumberFormat('fr-FR').format(Math.max(0, value))} XOF`;
    const minimum = Number(amount.dataset.minimum || 1);
    const showValidationAlert = (title, text) => {
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title,
                text,
                confirmButtonText: 'Compris',
                buttonsStyling: false,
                customClass: {
                    popup: 'saas-swal',
                    confirmButton: 'saas-btn saas-btn-primary',
                },
            });
        } else {
            message.textContent = text;
        }
    };
    const refreshEstimate = () => {
        const sent = Math.max(0, Math.floor(Number(amount.value || 0)));
        const fee = sent ? Math.ceil((sent * feeBps) / 10000) : 0;
        const debited = sent + fee;
        const hasAmount = sent >= minimum;
        const enough = hasAmount && debited <= available;
        requested.textContent = format(sent);
        fees.textContent = format(fee);
        total.textContent = format(debited);
        form.classList.toggle('is-over-balance', hasAmount && !enough);
        amount.setCustomValidity('');
        if (!sent) {
            message.textContent = 'Saisissez le montant que vous souhaitez recevoir sur votre Mobile Money.';
        } else if (!hasAmount) {
            message.textContent = `Le montant à recevoir doit être d’au moins ${format(minimum)}.`;
        } else if (!enough) {
            message.textContent = `Solde insuffisant : le total de ${format(debited)} dépasse votre solde disponible de ${format(available)}.`;
        } else {
            message.textContent = `Vous recevrez ${format(sent)}. Jusqu’à ${format(fee)} sont réservés pour les frais KPrimePay ; tout écart non utilisé vous est automatiquement restitué après confirmation.`;
        }
        // Le bouton reste cliquable sous le minimum afin d'expliquer clairement la règle.
        // Il est désactivé uniquement lorsque le total débité dépasse le solde disponible.
        button.disabled = sent > 0 && debited > available;
    };
    amount.addEventListener('input', refreshEstimate);
    password.addEventListener('input', refreshEstimate);
    form.addEventListener('submit', function (event) {
        const sent = Math.max(0, Math.floor(Number(amount.value || 0)));
        const fee = sent ? Math.ceil((sent * feeBps) / 10000) : 0;
        const debited = sent + fee;

        if (sent < minimum) {
            event.preventDefault();
            showValidationAlert(
                'Montant minimum non atteint',
                `Le montant minimum pour un retrait est de ${format(minimum)}. Vous avez saisi ${format(sent)}.`
            );
            return;
        }

        if (debited > available) {
            event.preventDefault();
            showValidationAlert(
                'Solde insuffisant',
                `Le montant maximal réservé de ${format(debited)} dépasse votre solde disponible de ${format(available)}.`
            );
        }
    });
    refreshEstimate();
});
</script>
@if($pendingAccounts->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('partnerAccountConfirmModal');
    if (!modal) return;
    const accountId = modal.querySelector('#partnerAccountConfirmAccountId');
    const resendAccountId = modal.querySelector('#partnerAccountConfirmResendAccountId');
    const label = modal.querySelector('#partnerAccountConfirmLabel');
    const cooldownDuration = 60;
    const cooldownKey = (kind, id) => `partner-withdrawal-${kind}-cooldown-${id}`;
    const storage = {
        get(key) { try { return window.sessionStorage.getItem(key); } catch (_) { return null; } },
        set(key, value) { try { window.sessionStorage.setItem(key, value); } catch (_) {} },
        remove(key) { try { window.sessionStorage.removeItem(key); } catch (_) {} },
    };
    const formatCountdown = (seconds) => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    const applyCooldown = (button, remaining, kind = button?.dataset.cooldownKind || 'confirm') => {
        if (!button?.dataset.accountId) return;
        const isResend = kind === 'resend';
        const text = button.querySelector(isResend ? '.partner-account-confirm-resend-label' : '.partner-withdrawal-status-label');
        const countdown = button.querySelector(isResend ? '.partner-account-confirm-resend-countdown' : '.partner-withdrawal-status-countdown');
        const fallback = isResend ? 'Renvoyer le code' : 'Confirmation e-mail requise';
        const baseLabel = button.dataset.baseLabel || text?.textContent.trim() || fallback;
        button.dataset.baseLabel = baseLabel;
        if (button._partnerCooldownTimer) window.clearInterval(button._partnerCooldownTimer);
        const endAt = Date.now() + (remaining * 1000);
        button.dataset.cooldownKind = kind;
        storage.set(cooldownKey(kind, button.dataset.accountId), String(endAt));
        const render = () => {
            const secondsLeft = Math.max(0, Math.ceil((endAt - Date.now()) / 1000));
            if (secondsLeft === 0) {
                button.disabled = false;
                button.removeAttribute('aria-disabled');
                button.classList.remove('is-cooldown');
                if (text) text.textContent = baseLabel;
                if (countdown) countdown.textContent = '';
                button.removeAttribute('aria-label');
                storage.remove(cooldownKey(kind, button.dataset.accountId));
                if (button._partnerCooldownTimer) window.clearInterval(button._partnerCooldownTimer);
                button._partnerCooldownTimer = null;
                return;
            }
            button.disabled = true;
            button.setAttribute('aria-disabled', 'true');
            button.classList.add('is-cooldown');
            if (text) text.textContent = baseLabel;
            if (countdown) countdown.textContent = `Disponible dans ${formatCountdown(secondsLeft)}`;
            button.setAttribute('aria-label', `${baseLabel}. Disponible dans ${formatCountdown(secondsLeft)}`);
        };
        render();
        button._partnerCooldownTimer = window.setInterval(render, 1000);
    };
    modal.ownerDocument.querySelectorAll('.partner-account-confirm-resend-button[data-account-id][data-cooldown-kind]').forEach((button) => {
        const kind = button.dataset.cooldownKind;
        const endAt = Number(storage.get(cooldownKey(kind, button.dataset.accountId)) || 0);
        const secondsLeft = Math.ceil((endAt - Date.now()) / 1000);
        if (secondsLeft > 0) applyCooldown(button, secondsLeft, kind);
    });
    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger || !trigger.dataset.accountId) return;
        accountId.value = trigger.dataset.accountId;
        if (resendAccountId) resendAccountId.value = trigger.dataset.accountId;
        label.textContent = trigger.dataset.accountLabel || '';
        if (resendButton) resendButton.dataset.accountId = trigger.dataset.accountId;
    });
    const resendForm = modal.querySelector('#partnerAccountConfirmResendForm');
    const resendButton = modal.querySelector('.partner-account-confirm-resend-button');
    resendForm?.addEventListener('submit', function () {
        if (resendButton) {
            resendButton.dataset.accountId = accountId.value;
            applyCooldown(resendButton, cooldownDuration, 'resend');
        }
    });
    modal.addEventListener('shown.bs.modal', function () {
        modal.querySelector('#partnerAccountConfirmCode')?.focus();
    });
});
</script>
@endif
@if($pendingAccounts->isNotEmpty() && (session('open_partner_account_confirmation') || $errors->has('code')))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('partnerAccountConfirmModal');
    if (!modal || !window.bootstrap?.Modal) return;
    const accountId = @json(old('account_id', $pendingAccount?->id));
    const trigger = modal.ownerDocument.querySelector('[data-account-id="' + accountId + '"]');
    if (trigger) {
        modal.querySelector('#partnerAccountConfirmAccountId').value = trigger.dataset.accountId;
        modal.querySelector('#partnerAccountConfirmLabel').textContent = trigger.dataset.accountLabel;
    }
    const instance = window.bootstrap.Modal.getOrCreateInstance(modal);
    modal.addEventListener('shown.bs.modal', function () { document.getElementById('partnerAccountConfirmCode')?.focus(); }, { once: true });
    instance.show();
});
</script>
@endif
@endpush
