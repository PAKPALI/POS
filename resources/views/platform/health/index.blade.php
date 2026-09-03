@extends('layouts.platform')
@section('title', 'Santé du système')
@section('page-title', 'Santé du système')

@section('content')
@php($schedulerLabels=['healthy'=>['Opérationnel','success'],'warning'=>['En retard','warning'],'critical'=>['Critique','danger'],'unknown'=>['Non observé','accent']])

<div class="platform-page-stack">
    <section class="platform-system-hero" aria-labelledby="health-hero-title">
        <div>
            <p class="platform-eyebrow"><i class="bi bi-activity" aria-hidden="true"></i> Supervision</p>
            <h2 id="health-hero-title">État opérationnel de la plateforme</h2>
            <p>Surveillez les tâches planifiées, les files et les fournisseurs depuis une seule vue.</p>
        </div>
        <span class="platform-system-chip is-{{ $schedulerLabels[$schedulerStatus][1] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $schedulerLabels[$schedulerStatus][0] }}</span>
    </section>

    <section class="platform-summary-grid platform-summary-grid-four" aria-label="Indicateurs système">
        <article class="platform-summary-metric is-{{ $schedulerLabels[$schedulerStatus][1] }}">
            <span class="platform-summary-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
            <span>Cron Laravel</span>
            <strong>{{ $heartbeat ? $heartbeatAge.' min' : '—' }}</strong>
        </article>
        <article class="platform-summary-metric is-{{ $queue['pending']>100?'warning':'accent' }}">
            <span class="platform-summary-icon"><i class="bi bi-list-task" aria-hidden="true"></i></span>
            <span>Jobs en attente</span>
            <strong>{{ number_format($queue['pending']) }}</strong>
        </article>
        <article class="platform-summary-metric is-{{ $queue['failed']>0?'danger':'success' }}">
            <span class="platform-summary-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
            <span>Jobs échoués</span>
            <strong>{{ number_format($queue['failed']) }}</strong>
        </article>
        <article class="platform-summary-metric is-{{ $blockedPayments>0?'warning':'success' }}">
            <span class="platform-summary-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span>
            <span>Paiements en attente depuis plus de 2 h</span>
            <div class="metric-value">{{ number_format($blockedPayments) }}</div>
        </article>
    </section>

    <section class="platform-card platform-data-panel">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow">Communications</p>
                <h2>Délivrabilité sur les 7 derniers jours</h2>
                <p>Suivi des destinataires configurés pour les ventes et inventaires.</p>
            </div>
        </header>
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr><th>Canal</th><th>En attente</th><th>En traitement</th><th>Envoyés</th><th>Échoués</th><th>Total</th></tr>
                </thead>
                <tbody>
                @forelse($deliveryStats->groupBy('channel') as $channel=>$rows)
                    @php($counts=$rows->pluck('total','status'))
                    <tr>
                        <td class="text-uppercase fw-semibold">{{ strtoupper($channel) }}</td>
                        <td>{{ $counts['pending']??0 }}</td>
                        <td>{{ $counts['processing']??0 }}</td>
                        <td><span class="platform-status-chip is-success"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $counts['sent']??0 }}</span></td>
                        <td><span class="platform-status-chip is-danger"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $counts['failed']??0 }}</span></td>
                        <td>{{ $rows->sum('total') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 opacity-25"></i>
                            Aucune livraison enregistrée sur cette période.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="platform-card platform-data-panel">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow">File d'attente</p>
                <h2>Jobs échoués récents</h2>
                <p>La relance remet le job dans la file ; elle ne garantit pas sa réussite.</p>
            </div>
        </header>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr><th>Date</th><th>File</th><th>Connexion</th><th>Identifiant</th><th><span class="visually-hidden">Action</span></th></tr>
                </thead>
                <tbody>
                @forelse($failedJobs as $job)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($job->failed_at)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $job->queue }}</td>
                        <td>{{ $job->connection }}</td>
                        <td><small>{{ $job->uuid }}</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning retry-job" data-url="{{ route('platform.health.jobs.retry',$job->uuid) }}">
                                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Relancer
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 opacity-25"></i>
                            Aucun job échoué.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.retry-job').forEach(button=>button.addEventListener('click',()=>Swal.fire({title:'Relancer ce job ?',text:'Vérifiez d\'abord que la cause de l\'échec est corrigée.',icon:'warning',input:'textarea',inputLabel:'Motif obligatoire',showCancelButton:true,confirmButtonText:'Oui, relancer',cancelButtonText:'Annuler',showLoaderOnConfirm:true,allowOutsideClick:()=>!Swal.isLoading(),preConfirm:async reason=>{if(!reason||reason.trim().length<5)return Swal.showValidationMessage('Indiquez un motif d\'au moins 5 caractères.');const r=await fetch(button.dataset.url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({reason:reason.trim()})});const d=await r.json().catch(()=>({}));if(!r.ok)return Swal.showValidationMessage(d.message||'Relance impossible.');return d}}).then(result=>{if(result.isConfirmed)Swal.fire({icon:'success',title:'Job relancé',text:result.value.message}).then(()=>location.reload())})));
</script>
@endpush
