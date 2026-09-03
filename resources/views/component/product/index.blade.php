@extends('layouts.saas')
@section('title', 'Produits')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Produits</h1>
            <p>Gérez les articles vendus, leurs prix, leur stock de sécurité et leur disponibilité.</p>
        </div>
        <button type="button" class="saas-btn saas-btn-primary {{ !$canAddProduct ? 'is-disabled' : '' }}" @if(!$canAddProduct) aria-disabled="true" data-limit-message="La limite de produits actifs de votre plan d’abonnement est atteinte." @else data-bs-toggle="modal" data-bs-target="#addModal" @endif>
            <i class="bi bi-plus-lg"></i> Ajouter un produit
        </button>
    </div>

    {{-- Modale Ajout --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header modal-header-accent">
                    <h3 class="modal-title">Ajouter un produit</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add">
                        @csrf
                        <input type="hidden" name="type" value="1">
                        <div class="row">
                            <div class="col-md-6 saas-form-group">
                                <label>Catégorie</label>
                                <select class="form-select select2-category" name="category" required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    @foreach ($Category as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Fournisseur</label>
                                <select class="form-select" name="supplier_id">
                                    <option value="">Aucun fournisseur</option>
                                    @foreach ($Supplier as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Nom</label>
                                <input type="text" name="name" placeholder="Nom du produit" required>
                            </div>
                            <div class="col-md-12 saas-form-group">
                                <label>Marge de sécurité</label>
                                <input type="number" name="margin" value="0" placeholder="0">
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label>Prix de vente</label>
                                <input type="number" name="price" class="price" min="0" step="0.01" placeholder="0" required>
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label>Prix d'achat</label>
                                <input type="number" name="purchase_price" class="purchase_price" min="0" step="0.01" placeholder="0" required>
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label>Bénéfice</label>
                                <input type="number" name="profit" class="profit" readonly placeholder="0">
                            </div>
                            <div class="col-md-4 saas-form-group">
                                <label>Prix TTC</label>
                                <input type="number" class="price_ttc" readonly>
                            </div>
                            <div class="col-md-8 saas-form-group">
                                <label>Image</label>
                                <input type="file" name="image" class="form-control">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Création…">
                                <span>Créer le produit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Modification --}}
    <div class="modal fade saas-modal" id="editModal" tabindex="-1" aria-labelledby="editModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content saas-modal-content saas-modal-warning">
                <div class="modal-header saas-modal-header">
                    <div>
                        <span class="saas-modal-eyebrow">Catalogue</span>
                        <h3 class="modal-title" id="editModalTitle">Modifier produit</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body saas-modal-body">
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

    {{-- Produits actifs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div><h2>Produits actifs</h2><p class="saas-card-description">Articles immédiatement disponibles dans le catalogue et au point de vente.</p></div>
        </div>

        <details class="saas-accordion">
            <summary><i class="bi bi-funnel"></i> Filtrer les produits</summary>
            <div class="saas-accordion-body">
                <div class="saas-filter-row">
                    <div class="saas-form-group">
                        <label>Catégorie</label>
                        <select class="form-select" id="filter_category">
                            <option value="">Toutes les catégories</option>
                            @foreach($Category as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="saas-form-group">
                        <label>Quantité</label>
                        <select class="form-select" id="filter_qte">
                            <option value="">Tous</option>
                            <option value="with">Avec quantité</option>
                            <option value="without">Sans quantité</option>
                        </select>
                    </div>
                    <div class="saas-form-group">
                        <label>Status</label>
                        <select class="form-select" id="filter_status">
                            <option value="">Tous</option>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                </div>
            </div>
        </details>

        <details class="saas-accordion">
            <summary><i class="bi bi-download"></i> Exporter les données</summary>
            <div class="saas-accordion-body">
                <div class="saas-export-group">
                    <button type="button" data-format="csv" class="saas-btn saas-btn-outline exportTabular"><i class="bi bi-filetype-csv"></i> CSV</button>
                    <button type="button" data-format="excel" class="saas-btn saas-btn-primary exportTabular"><i class="bi bi-file-earmark-excel"></i> Excel</button>
                    <button type="button" id="exportPdf" class="saas-btn saas-btn-secondary"><i class="bi bi-filetype-pdf"></i> PDF</button>
                </div>
            </div>
        </details>

        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Etat</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Quantité</th>
                        <th>Prix HT</th>
                        <th>Prix TTC</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Produits inactifs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div><h2>Produits archivés</h2><p class="saas-card-description">Articles retirés de la vente, conservés pour restauration et traçabilité.</p></div>
        </div>
        <div class="table-responsive">
            <table id="disabled_datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Etat</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Quantité</th>
                        <th>Prix HT</th>
                        <th>Prix TTC</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            function ajaxErrorMessage(xhr, fallback) {
                return xhr && xhr.responseJSON
                    ? (xhr.responseJSON.msg || xhr.responseJSON.message || fallback)
                    : fallback;
            }

            function showPlanLimitAlert(message) {
                var canUpgrade = @json(app(\App\Services\CompanyContext::class)->hasPermission('subscription.manage'));
                return Swal.fire({ icon: 'warning', title: 'Limite du plan atteinte', text: message, showCancelButton: canUpgrade, confirmButtonText: canUpgrade ? 'Améliorer mon plan' : 'OK', cancelButtonText: 'Fermer', buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' } }).then(function(result) { if (canUpgrade && result.isConfirmed) window.location.href = '{{ route('subscriptions.index') }}'; });
            }

            $(document).on('click', '[data-limit-message]', function(event) {
                event.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Limite du plan atteinte', text: this.dataset.limitMessage, buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' } });
            });

            function changeProductStatus(id, fallback) {
                return new Promise(function(resolve) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'DELETE',
                        url: '{{ url("component/product") }}/' + id,
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

            $('.select2-category').select2({
                dropdownParent: $('#addModal'),
                width: '100%',
                placeholder: "Sélectionnez une catégorie",
                allowClear: true
            });

            function renderEtat(data, type, row) {
                var m = parseInt(data) || 0;
                var q = parseInt(row.qte) || 0;
                if (m <= 0) return '<span class="saas-stock-ok"><i class="bi bi-check-circle"></i></span>';
                if (q <= m) return '<span class="saas-stock-alert blink-red"><i class="bi bi-exclamation-triangle"></i></span>';
                return '<span class="saas-stock-ok"><i class="bi bi-check-circle"></i></span>';
            }

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('product.index') }}",
                    data: function(d) {
                        d.category_id = $('#filter_category').val();
                        d.qte = $('#filter_qte').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'margin', name: 'margin', render: renderEtat },
                    { data: 'name', name: 'name' },
                    { data: 'category_id', name: 'category_id' },
                    { data: 'supplier_id', name: 'supplier_id' },
                    { data: 'qte', name: 'qte' },
                    { data: 'price', name: 'price' },
                    { data: 'price_ttc', name: 'price_ttc' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun produit actif pour le moment",
                    "processing": "Chargement des produits…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            var Disabled_Datatable = $('#disabled_datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('product.disabled.listing') }}",
                    data: function(d) {
                        d.category_id = $('#filter_category').val();
                        d.qte = $('#filter_qte').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'margin', name: 'margin', render: renderEtat },
                    { data: 'name', name: 'name' },
                    { data: 'category_id', name: 'category_id' },
                    { data: 'supplier_id', name: 'supplier_id' },
                    { data: 'qte', name: 'qte' },
                    { data: 'price', name: 'price' },
                    { data: 'price_ttc', name: 'price_ttc' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucun résultat correspondant",
                    "emptyTable": "Aucun produit archivé",
                    "processing": "Chargement des produits archivés…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
                Disabled_Datatable.ajax.reload(null, false);
            });

            $('#filter_category, #filter_qte, #filter_status').on('change', function() {
                Datatable.draw();
                Disabled_Datatable.draw();
            });

            $('#add').submit(function(event) {
                event.preventDefault();
                var formData = new FormData($('#add')[0]);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('product.store') }}",
                    enctype: 'multipart/form-data',
                    data: formData,
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                            $('#add')[0].reset();
                            Datatable.draw();
                        } else {
                            if (data.msg && data.msg.toLowerCase().includes('limite')) showPlanLimitAlert(data.msg);
                            else Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                        }
                    },
                    error: function(xhr) {
                        var message = ajaxErrorMessage(xhr, "Impossible de communiquer avec le serveur.");
                        if (xhr.status === 422 && message.toLowerCase().includes('limite')) showPlanLimitAlert(message);
                        else Swal.fire({ icon: "error", title: "Erreur", text: message, timer: 3600 });
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
                    url: '{{ url("component/product") }}/' + id + '/edit',
                    dataType: 'html',
                    success: function(result) { $('#edit_response').html(result); },
                    error: function(xhr) { $('#editModal').modal('hide'); Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce produit.') }); },
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
                    url: '{{ url("component/product") }}/' + id,
                    dataType: 'html',
                    success: function(result) { $('#show_response').html(result); },
                    error: function(xhr) { $('#showModal').modal('hide'); Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce produit.') }); },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            let TAX = {{ \App\Models\AMS\Setting::first()->default_tax ?? 0 }};

            $('.price, .purchase_price').on('input', function() {
                var unitPrice = parseFloat($('.price').val()) || 0;
                var purchasePrice = parseFloat($('.purchase_price').val()) || 0;
                var profit = unitPrice - purchasePrice;
                $('.profit').val(profit);
                var ttc = unitPrice + (unitPrice * TAX / 100);
                $('.price_ttc').val(ttc.toFixed(0));
            });

            $('body').on('click', '.archive', function() {
                var id = $(this).data("id");
                Swal.fire({
                    icon: "warning",
                    title: "Confirmer l'opération",
                    html: '<div class="saas-alert saas-alert-danger"><strong>ATTENTION</strong><br><br>Si ce produit n\'a jamais été utilisé dans une vente, il sera <strong>SUPPRIMÉ DÉFINITIVEMENT</strong>.<br><br>S\'il est déjà lié à une ou plusieurs ventes, il sera simplement <strong>ARCHIVÉ</strong>.</div>',
                    confirmButtonText: "Oui",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() { return changeProductStatus(id, "Impossible d'archiver ce produit."); }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    const data = result.value;
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: data.msg });
                    Datatable.draw();
                    Disabled_Datatable.draw();
                });
            });

            $('body').on('click', '.restore', function() {
                var id = $(this).data("id");
                Swal.fire({
                    icon: "question",
                    title: "Êtes-vous sûr de vouloir restaurer ce produit ?",
                    confirmButtonText: "Oui",
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() { return changeProductStatus(id, "Impossible de restaurer ce produit."); }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;
                    const data = result.value;
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 5000, timerProgressBar: true, text: data.msg });
                    Datatable.draw();
                    Disabled_Datatable.draw();
                });
            });

            $('#exportPdf').on('click', function(e) {
                e.preventDefault();
                let params = $.param({ category_id: $('#filter_category').val(), qte: $('#filter_qte').val(), status: $('#filter_status').val() });
                window.open("{{ route('product.export.pdf') }}?" + params, '_blank');
            });

            $('.exportTabular').on('click', function(e) {
                e.preventDefault();
                const button = this;
                const params = $.param({ category_id: $('#filter_category').val(), qte: $('#filter_qte').val(), status: $('#filter_status').val() });
                const baseUrl = "{{ route('product.export.tabular', ['format' => '__FORMAT__']) }}";
                window.ServerButtonLoader.download(button, baseUrl.replace('__FORMAT__', button.dataset.format) + '?' + params)
                    .catch(error => Swal.fire({ icon: 'error', title: 'Export impossible', text: error.message }));
            });
        });
    </script>
    @endpush
@endsection
