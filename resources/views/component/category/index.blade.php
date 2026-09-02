@extends('layouts.saas')
@section('title', 'Catégories')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link href="{{ asset('hub/assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Catégories</h1>
            <p>Organisez les produits par familles pour accélérer la vente et le suivi du stock.</p>
        </div>
        <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" aria-controls="addModal">
            <i class="bi bi-plus-lg"></i> Ajouter une catégorie
        </button>
    </div>

    {{-- Modale Ajout --}}
    <x-ui.modal id="addModal" title="Ajouter une catégorie" eyebrow="Catalogue" variant="primary">
        <p class="saas-modal-intro">Créez une famille de produits pour structurer le catalogue et faciliter la recherche à la vente.</p>
        <form id="add">
            @csrf
            <div class="saas-form-group">
                <label for="category-name">Nom de la catégorie <span aria-hidden="true">*</span></label>
                <input id="category-name" type="text" name="name" placeholder="Ex. Boissons, accessoires…" maxlength="255" required autofocus aria-describedby="category-name-help">
                <small id="category-name-help">Utilisez un nom court et reconnaissable par l’équipe.</small>
            </div>
            <div class="saas-modal-actions">
                <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Création…">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Créer la catégorie</span>
                </button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modale Modification --}}
    <x-ui.modal id="editModal" title="Modifier la catégorie" eyebrow="Catalogue" variant="warning">
        <div id="edit_response" aria-live="polite"></div>
    </x-ui.modal>

    {{-- Modale Détail --}}
    <x-ui.modal id="showModal" title="Détail de la catégorie" eyebrow="Catalogue" variant="primary">
        <div id="show_response" aria-live="polite"></div>
    </x-ui.modal>

    {{-- Catégories actives --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Catégories actives</h2>
                <p class="saas-card-description">Catégories disponibles lors de la création et du classement des produits.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Créée par</th>
                        <th>Créée le</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Catégories inactives --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Catégories archivées</h2>
                <p class="saas-card-description">Catégories retirées du catalogue, disponibles pour restauration.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="disabled_datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Créée par</th>
                        <th>Créée le</th>
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
    <script>
        $(function() {
            function ajaxErrorMessage(xhr, fallback) {
                return xhr && xhr.responseJSON
                    ? (xhr.responseJSON.msg || xhr.responseJSON.message || fallback)
                    : fallback;
            }

            function changeCategoryStatus(id, fallback) {
                return new Promise(function(resolve) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'DELETE',
                        url: '{{ url("component/category") }}/' + id,
                        dataType: 'json'
                    }).done(function(data) {
                        if (!data || !data.status) {
                            Swal.showValidationMessage((data && data.msg) || fallback);
                            resolve(false);
                            return;
                        }
                        resolve(data);
                    }).fail(function(xhr) {
                        Swal.showValidationMessage(ajaxErrorMessage(xhr, fallback));
                        resolve(false);
                    });
                });
            }

            var DatatableActive = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('category.index') }}",
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
                    "emptyTable": "Aucune catégorie active pour le moment",
                    "processing": "Chargement des catégories…",
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
                ajax: "{{ route('category.disabled.listing') }}",
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
                    "emptyTable": "Aucune catégorie archivée",
                    "processing": "Chargement des catégories archivées…",
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
                    url: "{{ route('category.store') }}",
                    data: $('#add').serialize(),
                    dataType: 'json',
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
                        const message = xhr.responseJSON
                            ? (xhr.responseJSON.msg || xhr.responseJSON.message)
                            : null;
                        Swal.fire({ icon: "error", title: "Erreur", text: message || "Impossible de communiquer avec le serveur.", timer: 3600 });
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
                    url: '{{ url("component/category") }}/' + id + '/edit',
                    dataType: 'html',
                    success: function(result) { $('#edit_response').html(result); },
                    error: function(xhr) { $('#editModal').modal('hide'); Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger cette catégorie.') }); },
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
                    url: '{{ url("component/category") }}/' + id,
                    dataType: 'html',
                    success: function(result) { $('#show_response').html(result); },
                    error: function(xhr) { $('#showModal').modal('hide'); Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger cette catégorie.') }); },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            $('body').on('click', '.archive', function() {
                var id = $(this).data("id");
                Swal.fire({
                    icon: "warning",
                    title: "Retirer cette catégorie ?",
                    html: '<p class="saas-confirm-copy">Si elle contient des produits, elle sera archivée et pourra être restaurée. Sans produit associé, elle sera supprimée définitivement.</p>',
                    confirmButtonText: "Retirer la catégorie",
                    showCancelButton: true,
                    cancelButtonText: "Conserver",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() { return changeCategoryStatus(id, "Impossible d'archiver cette catégorie."); }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    const data = result.value;
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: data.msg });
                    DatatableActive.draw();
                    DatatableInactive.draw();
                });
            });

            $('body').on('click', '.restore', function() {
                var id = $(this).data("id");
                Swal.fire({
                    icon: "question",
                    title: "Restaurer cette catégorie ?",
                    html: '<p class="saas-confirm-copy">Elle redeviendra disponible lors de la création et du classement des produits.</p>',
                    confirmButtonText: "Restaurer",
                    showCancelButton: true,
                    cancelButtonText: "Annuler",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() { return changeCategoryStatus(id, "Impossible de restaurer cette catégorie."); }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    const data = result.value;
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: data.msg });
                    DatatableActive.draw();
                    DatatableInactive.draw();
                });
            });
        });
    </script>
    @endpush
@endsection
