@extends('layouts.partner')

@section('title', 'Mes clients attribués')
@section('page-title', 'Mes clients')

@section('content')
@php($xof = fn ($amount) => number_format((int) $amount, 0, ',', ' ').' XOF')
<x-ui.page-header title="Mes clients attribués" eyebrow="Acquisition" icon="bi-people" description="Cette liste est limitée aux informations utiles au suivi commercial. Les e-mails et numéros de téléphone ne sont jamais affichés.">
    <x-slot:actions><x-ui.button :href="route('partner.code')" variant="secondary"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Partager mon code</x-ui.button></x-slot:actions>
</x-ui.page-header>

<x-ui.filter-panel title="Filtrer les clients" open>
    <form method="GET" class="partner-filter-grid">
        <x-ui.input id="clientQuery" name="q" label="Rechercher une entreprise" :value="$filters['q'] ?? ''" placeholder="Nom de l’entreprise" />
        <x-ui.select id="clientStatus" name="status" label="Statut d’attribution"><option value="">Tous les statuts</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="reversed" @selected(($filters['status'] ?? '') === 'reversed')>Annulée</option><option value="fraudulent" @selected(($filters['status'] ?? '') === 'fraudulent')>En revue</option></x-ui.select>
        <div class="partner-filter-actions"><x-ui.button type="submit" loading-text="Filtrage…"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrer</x-ui.button><x-ui.button :href="route('partner.clients')" variant="ghost">Réinitialiser</x-ui.button></div>
    </form>
</x-ui.filter-panel>

<x-ui.table-shell class="partner-list-table">
    <x-slot:toolbar><span class="partner-table-caption"><i class="bi bi-shield-lock" aria-hidden="true"></i> {{ $clients->total() }} client(s) attribué(s) — données personnelles masquées</span></x-slot:toolbar>
    <x-slot:table>
        <thead><tr><th>Entreprise</th><th>Pays</th><th>Attribution</th><th>Plan actuel</th><th>Dernière commission</th><th class="is-numeric">Commissions cumulées</th></tr></thead>
        <tbody>@forelse($clients as $client)
            @php($company = $client->subscriptionAccount?->billingCompany)
            @php($subscription = $client->subscriptionAccount?->latestSubscription)
            <tr><td><strong>{{ $company?->name ?? 'Entreprise non disponible' }}</strong><small class="partner-table-muted">Client #{{ $client->acquisition_rank }}</small></td><td>{{ $company?->country_code ?: '—' }}</td><td><x-ui.status :variant="$client->status === 'active' ? 'success' : 'neutral'">{{ $client->status === 'active' ? 'Active' : ucfirst($client->status) }}</x-ui.status><small class="partner-table-muted">{{ optional($client->attributed_at)->format('d/m/Y') }}</small></td><td>{{ $subscription?->plan?->name ?? 'Aucun abonnement' }}<small class="partner-table-muted">{{ $subscription?->status === 'active' ? 'Actif' : ($subscription?->status ? ucfirst($subscription->status) : '—') }}</small></td><td>{{ $client->last_commission_at ? \Carbon\Carbon::parse($client->last_commission_at)->format('d/m/Y') : '—' }}</td><td class="is-numeric"><strong>{{ $xof($client->commissions_total ?? 0) }}</strong></td></tr>
        @empty
            <tr><td colspan="6"><x-ui.empty-state icon="bi-people" title="Aucun client trouvé" description="Aucun client attribué ne correspond aux filtres sélectionnés." /></td></tr>
        @endforelse</tbody>
    </x-slot:table>
    <x-slot:footer><span>Page {{ $clients->currentPage() }} sur {{ $clients->lastPage() }}</span>{{ $clients->links() }}</x-slot:footer>
</x-ui.table-shell>
@endsection
