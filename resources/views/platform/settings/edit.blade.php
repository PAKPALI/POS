@extends('layouts.platform')
@section('title', 'Paramètres plateforme')
@section('page-title', 'Paramètres plateforme')
@section('content')
<div class="platform-settings-page">
    <nav class="platform-settings-nav" aria-label="Paramètres plateforme">
        <a class="platform-settings-tab" href="{{ route('platform.settings.general') }}"><i class="bi bi-sliders2" aria-hidden="true"></i> Général</a>
        <a class="platform-settings-tab active" href="{{ route('platform.settings.edit') }}" aria-current="page"><i class="bi bi-tags" aria-hidden="true"></i> Tarifs et coûts</a>
        <a class="platform-settings-tab" href="{{ route('platform.subscriptions.preflight') }}"><i class="bi bi-check2-circle" aria-hidden="true"></i> Pré-contrôle abonnements</a>
    </nav>

    <header class="platform-settings-intro">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-tags" aria-hidden="true"></i> Tarification</p>
            <h2>Maîtrisez les tarifs et les coûts de communication</h2>
            <p>Définissez les prix appliqués aux nouveaux checkouts et visualisez immédiatement la marge estimée.</p>
        </div>
        <span class="platform-settings-intro-badge"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Marge contrôlée</span>
    </header>

    <div class="platform-settings-layout">
        <section class="platform-settings-section">
            <header class="platform-settings-section-head">
                <p class="platform-eyebrow"><i class="bi bi-calculator" aria-hidden="true"></i> Paramètres de facturation</p>
                <h2>Tarifs et coûts des communications</h2>
                <p>Les changements s’appliquent uniquement aux nouveaux checkouts. Les consommations déjà enregistrées restent inchangées.</p>
            </header>
            <form id="pricingForm" method="POST" action="{{ route('platform.settings.pricing.update') }}">
                @csrf @method('PUT')
                <div class="platform-settings-form-grid">
                    <div class="platform-settings-field"><label for="sms_unit_price" class="form-label">Prix de vente SMS <span>(par unité)</span></label><div class="input-group"><input id="sms_unit_price" name="sms_unit_price" type="number" min="1" max="10000" class="form-control" value="{{ old('sms_unit_price',$smsUnitPrice) }}" required><span class="input-group-text">XOF</span></div></div>
                    <div class="platform-settings-field"><label for="whatsapp_unit_price" class="form-label">Prix de vente WhatsApp <span>(par unité)</span></label><div class="input-group"><input id="whatsapp_unit_price" name="whatsapp_unit_price" type="number" min="1" max="10000" class="form-control" value="{{ old('whatsapp_unit_price',$whatsappUnitPrice) }}" required><span class="input-group-text">XOF</span></div></div>
                    <div class="platform-settings-field"><label for="sms_unit_cost" class="form-label">Coût fournisseur SMS <span>(par unité)</span></label><div class="input-group"><input id="sms_unit_cost" name="sms_unit_cost" type="number" min="0" max="10000" class="form-control" value="{{ old('sms_unit_cost',$smsUnitCost) }}" required><span class="input-group-text">XOF</span></div></div>
                    <div class="platform-settings-field"><label for="whatsapp_unit_cost" class="form-label">Coût fournisseur WhatsApp <span>(par unité)</span></label><div class="input-group"><input id="whatsapp_unit_cost" name="whatsapp_unit_cost" type="number" min="0" max="10000" class="form-control" value="{{ old('whatsapp_unit_cost',$whatsappUnitCost) }}" required><span class="input-group-text">XOF</span></div></div>
                </div>
                <div class="platform-settings-field platform-settings-field-spaced"><label for="reason" class="form-label">Raison de la modification</label><textarea id="reason" name="reason" class="form-control" minlength="5" maxlength="500" rows="3" required>{{ old('reason') }}</textarea></div>
                <div class="platform-settings-field platform-settings-field-spaced"><label for="current_password" class="form-label">Mot de passe plateforme</label><div class="input-group"><input id="current_password" name="current_password" type="password" class="form-control" required><button type="button" class="btn btn-outline-secondary password-toggle" data-target="current_password" aria-label="Afficher le mot de passe"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                <div class="platform-settings-preview" aria-live="polite">
                    <div class="platform-settings-preview-head"><span><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Aperçu pour 100 unités</span><small>Estimation instantanée</small></div>
                    <div class="platform-settings-preview-grid">
                        <div><strong>SMS</strong><span>CA <b id="smsPreview">{{ number_format($smsUnitPrice*100,0,',',' ') }}</b> XOF</span><small>Bénéfice <b id="smsProfitPreview">{{ number_format(($smsUnitPrice-$smsUnitCost)*100,0,',',' ') }}</b> XOF</small></div>
                        <div><strong>WhatsApp</strong><span>CA <b id="whatsappPreview">{{ number_format($whatsappUnitPrice*100,0,',',' ') }}</b> XOF</span><small>Bénéfice <b id="whatsappProfitPreview">{{ number_format(($whatsappUnitPrice-$whatsappUnitCost)*100,0,',',' ') }}</b> XOF</small></div>
                    </div>
                </div>
                <div class="platform-settings-action"><button class="btn btn-warning" data-loading-text="Enregistrement…"><i class="bi bi-save2" aria-hidden="true"></i> Enregistrer les tarifs et coûts</button></div>
            </form>
        </section>
    </div>

    <section class="platform-settings-section platform-settings-history platform-settings-history-panel">
            <header class="platform-settings-section-head">
                <p class="platform-eyebrow"><i class="bi bi-clock-history" aria-hidden="true"></i> Traçabilité</p>
                <h2>Historique des tarifs</h2>
                <p>Retrouvez les évolutions des prix et des coûts enregistrées dans la console.</p>
            </header>
            <form method="GET" class="platform-settings-history-toolbar">
                <div class="platform-settings-history-toolbar-copy"><strong>Journal des modifications tarifaires</strong><small>Recherche par canal, valeur, administrateur ou motif.</small></div>
                <div class="platform-settings-history-toolbar-controls">
                    <div class="platform-settings-history-search"><label for="pricing-history-search">Rechercher</label><div class="platform-table-search-input"><i class="bi bi-search" aria-hidden="true"></i><input id="pricing-history-search" type="search" name="history_search" value="{{ $filters['history_search'] ?? '' }}" placeholder="Canal, valeur, motif…"></div></div>
                    <div class="platform-settings-history-page-size"><label for="pricing-history-per-page">Lignes</label><select id="pricing-history-per-page" name="history_per_page"><option value="10" @selected((int) ($filters['history_per_page'] ?? 20) === 10)>10</option><option value="20" @selected((int) ($filters['history_per_page'] ?? 20) === 20)>20</option><option value="50" @selected((int) ($filters['history_per_page'] ?? 20) === 50)>50</option><option value="100" @selected((int) ($filters['history_per_page'] ?? 20) === 100)>100</option></select></div>
                    <button class="btn btn-warning platform-settings-history-search-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Rechercher</button>
                    @if(!empty($filters['history_search']))<a class="platform-table-clear-search" href="{{ route('platform.settings.edit') }}">Effacer</a>@endif
                </div>
            </form>
            @php($labels=[\App\Services\PlatformPricingService::SMS_KEY=>'Vente SMS',\App\Services\PlatformPricingService::WHATSAPP_KEY=>'Vente WhatsApp',\App\Services\PlatformPricingService::SMS_COST_KEY=>'Coût SMS',\App\Services\PlatformPricingService::WHATSAPP_COST_KEY=>'Coût WhatsApp'])
            <div class="platform-datatable"><div class="platform-datatable-meta"><span>Résultats filtrés</span><small>Affichage de {{ $history->firstItem() ?? 0 }} à {{ $history->lastItem() ?? 0 }} sur {{ $history->total() }}</small></div><div class="table-responsive platform-table-scroll">
                <table class="table platform-data-table platform-settings-history-table">
                    <thead><tr><th>Paramètre</th><th>Ancienne valeur</th><th>Nouvelle valeur</th><th>Administrateur</th><th>Motif</th></tr></thead>
                    <tbody>
                    @forelse($history as $entry)
                        <tr><td><strong>{{ $labels[$entry->key] ?? $entry->key }}</strong><small class="platform-table-subtext">{{ $entry->key }}</small></td><td><span class="platform-table-code">{{ $entry->old_value ?: '—' }} XOF</span></td><td><span class="platform-table-code">{{ $entry->new_value ?: '—' }} XOF</span></td><td><strong>{{ $entry->admin?->name ?? 'Administrateur supprimé' }}</strong></td><td><span class="platform-table-reason">{{ Str::limit($entry->reason, 100) }}</span></td></tr>
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
@push('scripts')
<script>
const pf=new Intl.NumberFormat('fr-FR'),pv=id=>parseInt(document.getElementById(id).value,10)||0,refresh=()=>{smsPreview.textContent=pf.format(pv('sms_unit_price')*100);whatsappPreview.textContent=pf.format(pv('whatsapp_unit_price')*100);smsProfitPreview.textContent=pf.format((pv('sms_unit_price')-pv('sms_unit_cost'))*100);whatsappProfitPreview.textContent=pf.format((pv('whatsapp_unit_price')-pv('whatsapp_unit_cost'))*100)};
['sms_unit_price','whatsapp_unit_price','sms_unit_cost','whatsapp_unit_cost'].forEach(id=>document.getElementById(id).addEventListener('input',refresh));
document.querySelector('.password-toggle').addEventListener('click',function(){const input=document.getElementById(this.dataset.target),show=input.type==='password';input.type=show?'text':'password';this.querySelector('i').className=show?'bi bi-eye-slash':'bi bi-eye'});
pricingForm.addEventListener('submit',function(e){e.preventDefault();const form=this;if(!form.reportValidity())return;Swal.fire({title:'Confirmer les valeurs ?',html:'SMS : vente <b>'+pv('sms_unit_price')+'</b>, coût '+pv('sms_unit_cost')+' XOF<br>WhatsApp : vente <b>'+pv('whatsapp_unit_price')+'</b>, coût '+pv('whatsapp_unit_cost')+' XOF',icon:'warning',showCancelButton:true,confirmButtonText:'Oui, enregistrer',cancelButtonText:'Annuler',showLoaderOnConfirm:true,allowOutsideClick:()=>!Swal.isLoading(),preConfirm:async()=>{const r=await fetch(form.action,{method:'POST',headers:{Accept:'application/json'},body:new FormData(form)});if(!r.ok){const d=await r.json().catch(()=>({}));return Swal.showValidationMessage(d.errors?Object.values(d.errors).flat().join(' '):(d.message||'Erreur'));}return true}}).then(r=>{if(r.isConfirmed)location.reload()})});
</script>
@endpush
