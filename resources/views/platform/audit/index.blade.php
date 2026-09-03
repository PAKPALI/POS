@extends('layouts.platform')
@section('title', 'Journal d\'audit')
@section('page-title', 'Journal d\'audit')

@section('content')
<div class="platform-page-stack">
    <section class="platform-card platform-filter-card">
        <header>
            <p class="platform-eyebrow"><i class="bi bi-journal-check" aria-hidden="true"></i> Traçabilité</p>
            <h2 class="h5 mb-0">Filtrer les événements</h2>
        </header>
        <form method="GET" class="platform-filter-grid">
            <div class="platform-filter-field">
                <label class="form-label" for="q">Recherche</label>
                <input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Action, cible ou motif">
            </div>
            <div class="platform-filter-field">
                <label class="form-label" for="admin_id">Administrateur</label>
                <select id="admin_id" name="admin_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected((string)request('admin_id')===(string)$admin->id)>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="platform-filter-field">
                <label class="form-label" for="result">Résultat</label>
                <select id="result" name="result" class="form-select">
                    <option value="">Tous</option>
                    <option value="success" @selected(request('result')==='success')>Réussi</option>
                    <option value="failed" @selected(request('result')==='failed')>Échoué</option>
                </select>
            </div>
            <div class="platform-filter-field">
                <label class="form-label" for="from">Du</label>
                <input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="platform-filter-field">
                <label class="form-label" for="to">Au</label>
                <input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}">
            </div>
            <button class="btn btn-warning platform-filter-submit" data-loading-text="Recherche…"><i class="bi bi-search" aria-hidden="true"></i>Filtrer</button>
        </form>
    </section>

    <section class="platform-card platform-data-panel">
        <header class="platform-panel-head">
            <div>
                <p class="platform-eyebrow">Historique</p>
                <h2>{{ number_format($logs->total(), 0, ',', ' ') }} événement(s)</h2>
                <p>Chaque action d'administration conserve sa date, son auteur et son résultat.</p>
            </div>
            @if(request()->query())
                <a href="{{ route('platform.audit.index') }}" class="platform-panel-link"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>Réinitialiser</a>
            @endif
        </header>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr><th>Date</th><th>Administrateur</th><th>Action</th><th>Cible</th><th>Motif</th><th>Résultat</th><th><span class="visually-hidden">Détails</span></th></tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                        <td>
                            {{ $log->admin?->name ?? 'Compte supprimé' }}
                            <br><small class="text-secondary">{{ $log->ip_address }}</small>
                        </td>
                        <td><code>{{ $log->action }}</code></td>
                        <td><small>{{ class_basename($log->target_type ?: '—') }} #{{ $log->target_id }}</small></td>
                        <td><span title="{{ $log->reason }}">{{ \Illuminate\Support\Str::limit($log->reason ?: '—',45) }}</span></td>
                        <td>
                            <span class="platform-status-chip is-{{ $log->result==='success'?'success':'danger' }}">
                                <i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $log->result==='success'?'Réussi':'Échoué' }}
                            </span>
                        </td>
                        <td>
                            <a class="platform-action-btn" href="{{ route('platform.audit.show',$log) }}" aria-label="Consulter l'événement"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-5">
                            <i class="bi bi-journal-check fs-1 d-block mb-2 opacity-25"></i>
                            Aucun événement.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="platform-pagination">{{ $logs->links() }}</div>
    </section>
</div>
@endsection
