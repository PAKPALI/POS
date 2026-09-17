@extends('layouts.saas')
@section('title','Abonnement')
@section('eyebrow','Administration du compte')
@section('page-title','Abonnement')
@push('styles')
<link rel="stylesheet" href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260909-16">
<style>
.saas-duration-label{color:var(--ds-text-secondary);font-size:.82rem;font-weight:700}
.saas-usage-grid,.saas-plan-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.saas-usage-grid>div,.saas-plan-card{padding:18px;border:1px solid var(--ds-border-soft);border-radius:16px;background:var(--ds-glass-1)}.saas-usage-grid span{display:block;color:var(--ds-text-muted);font-size:.76rem}.saas-usage-grid strong{display:block;margin-top:6px;color:var(--ds-text-primary);font-size:1.2rem}.saas-plan-card{display:flex;flex-direction:column;gap:12px}.saas-plan-card.is-active{border-color:var(--ds-accent);box-shadow:0 0 0 1px var(--ds-accent)}.saas-plan-card h3{margin:0;color:var(--ds-text-primary)}.saas-plan-card ul{padding-left:18px;color:var(--ds-text-secondary);font-size:.8rem;line-height:1.8}.saas-plan-price strong{font-size:1.7rem;color:var(--ds-text-primary)}.saas-plan-price span,.saas-plan-annual{color:var(--ds-text-muted);font-size:.75rem}.saas-plan-duration-hint{color:var(--ds-text-muted);font-size:.78rem;line-height:1.5}.saas-modal-duration{display:flex;flex-direction:column;gap:7px;text-align:left;margin:16px 0 12px}.saas-modal-duration label{font-size:.82rem;font-weight:700;color:var(--ds-text-secondary)}.saas-modal-duration select{width:100%;min-height:44px;color:var(--ds-text-primary);background:var(--ds-bg-elevated);border:1px solid var(--ds-border-soft);border-radius:10px;padding:.55rem .7rem}.saas-modal-summary{display:grid;gap:5px;text-align:left;padding:12px;border:1px solid var(--ds-border-soft);border-radius:12px;background:var(--ds-glass-1);font-size:.84rem}.saas-modal-summary strong{color:var(--ds-accent)}.saas-modal-discount{color:var(--ds-success);font-weight:800;font-size:.78rem}@media(max-width:767px){.saas-usage-grid,.saas-plan-grid{grid-template-columns:1fr}}
.saas-months-picker{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;max-height:176px;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;touch-action:pan-y;padding:3px;border:1px solid var(--ds-border-soft);border-radius:12px;background:var(--ds-bg-elevated)}.saas-month-option{min-height:40px;border:1px solid var(--ds-border-soft);border-radius:9px;background:transparent;color:var(--ds-text-secondary);font-weight:700;cursor:pointer}.saas-month-option:hover,.saas-month-option:focus-visible{border-color:var(--ds-accent);color:var(--ds-accent);outline:none}.saas-month-option.is-selected{background:var(--ds-accent);border-color:var(--ds-accent);color:#fff}@media(max-width:767px){.saas-usage-grid,.saas-plan-grid{grid-template-columns:1fr}.saas-months-picker{grid-template-columns:repeat(2,minmax(0,1fr));max-height:184px}}
.saas-plan-feature{display:flex;align-items:center;gap:7px;min-height:36px;padding:8px 10px;border:1px solid rgba(85,214,156,.25);border-radius:10px;background:rgba(85,214,156,.08);color:var(--ds-success);font-size:.78rem;font-weight:800}.saas-plan-feature i{display:inline-block;width:16px;flex:0 0 16px;text-align:center;font-size:.95rem}.saas-plan-feature.is-unavailable{border-color:rgba(255,98,110,.3);background:rgba(255,98,110,.08);color:var(--ds-danger,#ff626e)}
.saas-plan-feature-accordions{display:grid;gap:8px;margin-top:8px}.saas-plan-feature-accordion{border:1px solid rgba(85,214,156,.25);border-radius:10px;background:rgba(85,214,156,.08);color:var(--ds-success)}.saas-plan-feature-accordion summary{display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:36px;padding:8px 10px;cursor:pointer;font-size:.78rem;font-weight:800;list-style:none}.saas-plan-feature-accordion summary::-webkit-details-marker{display:none}.saas-plan-feature-accordion summary span{display:flex;align-items:center;gap:7px}.saas-plan-feature-accordion summary span i{display:inline-block;width:16px;text-align:center;font-size:.95rem}.saas-plan-feature-accordion summary>i{transition:transform .18s ease}.saas-plan-feature-accordion[open] summary>i{transform:rotate(180deg)}.saas-plan-feature-accordion p{margin:0;padding:0 10px 10px;color:var(--ds-text-secondary);font-size:.75rem;font-weight:600;line-height:1.5}.saas-plan-feature-accordions .saas-plan-feature{margin:0}
.saas-payment-terms{display:flex;gap:10px;align-items:flex-start;margin-top:16px;padding:12px;text-align:left;border:1px solid rgba(255,98,110,.45);border-radius:11px;background:rgba(255,98,110,.08);color:var(--ds-text-secondary);font-size:.76rem;line-height:1.45}.saas-payment-terms input{width:18px;height:18px;flex:0 0 auto;margin-top:1px;accent-color:var(--ds-accent)}.saas-payment-terms strong{display:block;color:var(--ds-text-primary);font-size:.78rem;margin-bottom:3px}.saas-payment-terms a{color:var(--ds-accent);font-weight:800;text-decoration:underline}
.saas-plan-feature + .saas-plan-feature{margin-top:8px}
.saas-plan-card .saas-card-head{flex-direction:row;align-items:center;flex-wrap:nowrap}
.saas-subscription-modal{width:min(520px,calc(100vw - 28px))!important;padding:26px 26px 22px!important}.saas-subscription-modal .swal2-title{font-size:1.2rem!important;letter-spacing:-.02em}.saas-subscription-modal .swal2-html-container{margin:12px 0 0!important}.saas-subscription-modal .swal2-actions{width:100%;gap:10px;margin-top:20px}.saas-subscription-modal .swal2-actions button{flex:1;min-height:44px;margin:0!important}.saas-subscription-modal .swal2-confirm{color:#fff!important}.saas-subscription-modal .swal2-html-container>p:first-child{margin:0 0 14px;padding:10px 12px;color:var(--ds-text-primary);background:var(--ds-bg-elevated);border:1px solid var(--ds-border-soft);border-radius:10px;text-align:left;font-size:.84rem}.saas-subscription-modal .swal2-html-container>p:first-child b{font-weight:800}.saas-modal-duration{gap:8px!important;margin:0!important}.partner-promo-field{display:grid;gap:7px;margin-top:16px;padding-top:16px;border-top:1px solid var(--ds-border-soft);text-align:left}.partner-promo-label-row{display:flex;align-items:center;justify-content:space-between;gap:10px}.partner-promo-label{margin:0;color:var(--ds-text-primary);font-size:.8rem;font-weight:800}.partner-promo-optional{padding:3px 8px;color:var(--ds-text-muted);background:var(--ds-glass-1);border:1px solid var(--ds-border-soft);border-radius:999px;font-size:.65rem;font-weight:700}.partner-promo-input-wrap{position:relative;display:flex;align-items:center}.partner-promo-input-icon{position:absolute;left:13px;color:var(--ds-accent);font-size:1rem;pointer-events:none}.partner-promo-input{width:100%;min-height:46px;padding:10px 42px 10px 38px!important;color:var(--ds-text-primary)!important;background:var(--ds-bg-canvas)!important;border:1px solid var(--ds-border-soft)!important;border-radius:11px!important;box-shadow:none!important;font-size:.86rem!important;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.partner-promo-input::placeholder{color:var(--ds-text-muted);font-weight:500;letter-spacing:0;text-transform:none}.partner-promo-input:focus{border-color:var(--ds-accent)!important;box-shadow:0 0 0 3px var(--ds-focus-ring)!important;outline:0}.partner-promo-clear{position:absolute;right:8px;display:grid;place-items:center;width:30px;height:30px;padding:0;color:var(--ds-text-muted);background:transparent;border:0;border-radius:8px;cursor:pointer}.partner-promo-clear:hover,.partner-promo-clear:focus-visible{color:var(--ds-text-primary);background:var(--ds-glass-1);outline:0}.partner-promo-help{margin:0;color:var(--ds-text-muted);font-size:.7rem;line-height:1.45}.partner-promo-status{display:flex;align-items:flex-start;gap:7px;min-height:20px;margin:0;color:var(--ds-text-muted);font-size:.72rem;font-weight:650;line-height:1.45}.partner-promo-status i{flex:0 0 auto;margin-top:2px}.partner-promo-status[data-state=checking] i{animation:partnerPromoSpin 900ms linear infinite}.partner-promo-status[data-state=valid]{color:var(--ds-success,#35c98b)}.partner-promo-status[data-state=invalid],.partner-promo-status[data-state=error]{color:var(--ds-danger,#ff626e)}.partner-promo-status[data-state=info]{color:var(--ds-warning,#f5b942)}.partner-promo-status[data-state=checking],.partner-promo-status[data-state=empty]{color:var(--ds-text-muted)}.saas-modal-summary{gap:8px!important;margin-top:16px;padding:14px!important}.saas-modal-summary>div{display:flex;align-items:baseline;justify-content:space-between;gap:14px}.saas-modal-summary>div:last-child{display:block;margin-top:2px;padding-top:9px;border-top:1px solid var(--ds-border-soft)}.saas-modal-summary strong{white-space:nowrap}.saas-modal-summary>div:nth-child(3){margin-top:2px;padding-top:9px;border-top:1px solid var(--ds-border-soft);font-size:.9rem;font-weight:800}.saas-modal-summary>div:nth-child(3) strong{font-size:1.05rem}.saas-modal-discount{color:var(--ds-success);font-size:.75rem!important;line-height:1.45}@keyframes partnerPromoSpin{to{transform:rotate(360deg)}}@media(prefers-reduced-motion:reduce){.partner-promo-status[data-state=checking] i{animation:none}}@media(max-width:575px){.saas-subscription-modal{padding:22px 18px 18px!important}.saas-subscription-modal .swal2-actions{flex-direction:column-reverse}.saas-subscription-modal .swal2-actions button{width:100%}.saas-modal-summary>div{align-items:flex-start;flex-direction:column;gap:2px}}
</style>
@endpush
@section('content')
@php($snapshot=$current?->snapshot)
<p class="saas-card-description">À 12 mois, Réduction annuelle appliquée (1 mois offert).</p>
<div class="saas-page-header"><div><p class="saas-eyebrow">Facturation et capacités</p><h1>Votre abonnement</h1><p class="saas-card-description">Choisissez votre plan et la durée souhaitée. Le prix et la date d’expiration sont recalculés en temps réel.</p></div></div>
<div class="saas-metric-grid saas-metric-grid-3"><article class="saas-metric"><div class="saas-metric-icon"><i class="bi bi-patch-check"></i></div><span>Plan actuel</span><strong>{{ $snapshot['name'] ?? 'Essai' }}</strong><small>{{ $current && $current->ends_at->isFuture() ? ucfirst($current->status) : 'Expiré' }}</small></article><article class="saas-metric"><div class="saas-metric-icon"><i class="bi bi-calendar2-check"></i></div><span>Validité</span><strong>{{ $current ? max(0,now()->diffInDays($current->ends_at,false)) : 0 }} j</strong><small>{{ $current ? 'jusqu’au '.$current->ends_at->format('d/m/Y') : 'Renouvelez pour continuer' }}</small></article><article class="saas-metric"><div class="saas-metric-icon"><i class="bi bi-chat-square-text"></i></div><span>Crédits disponibles</span><strong>{{ number_format($company->sms_count,0,',',' ') }}</strong><small>SMS · {{ number_format($company->whatsapp_count,0,',',' ') }} WhatsApp</small></article></div>
<section class="saas-card mb-4"><div class="saas-card-head"><div><h2>Utilisation du compte</h2><p class="saas-card-description">Compteurs calculés côté serveur sur les compagnies rattachées.</p></div></div><div class="saas-card-body"><div class="saas-usage-grid"><div><span>Compagnies</span><strong>{{ $usage['companies'] ?? 0 }} / {{ $snapshot['company_limit'] ?? '—' }}</strong></div><div><span>Utilisateurs distincts</span><strong>{{ $usage['users'] ?? 0 }} / {{ $snapshot['user_limit'] ?? '—' }}</strong></div><div><span>Produits actifs</span><strong>{{ $usage['products'] ?? 0 }} / {{ $snapshot['product_limit'] ?? '—' }}</strong></div></div><p class="saas-card-description mb-0 mt-3">Les quotas du prochain paiement seront crédités sur {{ $company->name }}.</p></div></section>
<section class="saas-card mb-4">
    <div class="saas-card-head">
        <div>
            <h2>Choisir un plan et une durée</h2>
            <p class="saas-card-description">De 1 à 11 mois : tarif mensuel × nombre de mois. À 12 mois uniquement : tarif annuel réduit, soit 11 mensualités facturées pour 12 mois d’accès.</p>
        </div>
    </div>
    <div class="saas-plan-grid">
        @foreach($plans as $plan)
            @php($planFeatures = [
                ['key' => 'suppliers', 'label' => 'Fournisseurs', 'description' => 'Enregistrez vos fournisseurs, conservez leurs coordonnées et utilisez-les lors des inventaires pour suivre l’origine de vos produits.'],
                ['key' => 'ecommerce', 'label' => 'E-commerce', 'description' => 'Disposez d’un site e-commerce dédié à votre entreprise : vos produits y sont présentés et vos clients peuvent commander en ligne.'],
                ['key' => 'promo_codes', 'label' => 'Codes promo clients', 'description' => 'Choisissez un pourcentage et une date d’expiration. Chaque client fidèle ou nouveau client qui utilise le code lors d’un achat dans votre entreprise bénéficie de la même réduction. Le code peut être partagé pour faire connaître votre entreprise et développer votre clientèle.'],
            ])
            <article class="saas-plan-card {{ $snapshot && $plan->key === $snapshot['key'] ? 'is-active' : '' }}" data-plan-card data-monthly="{{ $plan->monthly_price }}" data-annual="{{ $plan->annual_price }}" data-plan-name="{{ $plan->name }}" data-plan="{{ $plan->key }}" data-plan-rank="{{ $plan->rank }}" data-current-rank="{{ $snapshot['rank'] ?? -1 }}" data-current-ends="{{ $current?->ends_at?->toIso8601String() }}">
                <div class="saas-card-head mb-0">
                    <div><p class="saas-eyebrow">Niveau {{ $plan->rank }}</p><h3>{{ $plan->name }}</h3></div>
                    @if($plan->key === 'bronze-pro')
                        <span class="saas-status-badge is-active">Recommandé</span>
                    @elseif($snapshot && $plan->key === $snapshot['key'])
                        <span class="saas-status-badge is-active">Plan actif</span>
                    @endif
                </div>
                <div class="saas-plan-price"><strong>{{ number_format($plan->monthly_price, 0, ',', ' ') }}</strong><span>FCFA HT / mois</span></div>
                <p class="saas-plan-annual">{{ number_format($plan->annual_price, 0, ',', ' ') }} FCFA HT pour 12 mois</p>
                <ul>
                    <li>{{ $plan->company_limit }} compagnie(s)</li>
                    <li>{{ $plan->user_limit }} utilisateur(s)</li>
                    <li>{{ number_format($plan->product_limit, 0, ',', ' ') }} produits / compagnie</li>
                    <li>{{ $plan->sms_quota }} SMS · {{ $plan->whatsapp_quota }} WhatsApp / mois</li>
                </ul>
                <div class="saas-plan-feature-accordions">
                    @foreach($planFeatures as $feature)
                        @php($included = (bool) ($plan->features->firstWhere('feature_key', $feature['key'])?->enabled ?? ($feature['key'] === 'promo_codes' && $plan->rank >= 3)))
                        @if($included)
                            <details class="saas-plan-feature-accordion">
                                <summary><span><i class="bi bi-check-circle-fill" aria-hidden="true"></i>{{ $feature['label'] }} inclus</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                <p>{{ $feature['description'] }}</p>
                            </details>
                        @else
                            <div class="saas-plan-feature is-unavailable"><i class="bi bi-x-circle-fill" aria-hidden="true"></i><span>{{ $feature['label'] }} non inclus</span></div>
                        @endif
                    @endforeach
                </div>
                @if($snapshot && $plan->rank < $snapshot['rank'])
                    <button class="saas-btn saas-btn-ghost w-100 mt-auto" disabled>Plan inférieur indisponible</button>
                @elseif($plan->key !== 'trial')
                    <p class="saas-plan-duration-hint mb-0">La durée (1 à 12 mois), le montant et l’expiration seront choisis dans la fenêtre de confirmation.</p>
                    <button class="saas-btn {{ $snapshot && $plan->key === $snapshot['key'] ? 'saas-btn-secondary' : 'saas-btn-primary' }} w-100 mt-auto" data-subscribe data-plan="{{ $plan->key }}">{{ $snapshot && $plan->key === $snapshot['key'] ? 'Renouveler' : 'Choisir la durée' }}</button>
                @endif
            </article>
        @endforeach
    </div>
</section>
<section class="saas-card subscription-history-card">
    <div class="saas-card-head">
        <div>
            <p class="saas-eyebrow">Suivi de facturation</p>
            <h2>Historique des paiements</h2>
            <p class="saas-card-description">Retrouvez les opérations d’abonnement, leur montant et leur statut.</p>
        </div>
        <div class="subscription-history-count"><i class="bi bi-receipt-cutoff" aria-hidden="true"></i><span>{{ $payments->total() }} paiement{{ $payments->total() > 1 ? 's' : '' }}</span></div>
    </div>
    <div class="saas-card-body">
        <div class="table-responsive subscription-history-table-wrap">
            <table class="saas-data-table subscription-history-table">
                <thead><tr><th>Date</th><th>Plan</th><th>Durée</th><th>Montant</th><th>Statut</th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td data-label="Date"><div class="subscription-history-date"><i class="bi bi-calendar3" aria-hidden="true"></i><span>{{ $payment->created_at->format('d/m/Y') }}<small>{{ $payment->created_at->format('H:i') }}</small></span></div></td>
                        <td data-label="Plan"><div class="subscription-history-plan"><strong>{{ $payment->snapshot['name'] ?? '—' }}</strong><small>Abonnement</small></div></td>
                        <td data-label="Durée"><span class="subscription-history-duration"><i class="bi bi-clock" aria-hidden="true"></i>{{ $payment->duration_months ?: ($payment->billing_period==='annual'?12:1) }} mois</span></td>
                        <td data-label="Montant"><strong class="subscription-history-amount">{{ number_format($payment->amount,0,',',' ') }} <small>{{ $payment->currency }}</small></strong></td>
                        <td data-label="Statut"><span class="saas-status-badge is-{{ $payment->status_variant }}"><i class="bi bi-{{ $payment->status_variant === 'success' ? 'check-circle-fill' : ($payment->status_variant === 'pending' ? 'hourglass-split' : ($payment->status_variant === 'danger' ? 'exclamation-circle-fill' : 'info-circle')) }}" aria-hidden="true"></i>{{ $payment->status_label }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="subscription-history-empty"><i class="bi bi-receipt" aria-hidden="true"></i><div><strong>Aucun paiement d’abonnement</strong><span>Vos opérations apparaîtront ici après le lancement d’un paiement.</span></div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="subscription-history-pagination">{{ $payments->links('pagination::bootstrap-5') }}</div>
    </div>
</section>
@endsection
@push('scripts')
@php($partnerPromoMarkup = !$hasPaidSubscription ? view('subscription.partials.partner-promo-field')->render() : '')
<script>
(() => {
    const dateFmt = new Intl.DateTimeFormat('fr-FR');
    const today = new Date();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const partnerPromoMarkup = @json($partnerPromoMarkup);
    const expiry = (months, card) => {
        const currentRank = Number(card.dataset.currentRank);
        const planRank = Number(card.dataset.planRank);
        const base = planRank > currentRank || !card.dataset.currentEnds ? today : new Date(card.dataset.currentEnds);
        const date = new Date(base);
        date.setMonth(date.getMonth() + months);
        return dateFmt.format(date);
    };
    const options = Array.from({ length: 12 }, (_, index) => {
        const months = index + 1;
        return `<button type="button" class="saas-month-option${months === 1 ? ' is-selected' : ''}" data-modal-month="${months}" aria-selected="${months === 1 ? 'true' : 'false'}">${months} mois</button>`;
    }).join('');
    const postJson = (url, payload) => fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(payload),
    }).then(response => response.json().then(data => {
        if (!response.ok || !data.status) throw new Error(data.msg || 'Opération impossible');
        return data;
    }));
    const setPromoStatus = (state, message) => {
        const status = document.querySelector('#partner-promo-status');
        const icon = document.querySelector('#partner-promo-status-icon');
        const text = document.querySelector('#partner-promo-status-text');
        if (!status || !icon || !text) return;
        const icons = { checking: 'bi-arrow-repeat', valid: 'bi-check-circle-fill', invalid: 'bi-x-circle-fill', error: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill', empty: 'bi-ticket-perforated' };
        status.dataset.state = state;
        icon.className = `bi ${icons[state] || icons.empty}`;
        text.textContent = message;
    };
    document.querySelectorAll('[data-plan-card]').forEach(card => {
        const button = card.querySelector('[data-subscribe]');
        if (!button) return;
        button.addEventListener('click', () => {
            let selectedMonths = 1;
            let previewRequest = 0;
            const modalHtml = `<p><b>${card.dataset.planName}</b><span class="text-muted"> · Préparation du paiement</span></p>
                <div class="saas-modal-duration">
                    <span class="saas-duration-label">Durée souhaitée</span>
                    <div class="saas-months-picker" role="listbox" aria-label="Choisir la durée de l’abonnement">${options}</div>
                    ${partnerPromoMarkup}
                </div>
                <div class="saas-modal-summary"><div><span>Montant catalogue</span><strong data-modal-gross></strong></div><div><span>Remise partenaire</span><strong data-modal-discount-amount></strong></div><div><span>Total à payer</span><strong data-modal-total></strong></div><div><span>Expiration estimée</span><strong data-modal-expiry></strong></div><div class="saas-modal-discount" data-modal-discount></div></div>
                <label class="saas-payment-terms" for="subscription-payment-terms"><input id="subscription-payment-terms" type="checkbox"><span><strong>Termes et conditions de paiement</strong>Je comprends que je serai redirigé vers la page de paiement KPrimePay. Les frais éventuels du moyen de paiement sont appliqués par l’opérateur et ne dépendent pas de MAXANOU.</span></label>`;
            const refreshModal = () => {
                const requestId = ++previewRequest;
                const input = document.querySelector('#partner-promo-code');
                const code = input?.value.trim() || null;
                const clear = document.querySelector('[data-promo-clear]');
                if (clear) clear.hidden = !code;
                setPromoStatus(code ? 'checking' : 'empty', code ? 'Vérification du code…' : 'Aucun code saisi');
                postJson(@json(route('subscriptions.preview')), { plan: card.dataset.plan, months: selectedMonths, promo_code: code }).then(data => {
                    if (requestId !== previewRequest) return;
                    const gross = document.querySelector('[data-modal-gross]');
                    const discountAmount = document.querySelector('[data-modal-discount-amount]');
                    const total = document.querySelector('[data-modal-total]');
                    const date = document.querySelector('[data-modal-expiry]');
                    const discount = document.querySelector('[data-modal-discount]');
                    if (gross) gross.textContent = `${Number(data.gross_amount).toLocaleString('fr-FR')} FCFA`;
                    if (discountAmount) discountAmount.textContent = `${Number(data.discount_amount).toLocaleString('fr-FR')} FCFA`;
                    if (total) total.textContent = `${Number(data.net_amount).toLocaleString('fr-FR')} FCFA`;
                    if (date) date.textContent = expiry(selectedMonths, card);
                    if (discount) discount.textContent = data.message || '';
                    if (code) setPromoStatus(data.eligible ? 'valid' : 'info', data.eligible ? 'Code valide — remise activée.' : (data.message || 'Code reconnu, sans remise sur ce compte.'));
                }).catch(error => setPromoStatus('error', error.message));
            };
            Swal.fire({
                title: 'Préparer votre abonnement',
                html: modalHtml,
                icon: 'question',
                customClass: { popup: 'saas-subscription-modal' },
                showCancelButton: true,
                confirmButtonText: 'Continuer vers le paiement',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                didOpen: () => {
                    const terms = document.querySelector('#subscription-payment-terms');
                    const confirmButton = Swal.getConfirmButton();
                    if (confirmButton) confirmButton.disabled = !terms?.checked;
                    terms?.addEventListener('change', () => { if (confirmButton) confirmButton.disabled = !terms.checked; });
                    document.querySelectorAll('[data-modal-month]').forEach(option => option.addEventListener('click', () => {
                        selectedMonths = Number(option.dataset.modalMonth);
                        document.querySelectorAll('[data-modal-month]').forEach(item => {
                            const active = item === option;
                            item.classList.toggle('is-selected', active);
                            item.setAttribute('aria-selected', active ? 'true' : 'false');
                        });
                        refreshModal();
                    }));
                    const input = document.querySelector('#partner-promo-code');
                    input?.addEventListener('input', () => {
                        input.value = input.value.toUpperCase().replace(/\s+/g, '');
                        window.clearTimeout(window.__partnerPromoPreview);
                        window.__partnerPromoPreview = window.setTimeout(refreshModal, 300);
                    });
                    document.querySelector('[data-promo-clear]')?.addEventListener('click', () => {
                        if (!input) return;
                        input.value = '';
                        refreshModal();
                        input.focus();
                    });
                    refreshModal();
                },
                preConfirm: () => {
                    const code = document.querySelector('#partner-promo-code')?.value.trim() || null;
                    if (!document.querySelector('#subscription-payment-terms')?.checked) {
                        Swal.showValidationMessage('Cochez les termes et conditions avant de continuer vers le paiement.');
                        return false;
                    }
                    if (selectedMonths < 1 || selectedMonths > 12) {
                        Swal.showValidationMessage('Choisissez une durée comprise entre 1 et 12 mois.');
                        return false;
                    }
                    return postJson(@json(route('subscriptions.checkout')), { plan: card.dataset.plan, months: selectedMonths, promo_code: code, terms_accepted: true }).then(data => window.location.assign(data.checkout_url)).catch(error => {
                        Swal.showValidationMessage(error.message);
                        return false;
                    });
                },
            });
        });
    });
})();
</script>
@endpush
