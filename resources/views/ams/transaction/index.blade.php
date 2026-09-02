@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-19" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/saas-page-fixes.css') }}?v=20260902-1" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Opérations</h1>
            <p>Suivez les entrées, sorties et transferts entre caisses.</p>
        </div>
        <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" aria-controls="addModal">
            <i class="bi bi-plus-lg"></i> Nouvelle opération
        </button>
    </div>

    {{-- Statistiques --}}
    <section class="saas-metric-grid mb-4" aria-label="Résumé opérations">
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Total opérations</span><span class="saas-metric-icon"><i class="bi bi-arrow-left-right"></i></span></div>
            <strong class="saas-metric-value">{{ $totalTransactions->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ number_format($totalTransactions->total, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Entrées</span><span class="saas-metric-icon"><i class="bi bi-arrow-down-circle"></i></span></div>
            <strong class="saas-metric-value">{{ $inTransactions->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ number_format($inTransactions->total, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Sorties</span><span class="saas-metric-icon"><i class="bi bi-arrow-up-circle"></i></span></div>
            <strong class="saas-metric-value">{{ $outTransactions->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ number_format($outTransactions->total, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Transferts</span><span class="saas-metric-icon"><i class="bi bi-arrow-repeat"></i></span></div>
            <strong class="saas-metric-value">{{ $transferTransactions->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ number_format($transferTransactions->total, 0, ',', ' ') }} FCFA</span>
        </div>
    </section>

    {{-- Balance nette --}}
    <div class="saas-card saas-transaction-balance">
        <div class="saas-card-head">
            <div>
                <h2>Balance nette</h2>
                <p class="saas-card-description">Différence entre les entrées et les sorties.</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 1.6rem; font-weight: 800; color: {{ $netBalance >= 0 ? 'var(--ds-success, #35C98B)' : 'var(--ds-danger, #FF626E)' }};">
                {{ number_format($netBalance, 0, ',', ' ') }} FCFA
            </div>
            <span class="saas-status-badge {{ $netBalance >= 0 ? 'is-active' : 'is-inactive' }}">{{ $netBalance >= 0 ? 'Positive' : 'Négative' }}</span>
        </div>
    </div>

    {{-- Modale Ajout --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Comptabilité</p>
                        <h3 class="modal-title">Nouvelle opération</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 saas-form-group">
                                <label>Type d'opération</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Choisir le type</option>
                                    <option value="OUT">Sortie</option>
                                    <option value="TRANSFER">Transfert</option>
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group" id="from_cash_div">
                                <label>Caisse source</label>
                                <select name="from_cash_id" class="form-select">
                                    <option value="">Choisir une caisse</option>
                                    @foreach($cashes as $cash)
                                        <option value="{{ $cash->id }}">{{ $cash->name }} ({{ number_format($cash->balance, 2, ',', ' ') }} FCFA)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group" id="to_cash_div">
                                <label>Caisse destination</label>
                                <select name="to_cash_id" class="form-select">
                                    <option value="">Choisir une caisse</option>
                                    @foreach($cashes as $cash)
                                        <option value="{{ $cash->id }}">{{ $cash->name }} ({{ number_format($cash->balance, 2, ',', ' ') }} FCFA)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Montant (FCFA)</label>
                                <input type="number" name="amount" min="1" required placeholder="0">
                            </div>
                            <div class="col-md-12 saas-form-group">
                                <label>Description</label>
                                <textarea name="description" rows="3" placeholder="Motif de l'opération…"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…">
                                <i class="bi bi-check-lg" aria-hidden="true"></i><span>Valider l'opération</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Détail --}}
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Comptabilité</p>
                        <h3 class="modal-title">Détail de l'opération</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="show_response" aria-live="polite"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="saas-card saas-transaction-operations">
        <div class="saas-card-head">
            <div>
                <h2>Liste des opérations</h2>
                <p class="saas-card-description">Historique complet des mouvements financiers avec traçabilité.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Montant</th>
                        <th>Utilisateur</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('hub/assets/plugins/datatables.net/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script>
        $(function() {
            function ajaxErrorMessage(xhr, fallback) {
                return xhr && xhr.responseJSON
                    ? (xhr.responseJSON.msg || xhr.responseJSON.message || fallback)
                    : fallback;
            }

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('transaction.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'type', name: 'type' },
                    { data: 'from_cash', name: 'from_cash' },
                    { data: 'to_cash', name: 'to_cash' },
                    { data: 'amount', name: 'amount' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucune donnée disponible",
                    "emptyTable": "Aucune opération enregistrée",
                    "processing": "Chargement des opérations…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            $('#type').change(function() {
                let type = $(this).val();
                if (type === 'IN') {
                    $('#from_cash_div').hide();
                    $('#to_cash_div').show();
                } else if (type === 'OUT') {
                    $('#from_cash_div').show();
                    $('#to_cash_div').hide();
                } else {
                    $('#from_cash_div').show();
                    $('#to_cash_div').show();
                }
            });

            $('#from_cash_div').hide();
            $('#to_cash_div').hide();

            $('#add').submit(function(e) {
                e.preventDefault();
                var form = this;
                var button = $(form).find('[type="submit"]');
                var formData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('transaction.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Enregistrement…');
                    },
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: "Succès", text: data.msg, timer: 2000, showConfirmButton: false });
                            $('#addModal').modal('hide');
                            form.reset();
                            $('#from_cash_div').hide();
                            $('#to_cash_div').hide();
                            Datatable.draw();
                        } else {
                            Swal.fire({ icon: "error", title: "Erreur", text: data.msg });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: "error", title: "Erreur serveur", text: ajaxErrorMessage(xhr, "Impossible de communiquer avec le serveur.") });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
                    }
                });
            });

            $('body').on('click', '.view', function() {
                const trigger = this;
                var id = $(trigger).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#show_response').empty();
                $.ajax({
                    url: '{{ url("ams/transaction") }}/' + id,
                    dataType: 'html',
                    success: function(result) {
                        $('#show_response').html(result);
                        $('#showModal').modal('show');
                    },
                    error: function(xhr) {
                        $('#showModal').modal('hide');
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger cette opération.') });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger);
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
