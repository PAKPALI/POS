@extends('layouts.saas')
@section('title', 'Clients')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Clients</h1>
            <p>Consultez, ajoutez et gérez la clientèle de l'entreprise active.</p>
        </div>
        <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" aria-controls="addModal">
            <i class="bi bi-plus-lg"></i> Ajouter un client
        </button>
    </div>

    {{-- Modale Ajout --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Clients</p>
                        <h3 class="modal-title">Ajouter un client</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 saas-form-group">
                                <label>Nom</label>
                                <input type="text" name="name" placeholder="Nom du client" required autofocus>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Pays du numéro</label>
                                <select name="country_code" id="clientCountry" class="form-select country-select" data-placeholder="Rechercher un pays" required>
                                    @foreach(config('african_countries') as $iso => $countryName)
                                        <option value="{{ $iso }}" @selected($iso === (app(\App\Services\CompanyContext::class)->getCompanyOrNull()?->country_code ?? 'TG'))>{{ $countryName }} ({{ $iso }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Téléphone local</label>
                                <input type="tel" name="phone" id="clientPhone" inputmode="numeric" pattern="[0-9]{6,15}" minlength="6" maxlength="15" placeholder="Ex. 90000000">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Ajout en cours…">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Ajouter le client</span>
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
                        <p class="saas-modal-eyebrow" style="color: var(--ds-warning, #F5B942);">Clients</p>
                        <h3 class="modal-title">Modifier le client</h3>
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
                        <p class="saas-modal-eyebrow">Clients</p>
                        <h3 class="modal-title">Détail du client</h3>
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

    {{-- Clients actifs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Clients actifs</h2>
                <p class="saas-card-description">Clients visibles lors des ventes et dans le sélecteur du point de vente.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Créé par</th>
                        <th>Créé le</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Clients inactifs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Clients archivés</h2>
                <p class="saas-card-description">Clients retirés de la vente, disponibles pour restauration et traçabilité.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="disabled_datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Créé par</th>
                        <th>Créé le</th>
                        <th>Statut</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            function clientRequestError(xhr, fallback) {
                const response = xhr && xhr.responseJSON ? xhr.responseJSON : {};
                const validationErrors = response.errors ? Object.values(response.errors) : [];
                const firstValidationError = validationErrors.length
                    ? (Array.isArray(validationErrors[0]) ? validationErrors[0][0] : validationErrors[0])
                    : null;
                return response.msg || response.message || firstValidationError || fallback;
            }

            $('.country-select').each(function() {
                var $el = $(this);
                var dropdownParent = $el.closest('.modal');
                if (dropdownParent.length && !$el.hasClass('select2-hidden-accessible')) {
                    $el.select2({
                        width: '100%',
                        placeholder: $el.data('placeholder') || 'Rechercher un pays…',
                        allowClear: true,
                        dropdownParent: dropdownParent,
                        language: { noResults: function() { return 'Aucun pays trouvé'; } }
                    });
                }
            });

            var DatatableActive = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('client.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun client actif pour le moment",
                    "processing": "Chargement des clients…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            var DatatableInactive = $('#disabled_datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('client.disabled.listing') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun client archivé",
                    "processing": "Chargement des clients archivés…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            window.addEventListener('datatableUpdated', function() {
                DatatableActive.ajax.reload(null, false);
                DatatableInactive.ajax.reload(null, false);
            });

            $('#add').submit(function(event) {
                event.preventDefault();
                var form = this;
                var button = $(form).find('[type="submit"]');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('client.store') }}",
                    data: $(form).serialize(),
                    dataType: 'json',
                    beforeSend: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Ajout en cours…');
                    },
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                            $('#addModal').modal('hide');
                            form.reset();
                            DatatableActive.draw();
                        } else {
                            Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: "error", title: "Ajout impossible", text: clientRequestError(xhr, "Impossible de communiquer avec le serveur."), timer: 3600 });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
                    }
                });
                return false;
            });

            $('body').on('click', '.editModal', function(event) {
                event.preventDefault();
                const trigger = event.currentTarget;
                const id = $(trigger).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#edit_response').empty();
                $.ajax({
                    url: '{{ url("component/client") }}/' + id + '/edit',
                    dataType: 'html',
                    success: function(result) {
                        $('#edit_response').html(result);
                        $('#editModal').modal('show');
                        var countryInEdit = $('#editModal .country-select');
                        if (countryInEdit.length && !countryInEdit.hasClass('select2-hidden-accessible')) {
                            countryInEdit.select2({
                                width: '100%',
                                placeholder: 'Rechercher un pays…',
                                allowClear: true,
                                dropdownParent: $('#editModal'),
                                language: { noResults: function() { return 'Aucun pays trouvé'; } }
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: clientRequestError(xhr, "Impossible de charger le formulaire de modification.") });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger);
                    }
                });
            });

            $('body').on('click', '.view', function(event) {
                event.preventDefault();
                const trigger = event.currentTarget;
                const id = $(trigger).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#show_response').empty();
                $.ajax({
                    url: '{{ url("component/client") }}/' + id,
                    dataType: 'html',
                    success: function(result) {
                        $('#show_response').html(result);
                        $('#showModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: clientRequestError(xhr, "Impossible de consulter ce client.") });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger);
                    }
                });
            });

            function submitClientStatusChange(id, csrfToken, fallbackMessage) {
                const cancelButton = Swal.getCancelButton();
                if (cancelButton) cancelButton.disabled = true;

                return new Promise(function(resolve) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        type: 'DELETE',
                        url: '{{ url("component/client") }}/' + id,
                        dataType: 'json',
                    })
                    .done(function(data) {
                        if (!data || !data.status) {
                            if (cancelButton) cancelButton.disabled = false;
                            Swal.showValidationMessage((data && data.msg) || fallbackMessage);
                            resolve(false);
                            return;
                        }
                        resolve(data);
                    })
                    .fail(function(xhr) {
                        if (cancelButton) cancelButton.disabled = false;
                        Swal.showValidationMessage(clientRequestError(xhr, fallbackMessage));
                        resolve(false);
                    });
                });
            }

            function showClientStatusSuccess(data) {
                Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: data.msg });
                DatatableActive.draw();
                DatatableInactive.draw();
            }

            $('body').on('click', '.archive', function() {
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const id = $(this).data('id');
                Swal.fire({
                    icon: 'warning',
                    title: "Retirer ce client ?",
                    html: '<p class="saas-confirm-copy">Ce client sera <strong>archivé</strong> et ne sera plus proposé lors des ventes. Vous pourrez le restaurer ultérieurement.</p>',
                    confirmButtonText: 'Retirer le client',
                    showCancelButton: true,
                    cancelButtonText: 'Conserver',
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() {
                        return submitClientStatusChange(id, csrfToken, "Impossible d'archiver ce client.");
                    },
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    showClientStatusSuccess(result.value);
                });
            });

            $('body').on('click', '.restore', function() {
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const id = $(this).data('id');
                Swal.fire({
                    icon: 'question',
                    title: 'Restaurer ce client ?',
                    html: '<p class="saas-confirm-copy">Il redeviendra disponible dans le sélecteur du point de vente et dans les ventes.</p>',
                    confirmButtonText: 'Restaurer',
                    showCancelButton: true,
                    cancelButtonText: 'Annuler',
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() {
                        return submitClientStatusChange(id, csrfToken, 'Impossible de restaurer ce client.');
                    },
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    showClientStatusSuccess(result.value);
                });
            });
        });
    </script>
    @endpush
@endsection
