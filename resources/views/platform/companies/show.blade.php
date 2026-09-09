@extends('layouts.platform')
@section('title', $company->name)
@section('page-title', 'Détails entreprise')
@section('content')
<div class="platform-company-page">
<header class="platform-company-hero">
    <div class="platform-company-hero-copy">
        <a href="{{ route('platform.companies.index') }}" class="platform-company-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour aux entreprises</a>
        <div class="platform-company-title-row">
            <span class="platform-company-mark" aria-hidden="true"><i class="bi bi-building"></i></span>
            <div>
                <p class="platform-eyebrow">Administration / Console SaaS</p>
                <h1>{{ $company->name }}</h1>
                <p>Vue opérationnelle de l’entreprise, de son équipe et de ses consommations.</p>
            </div>
        </div>
    </div>
    <div class="platform-company-hero-actions">
        <span class="platform-status-chip is-{{ $company->status === 'active' ? 'success' : 'danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $company->status === 'active' ? 'Entreprise active' : 'Entreprise suspendue' }}</span>
        <button type="button" class="btn {{ $company->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} company-status-action" data-status="{{ $company->status === 'active' ? 'suspended' : 'active' }}" data-url="{{ route('platform.companies.status', $company) }}" data-loading-text="Traitement…"><i class="bi {{ $company->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>{{ $company->status === 'active' ? 'Suspendre l\'entreprise' : 'Réactiver l\'entreprise' }}</button>
    </div>
</header>

<section class="platform-company-summary-grid" aria-label="Indicateurs de l'entreprise">
    @foreach([['Ventes',$stats['sales'],'bi-receipt','accent'],['Chiffre de ventes',number_format($stats['sales_amount'],0,',',' ').' '.$company->currency,'bi-cash-stack','success'],['Commandes',$stats['orders'],'bi-bag-check','violet'],['Produits',$stats['products'],'bi-box-seam','info'],['Inventaires',$stats['inventories'],'bi-clipboard-data','warning'],['Communications',$stats['communications'],'bi-chat-dots','info'],['Paiements quotas',$stats['payments'],'bi-credit-card','accent'],['Membres',$company->memberships->count(),'bi-people','success']] as [$label,$value,$icon,$tone])
    <article class="platform-summary-metric is-{{ $tone }}">
        <span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
        <span>{{ $label }}</span>
        <strong>{{ $value }}</strong>
    </article>
    @endforeach
</section>

