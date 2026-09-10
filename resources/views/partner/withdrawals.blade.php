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
                    <div class="col-md-6"><label class="form-label" for="withdrawalPhone">Numéro</label><input id="withdrawalPhone" name="phone_number" class="form-control" inputmode="tel" maxlength="24" placeholder="Ex. 90 00 00 00" required></div>
                    <div class="col-md-6"><label class="form-label" for="withdrawalBeneficiary">Nom du bénéficiaire</label><input id="withdrawalBeneficiary" name="beneficiary_name" class="form-control" maxlength="120" required></div>
                </div>
                <div class="mt-3"><x-ui.button type="submit" loading-text="Enregistrement…"><i class="bi bi-shield-check" aria-hidden="true"></i> Enregistrer le compte</x-ui.button></div>
            </form>
        </details>
    </x-ui.card>
</div>

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
    const labels = @json($gatewayLabels);
    const sync = () => {
        const values = gateways[country.value] || [];
        gateway.replaceChildren(...values.map(value => new Option(labels[value] || value, value)));
        gateway.disabled = values.length === 0;
    };
    country.addEventListener('change', sync);
    sync();
});
</script>
@endpush
