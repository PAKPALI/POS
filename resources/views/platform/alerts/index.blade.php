@extends('layouts.platform')
@section('title', 'Alertes opérationnelles')
@section('page-title', 'Alertes opérationnelles')

@php
    $statusLabels = ['open' => 'Ouverte', 'acknowledged' => 'Prise en charge', 'resolved' => 'Résolue'];
    $statusTones = ['open' => 'danger', 'acknowledged' => 'info', 'resolved' => 'success'];
    $statusIcons = ['open' => 'bi-exclamation-circle', 'acknowledged' => 'bi-hand-thumbs-up', 'resolved' => 'bi-check-circle'];
    $severityLabels = ['critical' => 'Critique', 'warning' => 'Avertissement', 'info' => 'Information'];
    $severityTones = ['critical' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    $severityIcons = ['critical' => 'bi-lightning-charge-fill', 'warning' => 'bi-exclamation-triangle-fill', 'info' => 'bi-info-circle-fill'];
@endphp

@section('content')
<div class="platform-alert-page">
    <header class="platform-system-hero platform-alert-hero">
        <div class="platform-alert-hero-copy">
            <p class="platform-eyebrow"><i class="bi bi-bell-fill" aria-hidden="true"></i> Surveillance SaaS</p>
            <h2>Gardez le contrôle des incidents</h2>
            <p>Suivez les anomalies de la file d’attente, des paiements et des communications, puis coordonnez rapidement leur prise en charge.</p>
        </div>
        <div class="platform-alert-hero-actions">
            <span class="platform-system-chip {{ $settings->enabled ? 'is-success' : 'is-warning' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> Surveillance {{ $settings->enabled ? 'active' : 'en pause' }}</span>
            <form method="POST" action="{{ route('platform.alerts.check') }}">
                @csrf
                <button class="btn btn-warning" data-loading-text="Vérification…"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Vérifier maintenant</button>
            </form>
        </div>
    </header>

    <section class="platform-summary-grid platform-summary-grid-four platform-alert-summary" aria-label="Résumé des alertes">
        @foreach([
            ['Toutes les alertes', $summary['total'], 'bi-bell', 'info'],
            ['Ouvertes', $summary['open'], 'bi-exclamation-circle', 'danger'],
            ['Prises en charge', $summary['acknowledged'], 'bi-hand-thumbs-up', 'warning'],
            ['Résolues', $summary['resolved'], 'bi-check-circle', 'success'],
        ] as [$label, $value, $icon, $tone])
            <article class="platform-summary-metric is-{{ $tone }}">
                <span class="platform-summary-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                <span>{{ $label }}</span>
                <strong>{{ number_format($value, 0, ',', ' ') }}</strong>
            </article>
        @endforeach
    </section>

    @if(auth('platform')->user()->hasPlatformPermission('platform.admins.manage'))
    <section class="platform-alert-settings platform-card platform-filter-card">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow"><i class="bi bi-sliders2" aria-hidden="true"></i> Configuration</p>
                <h2>Paramètres de surveillance</h2>
                <p>Définissez les seuils qui déclenchent une alerte et les administrateurs qui reçoivent les notifications.</p>
            </div>
            <span class="platform-status-chip {{ $settings->enabled ? 'is-success' : 'is-muted' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $settings->enabled ? 'Automatique' : 'Désactivée' }}</span>
        </header>

        <form method="POST" action="{{ route('platform.alerts.settings') }}">
            @csrf @method('PUT')
            <label class="saas-switch-line platform-alert-toggle">
                <input type="checkbox" id="enabled" name="enabled" value="1" class="saas-switch-input" @checked($settings->enabled)>
                <span class="saas-switch-control"></span>
                <span><strong>Activer la surveillance automatique</strong><small>Recevoir un e-mail lorsqu’un seuil est dépassé.</small></span>
            </label>

            <div class="platform-alert-subsection">
                <div class="platform-alert-subsection-head"><strong>Seuils de détection</strong><small>Les valeurs sont contrôlées avant chaque enregistrement.</small></div>
                <div class="platform-alert-threshold-grid">
                    <div class="platform-filter-field"><label class="form-label" for="failed-jobs-threshold">Jobs échoués</label><input id="failed-jobs-threshold" class="form-control" type="number" name="failed_jobs_threshold" min="1" value="{{ $settings->failed_jobs_threshold }}" required><small class="platform-field-help">Nombre minimum de jobs en échec.</small></div>
                    <div class="platform-filter-field"><label class="form-label" for="queue-age-minutes">Ancienneté queue (min)</label><input id="queue-age-minutes" class="form-control" type="number" name="queue_age_minutes" min="1" value="{{ $settings->queue_age_minutes }}" required><small class="platform-field-help">Temps d’attente maximal d’un job.</small></div>
                    <div class="platform-filter-field"><label class="form-label" for="blocked-payment-minutes">Paiement bloqué (min)</label><input id="blocked-payment-minutes" class="form-control" type="number" name="blocked_payment_minutes" min="10" value="{{ $settings->blocked_payment_minutes }}" required><small class="platform-field-help">Délai avant signalement.</small></div>
                    <div class="platform-filter-field"><label class="form-label" for="delivery-failure-percent">Échecs communications (%)</label><input id="delivery-failure-percent" class="form-control" type="number" name="delivery_failure_percent" min="1" max="100" value="{{ $settings->delivery_failure_percent }}" required><small class="platform-field-help">Part minimale d’échecs.</small></div>
                    <div class="platform-filter-field"><label class="form-label" for="delivery-minimum-volume">Volume minimum</label><input id="delivery-minimum-volume" class="form-control" type="number" name="delivery_minimum_volume" min="1" value="{{ $settings->delivery_minimum_volume }}" required><small class="platform-field-help">Messages observés par canal.</small></div>
                    <div class="platform-filter-field"><label class="form-label" for="cooldown-minutes">Anti-spam (min)</label><input id="cooldown-minutes" class="form-control" type="number" name="cooldown_minutes" min="5" value="{{ $settings->cooldown_minutes }}" required><small class="platform-field-help">Délai entre deux notifications.</small></div>
                </div>
            </div>

            <div class="platform-alert-subsection">
                <div class="platform-alert-subsection-head"><strong>Destinataires e-mail</strong><small>Sans sélection, tous les super-administrateurs et techniciens actifs sont alertés.</small></div>
                <div class="platform-alert-recipient-list">
                    @forelse($admins as $admin)
                        <label class="saas-switch-line">
                            <input type="checkbox" name="recipient_admin_ids[]" value="{{ $admin->id }}" class="saas-switch-input" @checked(in_array($admin->id, $settings->recipient_admin_ids ?? []))>
                            <span class="saas-switch-control"></span>
                            <span>{{ $admin->name }} <small>({{ $admin->roleLabel() }})</small></span>
                        </label>
                    @empty
                        <span class="platform-table-muted">Aucun administrateur technique actif.</span>
                    @endforelse
                </div>
            </div>

            <div class="platform-alert-security-grid">
                <div class="platform-filter-field"><label class="form-label" for="alert-reason">Motif de modification</label><input id="alert-reason" class="form-control" name="reason" minlength="5" placeholder="Pourquoi modifiez-vous ces seuils ?" required></div>
                <div class="platform-filter-field"><label class="form-label" for="alert-current-password">Votre mot de passe</label><input id="alert-current-password" class="form-control" type="password" name="current_password" required></div>
                <button class="btn btn-warning platform-alert-submit" data-loading-text="Enregistrement…"><i class="bi bi-check-lg" aria-hidden="true"></i> Enregistrer les paramètres</button>
            </div>
        </form>
    </section>
    @endif

    <section class="platform-alert-data-panel platform-card platform-data-panel">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow"><i class="bi bi-list-check" aria-hidden="true"></i> Journal des incidents</p>
                <h2>Historique des alertes</h2>
                <p>Recherchez, filtrez et parcourez les incidents détectés par la surveillance automatique.</p>
            </div>
            <span class="platform-status-chip is-muted"><i class="bi bi-database" aria-hidden="true"></i> {{ number_format($alerts->total(), 0, ',', ' ') }} résultat(s)</span>
        </header>

        <form method="GET" class="platform-alert-table-toolbar">
            <div class="platform-alert-table-toolbar-copy"><strong>Filtrer le journal</strong><small>La recherche porte sur le titre, le message et le type d’incident.</small></div>
            <div class="platform-alert-table-toolbar-controls">
                <div class="platform-alert-search-field"><label for="alert-search">Rechercher</label><div class="platform-table-search-input"><i class="bi bi-search" aria-hidden="true"></i><input id="alert-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Titre, message, type…"></div></div>
                <div class="platform-alert-filter-field"><label for="alert-status">État</label><select id="alert-status" name="status"><option value="">Tous les états</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="platform-alert-filter-field"><label for="alert-severity">Gravité</label><select id="alert-severity" name="severity"><option value="">Toutes les gravités</option>@foreach($severityLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['severity'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="platform-alert-page-size"><label for="alert-per-page">Lignes</label><select id="alert-per-page" name="per_page"><option value="10" @selected((int) ($filters['per_page'] ?? 20) === 10)>10</option><option value="20" @selected((int) ($filters['per_page'] ?? 20) === 20)>20</option><option value="50" @selected((int) ($filters['per_page'] ?? 20) === 50)>50</option><option value="100" @selected((int) ($filters['per_page'] ?? 20) === 100)>100</option></select></div>
                <button class="btn btn-warning platform-alert-search-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i> Rechercher</button>
                @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['severity']))<a class="platform-table-clear-search" href="{{ route('platform.alerts.index', request()->except(['search', 'status', 'severity', 'per_page', 'page'])) }}">Effacer</a>@endif
            </div>
        </form>

        <div class="platform-datatable">
            <div class="platform-datatable-meta"><span>Résultats filtrés</span><small>Affichage de {{ $alerts->firstItem() ?? 0 }} à {{ $alerts->lastItem() ?? 0 }} sur {{ $alerts->total() }}</small></div>
            <div class="table-responsive platform-table-scroll">
                <table class="table platform-data-table platform-alerts-table">
                    <thead><tr><th>Incident</th><th>Gravité</th><th>État</th><th>Dernière détection</th><th>Notification</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($alerts as $alert)
                        <tr>
                            <td><div class="platform-alert-incident"><span class="platform-alert-incident-icon is-{{ $severityTones[$alert->severity] ?? 'info' }}"><i class="bi {{ $severityIcons[$alert->severity] ?? 'bi-bell' }}" aria-hidden="true"></i></span><span><strong>{{ $alert->title }}</strong><small class="platform-table-subtext">{{ str_replace('.', ' · ', $alert->type) }}<br>{{ \Illuminate\Support\Str::limit($alert->message, 120) }}</small></span></div></td>
                            <td><span class="platform-status-chip is-{{ $severityTones[$alert->severity] ?? 'info' }}"><i class="bi {{ $severityIcons[$alert->severity] ?? 'bi-info-circle' }}" aria-hidden="true"></i> {{ $severityLabels[$alert->severity] ?? ucfirst($alert->severity) }}</span></td>
                            <td><span class="platform-status-chip is-{{ $statusTones[$alert->status] ?? 'muted' }}"><i class="bi {{ $statusIcons[$alert->status] ?? 'bi-circle' }}" aria-hidden="true"></i> {{ $statusLabels[$alert->status] ?? ucfirst($alert->status) }}</span></td>
                            <td><span class="platform-table-date">{{ $alert->last_detected_at->format('d/m/Y') }}</span><small class="platform-table-subtext">{{ $alert->last_detected_at->format('H:i') }}</small></td>
                            <td>@if($alert->last_notified_at)<span class="platform-table-date">{{ $alert->last_notified_at->format('d/m/Y') }}</span><small class="platform-table-subtext">{{ $alert->last_notified_at->format('H:i') }}</small>@else<span class="platform-table-muted">Non envoyée</span>@endif</td>
                            <td><div class="platform-action-group">@if($alert->status === 'open')<form method="POST" action="{{ route('platform.alerts.acknowledge', $alert) }}">@csrf<button class="platform-action-btn" data-loading-text="…" title="Prendre en charge" aria-label="Prendre en charge"><i class="bi bi-hand-thumbs-up" aria-hidden="true"></i></button></form>@endif @if($alert->status !== 'resolved')<button class="platform-action-btn btn-success resolve-alert" data-url="{{ route('platform.alerts.resolve', $alert) }}" title="Résoudre" aria-label="Résoudre"><i class="bi bi-check-lg" aria-hidden="true"></i></button>@endif @if($alert->status === 'resolved')<span class="platform-table-muted">Clôturée</span>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="platform-table-empty"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Aucune alerte ne correspond à ces filtres.</span></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="platform-pagination">{{ $alerts->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.resolve-alert').forEach(button => button.addEventListener('click', async () => {
    const result = await Swal.fire({
        title: 'Marquer cette alerte comme résolue ?',
        input: 'text',
        inputPlaceholder: 'Décrivez la résolution',
        showCancelButton: true,
        confirmButtonText: 'Résoudre',
        cancelButtonText: 'Annuler',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        inputValidator: value => !value || value.trim().length < 5 ? 'Motif de 5 caractères minimum.' : null,
        preConfirm: async reason => {
            const response = await fetch(button.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ reason }) });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) return Swal.showValidationMessage(data.message || 'Opération impossible.');
            return data;
        }
    });
    if (result?.isConfirmed) window.location.reload();
}));
</script>
@endpush
