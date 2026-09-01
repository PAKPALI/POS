@extends('layouts.saas')
@section('title', 'Fournisseurs')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Fournisseurs</h1>
            <p>Centralisez les partenaires d’approvisionnement et leurs coordonnées professionnelles.</p>
        </div>
        <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Ajouter un fournisseur
        </button>
    </div>

    {{-- Modale Ajout --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header modal-header-accent">
                    <h3 class="modal-title">Ajouter un fournisseur</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 saas-form-group">
                                <label>Nom</label>
                                <input type="text" name="name" placeholder="Nom">
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Contact / Adresse</label>
                                <input type="text" name="contact" placeholder="Contact ou adresse">
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Téléphone</label>
                                <input type="text" name="phone" placeholder="Téléphone">
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>WhatsApp</label>
                                <input type="text" name="whatsapp" placeholder="Numéro WhatsApp">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Création…">Créer le fournisseur</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Modification --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header modal-header-accent modal-header-warning">
                    <h3 class="modal-title">Modifier fournisseur</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="edit_response"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Détail --}}
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header modal-header-accent">
                    <h3 class="modal-title">Détail</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="show_response"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fournisseurs actifs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div><h2>Fournisseurs actifs</h2><p class="saas-card-description">Partenaires disponibles pour les produits et les mouvements de stock.</p></div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Téléphone</th>
                        <th>WhatsApp</th>
                        <th>Créer par</th>
                        <th>Créer le</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Fournisseurs inactifs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div><h2>Fournisseurs archivés</h2><p class="saas-card-description">Partenaires conservés dans l’historique et disponibles pour restauration.</p></div>
        </div>
        <div class="table-responsive">
            <table id="disabled_datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Téléphone</th>
                        <th>WhatsApp</th>
                        <th>Créer par</th>
                        <th>Créer le</th>
                        <th>Status</th>
                        <th>Action</th>
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
                if (xhr && xhr.responseJSON) {
                    return xhr.responseJSON.msg || xhr.responseJSON.message || fallback;
                }
                return fallback;
            }

            function showSupplierSuccess(data) {
                Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: data.msg });
            }

            function requestSupplierStatus(id, fallbackMessage) {
                const cancelButton = Swal.getCancelButton();
                if (cancelButton) cancelButton.disabled = true;

                return new Promise(function(resolve) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'DELETE',
                        url: '{{ url("component/supplier") }}/' + id,
                        dataType: 'json'
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
                        Swal.showValidationMessage(ajaxErrorMessage(xhr, fallbackMessage));
                        resolve(false);
                    });
                });
            }

            var DatatableActive = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('supplier.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'contact', name: 'contact' },
                    { data: 'phone', name: 'phone' },
                    { data: 'whatsapp', name: 'whatsapp' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun fournisseur actif pour le moment",
                    "processing": "Chargement des fournisseurs…",
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
                ajax: "{{ route('supplier.disabled.listing') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'contact', name: 'contact' },
                    { data: 'phone', name: 'phone' },
                    { data: 'whatsapp', name: 'whatsapp' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun fournisseur archivé",
                    "processing": "Chargement des fournisseurs archivés…",
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
                $.ajax({
                    type: 'POST',
                    url: "{{ route('supplier.store') }}",
                    data: $('#add').serialize(),
                    datatype: 'json',
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                            $('#addModal').modal('hide');
                            DatatableActive.draw();
                        } else {
                            Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: "error", title: "Erreur", text: ajaxErrorMessage(xhr, "Impossible de communiquer avec le serveur."), timer: 3600 });
                    }
                });
                return false;
            });

            $('body').on('click', '.editModal', function() {
                const trigger = this;
                var id = $(this).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#edit_response').empty();
                $('#editModal').modal('show');
                $.ajax({
                    url: '{{ url("component/supplier") }}/' + id + '/edit',
                    dataType: 'html',
                    success: function(result) { $('#edit_response').html(result); },
                    error: function(xhr) {
                        $('#editModal').modal('hide');
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce fournisseur.') });
                    },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            $('body').on('click', '.view', function() {
                const trigger = this;
                var id = $(this).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#show_response').empty();
                $('#showModal').modal('show');
                $.ajax({
                    url: '{{ url("component/supplier") }}/' + id,
                    dataType: 'html',
                    success: function(result) { $('#show_response').html(result); },
                    error: function(xhr) {
                        $('#showModal').modal('hide');
                        Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce fournisseur.') });
                    },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            $('body').on('click', '.archive', function() {
                var id = $(this).data("id");
                Swal.fire({
                    icon: "warning",
                    title: "Confirmer l'opération",
                    html: '<div style="background:#dc3545;color:white;padding:15px;border-radius:8px;font-size:15px;font-weight:bold;text-align:left;">ATTENTION<br><br>Ce fournisseur sera <strong>ARCHIVÉ</strong>.</div>',
                    confirmButtonText: "Oui",
                    confirmButtonColor: "#dc3545",
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    cancelButtonColor: "#0d6efd",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() { return requestSupplierStatus(id, "Impossible d'archiver ce fournisseur."); }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    showSupplierSuccess(result.value);
                    DatatableActive.draw();
                    DatatableInactive.draw();
                });
            });

            $('body').on('click', '.restore', function() {
                var id = $(this).data("id");
                Swal.fire({
                    icon: "question",
                    title: "Êtes-vous sûr de vouloir restaurer ce fournisseur ?",
                    confirmButtonText: "Oui",
                    confirmButtonColor: 'green',
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    cancelButtonColor: '#0d6efd',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() { return requestSupplierStatus(id, "Impossible de restaurer ce fournisseur."); }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    showSupplierSuccess(result.value);
                    DatatableActive.draw();
                    DatatableInactive.draw();
                });
            });
        });
    </script>
    @endpush
@endsection
