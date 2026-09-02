@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Caisse</h1>
            <p>Gestion des comptes de caisse, soldes et rôles (principale, taxe).</p>
        </div>
        <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" aria-controls="addModal">
            <i class="bi bi-plus-lg"></i> Ajouter une caisse
        </button>
    </div>

    {{-- Statistiques --}}
    <section class="saas-metric-grid mb-4" aria-label="Résumé caisses">
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Total caisses</span><span class="saas-metric-icon"><i class="bi bi-wallet2"></i></span></div>
            <strong class="saas-metric-value">{{ $totalCash->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ $totalCash ? number_format($totalCashSum, 0, ',', ' ') : '0' }} FCFA</span>
        </div>
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Caisses actives</span><span class="saas-metric-icon"><i class="bi bi-check-circle"></i></span></div>
            <strong class="saas-metric-value">{{ $activeCash->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ $activeCash ? number_format($activeCashSum, 0, ',', ' ') : '0' }} FCFA</span>
        </div>
        <div class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Caisses inactives</span><span class="saas-metric-icon"><i class="bi bi-x-circle"></i></span></div>
            <strong class="saas-metric-value">{{ $inactiveCash->count }}</strong>
            <span style="color: var(--ds-text-muted); font-size: .78rem;">{{ $inactiveCash ? number_format($inactiveCashSum, 0, ',', ' ') : '0' }} FCFA</span>
        </div>
    </section>

    {{-- Modale Ajout --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Comptabilité</p>
                        <h3 class="modal-title">Ajouter une caisse</h3>
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
                                <label>Nom de la caisse</label>
                                <input type="text" name="name" placeholder="Ex. Caisse principale, Caisse de taxe…" required>
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label class="saas-switch-line" for="add-cash-is-default"><span><strong>Caisse principale</strong></span><input class="saas-switch-input cash-role-toggle" type="checkbox" name="is_default" id="add-cash-is-default" value="1"><span class="saas-switch-control" aria-hidden="true"></span></label>
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label class="saas-switch-line" for="add-cash-is-tax"><span><strong>Caisse de taxe</strong></span><input class="saas-switch-input cash-role-toggle" type="checkbox" name="is_tax" id="add-cash-is-tax" value="1"><span class="saas-switch-control" aria-hidden="true"></span></label>
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label class="saas-switch-line" for="add-cash-status"><span><strong>Statut</strong></span><input class="saas-switch-input" type="checkbox" name="status" id="add-cash-status" value="1" checked><span class="saas-switch-control" aria-hidden="true"></span></label>
                            </div>
                            <div class="col-md-12 saas-form-group mt-3">
                                <label>Description</label>
                                <textarea name="description" placeholder="Description facultative…"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Création…">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Créer la caisse</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Modification --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content saas-modal-warning">
                <div class="modal-header" style="border-left: 4px solid var(--ds-warning, #F5B942);">
                    <div>
                        <p class="saas-modal-eyebrow" style="color: var(--ds-warning, #F5B942);">Comptabilité</p>
                        <h3 class="modal-title">Modifier la caisse</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="edit_response" aria-live="polite"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Détail --}}
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Comptabilité</p>
                        <h3 class="modal-title">Détail de la caisse</h3>
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
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Comptes de caisse</h2>
                <p class="saas-card-description">Liste des caisses avec soldes, rôles et statuts.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Solde</th>
                        <th>Principale</th>
                        <th>Statut</th>
                        <th>Créé par</th>
                        <th>Créé le</th>
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

            $(document).on('change', '.cash-role-toggle', function () {
                if (this.checked) {
                    const otherName = this.name === 'is_default' ? 'is_tax' : 'is_default';
                    $(this).closest('form').find('input[name="' + otherName + '"]').prop('checked', false);
                }
            });

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('cash-account.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    { data: 'balance', name: 'balance' },
                    { data: 'is_default', name: 'is_default' },
                    { data: 'status', name: 'status' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucune donnée disponible",
                    "emptyTable": "Aucune caisse créée pour le moment",
                    "processing": "Chargement des caisses…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            $('#add').submit(function(event) {
                event.preventDefault();
                var form = this;
                var button = $(form).find('[type="submit"]');
                var formData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('cash-account.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    beforeSend: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Création…');
                    },
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title || "Succès", text: data.msg || "Caisse créée avec succès", showConfirmButton: false, timer: 3000, timerProgressBar: true });
                            $('#addModal').modal('hide');
                            form.reset();
                            Datatable.draw();
                        } else {
                            Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title || "Erreur", text: data.msg || "Impossible d'enregistrer la caisse", showConfirmButton: false, timer: 3000, timerProgressBar: true });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: "error", title: "Erreur", text: ajaxErrorMessage(xhr, "Impossible d'enregistrer la caisse."), timer: 3600 });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
                    }
                });
                return false;
            });

            $('body').on('click', '.editModal', function() {
                const trigger = this;
                var id = $(this).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#edit_response').empty();
                $.ajax({
                    url: '{{ url("ams/cash-account") }}/' + id + '/edit',
                    dataType: 'html',
                    success: function(result) {
                        $('#edit_response').html(result);
                        $('#editModal').modal('show');
                    },
                    error: function(xhr) {
                        $('#editModal').modal('hide');
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce compte.') });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger);
                    }
                });
            });

            $('body').on('click', '.view', function() {
                const trigger = this;
                var id = $(this).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#show_response').empty();
                $.ajax({
                    url: '{{ url("ams/cash-account") }}/' + id,
                    dataType: 'html',
                    success: function(result) {
                        $('#show_response').html(result);
                        $('#showModal').modal('show');
                    },
                    error: function(xhr) {
                        $('#showModal').modal('hide');
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce compte.') });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger);
                    }
                });
            });

            $('body').on('click', '.archive', function() {
                var id = $(this).data("id");
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                Swal.fire({
                    icon: 'warning',
                    title: "Archiver cette caisse ?",
                    html: '<p class="saas-confirm-copy">La caisse sera désactivée et ne pourra plus recevoir de transactions. Vous pourrez la restaurer ultérieurement.</p>',
                    confirmButtonText: 'Archiver',
                    showCancelButton: true,
                    cancelButtonText: 'Conserver',
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                headers: { 'X-CSRF-TOKEN': csrfToken },
                                type: 'DELETE',
                                url: 'cash-account/' + id,
                                dataType: 'json'
                            }).done(function(data) {
                                if (!data || !data.status) {
                                    Swal.showValidationMessage((data && data.msg) || "Impossible d'archiver cette caisse.");
                                    resolve(false);
                                    return;
                                }
                                resolve(data);
                            }).fail(function(xhr) {
                                Swal.showValidationMessage(ajaxErrorMessage(xhr, "Impossible d'archiver cette caisse."));
                                resolve(false);
                            });
                        });
                    },
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: result.value.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: result.value.msg });
                    Datatable.draw();
                });
            });

            $('body').on('click', '.restore', function() {
                var id = $(this).data("id");
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                Swal.fire({
                    icon: 'question',
                    title: 'Restaurer cette caisse ?',
                    html: '<p class="saas-confirm-copy">Elle redeviendra disponible pour les opérations financières.</p>',
                    confirmButtonText: 'Restaurer',
                    showCancelButton: true,
                    cancelButtonText: 'Annuler',
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                headers: { 'X-CSRF-TOKEN': csrfToken },
                                type: 'DELETE',
                                url: 'cash-account/' + id,
                                dataType: 'json'
                            }).done(function(data) {
                                if (!data || !data.status) {
                                    Swal.showValidationMessage((data && data.msg) || 'Impossible de restaurer cette caisse.');
                                    resolve(false);
                                    return;
                                }
                                resolve(data);
                            }).fail(function(xhr) {
                                Swal.showValidationMessage(ajaxErrorMessage(xhr, 'Impossible de restaurer cette caisse.'));
                                resolve(false);
                            });
                        });
                    },
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: result.value.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: result.value.msg });
                    Datatable.draw();
                });
            });
        });
    </script>
    @endpush
@endsection
