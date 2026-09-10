@extends('layouts.partner')

@section('title', 'Mes retraits')
@section('page-title', 'Mes retraits')

@section('content')
@php
    $xof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF';
    $gatewayLabels = ['MIXX-YAS-TG' => 'Mixx by Yas (TMoney)', 'MOOV-MONEY-TG' => 'Moov Money (Flooz)'];
@endphp
<x-ui.page-header title="Retraits Mobile Money" eyebrow="Portefeuille sécurisé" icon="bi-send-check" description="Préparez votre compte de versement et consultez votre éligibilité. Les transferts KPrimePay restent désactivés pendant cette phase de préparation." />

<div class="saas-metric-grid partner-dashboard-metrics">
    <x-ui.stat-card label="Solde disponible" :value="$xof($eligibility['balances']['available'])" hint="Montant réservé aux retraits futurs" icon="bi-wallet2" />
    <x-ui.stat-card label="Montant minimum" :value="$xof($eligibility['minimum'])" hint="Seuil configuré par la plateforme" icon="bi-arrow-down-circle" />
    <x-ui.stat-card label="Clients qualifiés" :value="$partner->qualified_clients_count" :hint="Minimum requis : ".$eligibility['required_clients']" icon="bi-people" />
</div>

<div class="partner-dashboard-grid">
    <x-ui.card title="Éligibilité au retrait" description="Tous les garde-fous doivent être satisfaits avant toute demande.">
        @if($eligibility['eligible'])
            <x-ui.notice variant="success"><i class="bi bi-check-circle" aria-hidden="true"></i> Votre compte est éligible. L’ouverture opérationnelle sera annoncée après la validation KPrimePay.</x-ui.notice>
        @else
            <x-ui.notice variant="warning"><i class="bi bi-shield-lock" aria-hidden="true"></i> Les retraits ne sont pas encore ouverts.</x-ui.notice>
            <ul class="mb-0 mt-3">@foreach($eligibility['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul>
        @endif
        <p class="text-muted small mt-3 mb-0">Aucun appel KPrimePay et aucun mouvement d’argent externe ne sont effectués sur cette page.</p>
    </x-ui.card>

    <x-ui.card title="Compte de versement" description="Le numéro est chiffré et affiché partiellement. Toute modification invalide sa vérification.">
        @forelse($accounts as $account)
            <div class="d-flex align-items-center justify-content-between gap-3 border rounded-3 p-3 mb-2">
                <div><strong>{{ $gatewayLabels[$account->gateway] ?? $account->gateway }}</strong><div class="text-muted small">{{ $account->maskedPhone() }} · {{ $account->beneficiary_name }}</div></div>
                <x-ui.status :variant="$account->status === 'verified' ? 'success' : 'neutral'">{{ $account->status === 'verified' ? 'Vérifié' : 'À vérifier' }}</x-ui.status>
            </div>
        @empty
            <x-ui.empty-state icon="bi-phone" title="Aucun compte enregistré" description="Ajoutez le numéro Mobile Money qui recevra vos futurs versements." />
        @endforelse
        <details class="mt-3"><summary class="saas-btn saas-btn-secondary d-inline-flex">Ajouter un compte</summary>
            <form method="POST" action="{{ route('partner.withdrawals.accounts.store') }}" class="mt-3">@csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="withdrawalCountry">Pays</label><select id="withdrawalCountry" name="country_code" class="form-select" required>@foreach($countries as $country)<option value="{{ $country['code'] }}">{{ $country['name'] }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label" for="withdrawalGateway">Opérateur</label><select id="withdrawalGateway" name="gateway" class="form-select" required>@foreach($gateways['TG'] ?? [] as $gateway)<option value="{{ $gateway }}">{{ $gatewayLabels[$gateway] ?? $gateway }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label" for="withdrawalPhone">Numéro</label><input id="withdrawalPhone" name="phone_number" class="form-control" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" placeholder="Ex. 96 00 00 00" required><small id="withdrawalPhoneHelp" class="form-text">8 chiffres requis.</small></div>
                    <div class="col-md-6"><label class="form-label" for="withdrawalBeneficiary">Nom du bénéficiaire</label><input id="withdrawalBeneficiary" name="beneficiary_name" class="form-control" maxlength="120" required></div>
                </div>
                <div class="mt-3"><x-ui.button type="submit" loading-text="Enregistrement…"><i class="bi bi-shield-check" aria-hidden="true"></i> Enregistrer le compte</x-ui.button></div>
            </form>
        </details>
    </x-ui.card>
</div>

<x-ui.card title="Demander un retrait" description="Votre mot de passe et un code e-mail sont obligatoires avant toute réservation.">
    @php($verifiedAccounts = $accounts->where('status', 'verified'))
    @if($eligibility['eligible'] && $verifiedAccounts->isNotEmpty())
        <form method="POST" action="{{ route('partner.withdrawals.request') }}" class="row g-3">@csrf
            <div class="col-md-4"><label class="form-label" for="withdrawalAccount">Compte vérifié</label><select id="withdrawalAccount" name="account_id" class="form-select" required>@foreach($verifiedAccounts as $account)<option value="{{ $account->id }}">{{ $gatewayLabels[$account->gateway] ?? $account->gateway }} · {{ $account->maskedPhone() }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="withdrawalAmount">Montant (XOF)</label><input id="withdrawalAmount" name="amount" class="form-control" type="number" min="{{ $eligibility['minimum'] }}" max="{{ $eligibility['balances']['available'] }}" required></div>
            <div class="col-md-4"><label class="form-label" for="withdrawalPassword">Mot de passe</label><input id="withdrawalPassword" name="current_password" class="form-control" type="password" autocomplete="current-password" required></div>
            <div class="col-12"><x-ui.button type="submit" loading-text="Envoi du code…"><i class="bi bi-envelope-lock" aria-hidden="true"></i> Recevoir le code e-mail</x-ui.button></div>
        </form>
    @else
        <x-ui.notice variant="info">La demande deviendra disponible lorsque les retraits seront activés et qu’un compte Mobile Money vérifié répondra aux conditions.</x-ui.notice>
    @endif
</x-ui.card>

<x-ui.card title="Prochaine étape" description="Le versement direct sera activé uniquement après validation du nouvel endpoint KPrimePay et une recette staging complète.">
    <div class="d-flex gap-3 align-items-start"><i class="bi bi-info-circle fs-4 text-primary"></i><p class="mb-0">Une demande de retrait exigera un compte vérifié, le seuil minimum, le nombre de clients requis, votre mot de passe et un code de confirmation envoyé par e-mail. Les paiements SMS et WhatsApp restent indépendants de ce flux.</p></div>
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
        const valid = digits.length === 0 || (digits.length === 8 && prefixes.includes(digits.slice(0, 2)));
        phone.setCustomValidity(valid ? '' : 'Le numéro doit contenir 8 chiffres et correspondre à l’opérateur sélectionné.');
        help.textContent = prefixes.length ? `8 chiffres. Préfixes acceptés : ${prefixes.join(', ')}.` : '8 chiffres requis.';
    };
    country.addEventListener('change', sync);
    gateway.addEventListener('change', validatePhone);
    phone.addEventListener('input', validatePhone);
    sync();
});
</script>
@endpush
