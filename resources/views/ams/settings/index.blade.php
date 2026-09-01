@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Paramètres comptabilité</h1>
            <p>Configurez les caisses par défaut et le taux de taxe applicable.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>Configuration</h2>
                        <p class="saas-card-description">Ces réglages impactent le calcul de la taxe et l'affectation des écritures automatiques.</p>
                    </div>
                </div>

                <form id="settingsForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 saas-form-group">
                            <label>Caisse par défaut</label>
                            <select name="default_cash_id" class="form-select" required>
                                <option value="">Choisir une caisse</option>
                                @foreach($cashes as $cash)
                                    <option value="{{ $cash->id }}"
                                        @if(isset($setting) && $setting->default_cash_id == $cash->id) selected @endif>
                                        {{ $cash->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: var(--ds-text-muted); font-size: .75rem;">Caisse utilisée pour les encaissements par défaut.</small>
                        </div>

                        <div class="col-md-12 saas-form-group">
                            <label>Taxe par défaut (%)</label>
                            <input type="number" step="0.01" name="default_tax" value="{{ $setting->default_tax ?? 0 }}" min="0" max="100" placeholder="0">
                            <small style="color: var(--ds-text-muted); font-size: .75rem;">Pourcentage de TVA appliqué aux ventes. Mettez 0 pour désactiver.</small>
                        </div>

                        <div class="col-md-12 saas-form-group">
                            <label>Caisse TAXE</label>
                            <select name="tax_cash_id" class="form-select">
                                <option value="">Aucune</option>
                                @foreach($cashes as $cash)
                                    <option value="{{ $cash->id }}"
                                        @if(isset($setting) && $setting->tax_cash_id == $cash->id) selected @endif>
                                        {{ $cash->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: var(--ds-text-muted); font-size: .75rem;">Caisse de destination pour les montants de taxe collectés.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                        <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…">
                            <i class="bi bi-check-lg" aria-hidden="true"></i><span>Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#settingsForm').submit(function(e) {
                e.preventDefault();
                var form = this;
                var button = $(form).find('[type="submit"]');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('ams.settings.store') }}",
                    data: $(form).serialize(),
                    beforeSend: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Enregistrement…');
                    },
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: "Succès", text: data.msg, timer: 2000, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: "error", title: "Erreur", text: data.msg });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: "error", title: "Erreur serveur", text: "Impossible d'enregistrer les paramètres." });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