<div class="platform-company-content-grid">
    <section class="platform-card platform-company-panel" aria-labelledby="company-identity-title">
            <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-fingerprint" aria-hidden="true"></i> Identité</p><h2 id="company-identity-title">Informations de l’entreprise</h2><p>Références et paramètres principaux.</p></div></header>
            <dl class="platform-company-details mb-0">
                <dt class="col-5">Statut</dt>
                <dd class="col-7"><span class="platform-status-chip is-{{ $company->status === 'active' ? 'success' : 'danger' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $company->status }}</span></dd>
                <dt class="col-5">E-mail</dt>
                <dd class="col-7">{{ $company->email }}</dd>
                <dt class="col-5">Téléphone</dt>
                <dd class="col-7">{{ $company->number1 ?: 'Non renseigné' }}</dd>
                <dt class="col-5">Slug</dt>
                <dd class="col-7"><code>{{ $company->slug }}</code></dd>
                <dt class="col-5">Identifiant</dt>
                <dd class="col-7 text-break"><small>{{ $company->public_id }}</small></dd>
                <dt class="col-5">Devise</dt>
                <dd class="col-7">{{ $company->currency }}</dd>
                <dt class="col-5">Quotas</dt>
                <dd class="col-7">{{ number_format($company->sms_count) }} SMS / {{ number_format($company->whatsapp_count) }} WhatsApp</dd>
                <dt class="col-5">Création</dt>
                <dd class="col-7">{{ $company->created_at?->format('d/m/Y H:i') }}</dd>
            </dl>
    </section>
    <section class="platform-card platform-company-panel" aria-labelledby="company-team-title">
            <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-people" aria-hidden="true"></i> Équipe</p><h2 id="company-team-title">Membres et rôles</h2><p>{{ $company->memberships->count() }} membre(s) rattaché(s) à cette entreprise.</p></div></header>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 platform-company-table">
                    <thead><tr><th>Utilisateur</th><th>Rôle</th><th>Statut</th><th>Dernier accès</th></tr></thead>
                    <tbody>
                    @forelse($company->memberships as $membership)
                        <tr>
                            <td>
                                <a href="{{ route('platform.users.show', $membership->user) }}" class="text-decoration-none">{{ $membership->user?->name }}</a>
                                <br><small class="text-secondary">{{ $membership->user?->email }}</small>
                            </td>
                            <td>{{ $membership->role?->name ?? 'Non attribué' }}</td>
                            <td><span class="platform-status-chip is-{{ $membership->status === 'active' ? 'success' : 'muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $membership->status }}</span></td>
                            <td>{{ $membership->last_accessed_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">Aucun membre.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
    </section>
</div>

<section class="platform-card platform-company-panel platform-company-finance" aria-labelledby="company-finance-title">
    <header class="platform-panel-head"><div><p class="platform-eyebrow"><i class="bi bi-credit-card" aria-hidden="true"></i> Finance</p><h2 id="company-finance-title">Paiements de quotas récents</h2><p>Suivi des achats SMS et WhatsApp associés à cette entreprise.</p></div></header>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0 platform-company-table platform-company-payments-table">
            <thead><tr><th>Transaction</th><th>SMS</th><th>WhatsApp</th><th>Montant</th><th>Statut</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td><small>{{ $payment->transaction_id }}</small></td>
                    <td>{{ $payment->sms_quantity }}</td>
                    <td>{{ $payment->whatsapp_quantity }}</td>
                    <td>{{ number_format($payment->amount,0,',',' ') }} {{ $payment->currency }}</td>
                    <td><span class="platform-status-chip is-{{ $payment->status==='paid'?'success':(in_array($payment->status,['failed','expired'])?'danger':'warning') }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $payment->status }}</span></td>
                    <td>{{ $payment->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">Aucun paiement.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.company-status-action').forEach(function (button) {
    button.addEventListener('click', function () {
        const suspending = button.dataset.status === 'suspended';
        Swal.fire({
            title: suspending ? 'Suspendre cette entreprise ?' : 'Réactiver cette entreprise ?',
            text: suspending ? 'Ses membres perdront immédiatement l\'accès à ses données.' : 'Ses membres pourront de nouveau y accéder.',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motif obligatoire',
            inputPlaceholder: 'Expliquez la raison de cette opération…',
            showCancelButton: true,
            confirmButtonText: suspending ? 'Oui, suspendre' : 'Oui, réactiver',
            cancelButtonText: 'Annuler',
            buttonsStyling: false,
            customClass: { confirmButton: suspending ? 'saas-btn saas-btn-danger' : 'saas-btn saas-btn-success', cancelButton: 'saas-btn saas-btn-ghost' },
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            allowEscapeKey: () => !Swal.isLoading(),
            preConfirm: async (reason) => {
                if (!reason || reason.trim().length < 5) return Swal.showValidationMessage('Indiquez un motif d\'au moins 5 caractères.');
                const response = await fetch(button.dataset.url, {method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({_method:'PATCH',status:button.dataset.status,reason:reason.trim()})});
                if (!response.ok) { const data = await response.json().catch(() => ({})); return Swal.showValidationMessage(data.message || 'L\'opération n\'a pas pu être effectuée.'); }
                return true;
            }
        }).then(result => { if (result.isConfirmed) window.location.reload(); });
    });
});
</script>
@endpush
