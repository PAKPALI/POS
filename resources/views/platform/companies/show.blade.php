@extends('layouts.platform')
@section('title', $company->name)
@section('page-title', $company->name)
@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-4">
    <a href="{{ route('platform.companies.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Entreprises</a>
    <button type="button" class="btn {{ $company->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} company-status-action" data-status="{{ $company->status === 'active' ? 'suspended' : 'active' }}" data-url="{{ route('platform.companies.status', $company) }}" data-loading-text="Traitement…"><i class="bi {{ $company->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>{{ $company->status === 'active' ? 'Suspendre l\'entreprise' : 'Réactiver l\'entreprise' }}</button>
</div>

<section class="platform-summary-grid" aria-label="Indicateurs de l'entreprise">
    @foreach([['Ventes',$stats['sales'],'bi-receipt','accent'],['Chiffre de ventes',number_format($stats['sales_amount'],0,',',' ').' '.$company->currency,'bi-cash-stack','success'],['Commandes',$stats['orders'],'bi-bag-check','violet'],['Produits',$stats['products'],'bi-box-seam','info'],['Inventaires',$stats['inventories'],'bi-clipboard-data','warning'],['Communications',$stats['communications'],'bi-chat-dots','info'],['Paiements quotas',$stats['payments'],'bi-credit-card','accent'],['Membres',$company->memberships->count(),'bi-people','success']] as [$label,$value,$icon,$tone])
    <article class="platform-summary-metric is-{{ $tone }}">
        <span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
        <span>{{ $label }}</span>
        <strong>{{ $value }}</strong>
    </article>
    @endforeach
</section>

<div class="row g-4 mt-1">
    <div class="col-xl-5">
        <div class="platform-card p-4 h-100">
            <header class="platform-panel-head"><div><p class="platform-eyebrow">Identité</p><h2 class="h5 mb-0">Informations</h2></div></header>
            <dl class="row mb-0">
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
        </div>
    </div>
    <div class="col-xl-7">
        <div class="platform-card p-3 h-100">
            <header class="platform-panel-head"><div><p class="platform-eyebrow">Équipe</p><h2 class="h5 mb-0">Membres et rôles</h2></div></header>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
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
        </div>
    </div>
</div>

<div class="platform-card p-3 mt-4">
    <header class="platform-panel-head"><div><p class="platform-eyebrow">Finance</p><h2 class="h5 mb-0">Paiements de quotas récents</h2></div></header>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
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
