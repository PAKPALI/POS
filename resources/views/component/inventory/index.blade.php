@extends('layouts.saas')
@section('title', 'Inventaires')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Inventaires</h1>
            <p>Suivez les entrées et sorties de stock par produit, fournisseur et date.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="saas-btn saas-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg"></i> Entrée
            </button>
            <button type="button" class="saas-btn saas-btn-danger" data-bs-toggle="modal" data-bs-target="#removeModal">
                <i class="bi bi-dash-lg"></i> Sortie
            </button>
        </div>
    </div>

    {{-- Modale Entrée --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Inventaire</p>
                        <h3 class="modal-title">Ajouter une entrée</h3>
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
                                <label>Produit</label>
                                <select name="product_id" id="product_id" class="form-select">
                                    <option value="">Sélectionner un produit</option>
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Fournisseur</label>
                                <select name="supplier_id" id="supplier_id" class="form-select">
                                    <option value="">Aucun fournisseur</option>
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Quantité ajoutée</label>
                                <input type="number" name="qte_added" id="qte_added" placeholder="Quantité ajoutée" min="1">
                            </div>
                            <div class="col-md-12 saas-form-group mt-3">
                                <label>Note</label>
                                <textarea name="note" id="note" placeholder="Note facultative…"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                            <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…">
                                <i class="bi bi-check-lg" aria-hidden="true"></i><span>Valider l'entrée</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale Sortie --}}
    <div class="modal fade" id="removeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content saas-modal-content saas-modal-danger">
                <div class="modal-header" style="border-left: 4px solid var(--ds-danger, #FF626E);">
                    <div>
                        <p class="saas-modal-eyebrow" style="color: var(--ds-danger, #FF626E);">Inventaire</p>
                        <h3 class="modal-title">Retirer du stock</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="remove">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 saas-form-group">
                                <label>Produit</label>
                                <select name="product_id" id="product_id1" class="form-select">
                                    <option value="">Sélectionner un produit en stock</option>
                                </select>
                            </div>
                            <div class="col-md-6 saas-form-group">
                                <label>Quantité retirée</label>
                                <input type="number" name="qte_removed" id="qte_removed" placeholder="Quantité retirée" min="1">
                            </div>
                            <div class="col-md-12 saas-form-group mt-3">
                                <label>Note</label>
                                <textarea name="note" id="note" placeholder="Motif du retrait…"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                            <button type="submit" class="saas-btn saas-btn-danger" data-loading-text="Retrait en cours…">
                                <i class="bi bi-dash-lg" aria-hidden="true"></i><span>Valider la sortie</span>
                            </button>
                        </div>
                    </form>
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
                        <p class="saas-modal-eyebrow">Inventaire</p>
                        <h3 class="modal-title">Détail du mouvement</h3>
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
                <h2>Mouvements de stock</h2>
                <p class="saas-card-description">Historique complet des entrées et sorties avec traçabilité par produit et fournisseur.</p>
            </div>
        </div>

        <details class="saas-accordion">
            <summary><i class="bi bi-funnel"></i> Filtrer les inventaires</summary>
            <div class="saas-accordion-body">
                <div class="saas-filter-row">
                    <div class="saas-form-group">
                        <label>Type</label>
                        <select class="form-select" id="type">
                            <option value="">Tous les types</option>
                            <option value="1">Entrée</option>
                            <option value="2">Sortie</option>
                        </select>
                    </div>
                    <div class="saas-form-group">
                        <label>Produit</label>
                        <select class="form-select" id="filter_product">
                            <option value="">Tous les produits</option>
                        </select>
                    </div>
                    <div class="saas-form-group">
                        <label>Fournisseur</label>
                        <select class="form-select" id="filter_supplier">
                            <option value="">Tous les fournisseurs</option>
                        </select>
                    </div>
                    <div class="saas-daterangepicker-wrap">
                        <i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i>
                        <input type="text" id="inventoryDateRange" class="form-control" readonly placeholder="Période">
                        <input type="hidden" id="start_date">
                        <input type="hidden" id="end_date">
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
                    <button type="button" class="saas-btn saas-btn-secondary" id="exportPdf"><i class="bi bi-filetype-pdf"></i> PDF</button>
                </div>
            </div>
        </details>

        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Produit</th>
                        <th>Fournisseur</th>
                        <th>Qté avant</th>
                        <th>Qté saisie</th>
                        <th>Qté après</th>
                        <th>Créé par</th>
                        <th>Créé le</th>
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
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker"></script>
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
                ajax: {
                    url: "{{ route('inventory.index') }}",
                    data: function(d) {
                        d.type = $('#type').val();
                        d.product_id = $('#filter_product').val();
                        d.supplier_id = $('#filter_supplier').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'type', name: 'type' },
                    { data: 'product_id', name: 'product_id' },
                    { data: 'supplier_id', name: 'supplier_id' },
                    { data: 'qte_before', name: 'qte_before' },
                    { data: 'qte_added', name: 'qte_added' },
                    { data: 'qte_after', name: 'qte_after' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                responsive: true,
                language: {
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "zeroRecords": "Aucune donnée disponible",
                    "emptyTable": "Aucun mouvement de stock pour le moment",
                    "processing": "Chargement des mouvements…",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher :",
                    "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
                },
            });

            const productSearchUrl = @json(route('inventory.products.search'));
            const supplierSearchUrl = @json(route('inventory.suppliers.search'));

            function remoteSelect(selector, url, placeholder, options = {}) {
                const element = $(selector);
                element.select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    dropdownParent: options.dropdownParent ? $(options.dropdownParent) : undefined,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term || '',
                            page: params.page || 1,
                            ...(options.inStock ? { in_stock: 1 } : {})
                        }),
                        processResults: data => data,
                        cache: true
                    }
                });
            }

            remoteSelect('#product_id', productSearchUrl, 'Rechercher un produit', { dropdownParent: '#addModal' });
            remoteSelect('#supplier_id', supplierSearchUrl, 'Aucun fournisseur', { dropdownParent: '#addModal' });
            remoteSelect('#product_id1', productSearchUrl, 'Rechercher un produit en stock', { dropdownParent: '#removeModal', inStock: true });
            remoteSelect('#filter_product', productSearchUrl, 'Tous les produits');
            remoteSelect('#filter_supplier', supplierSearchUrl, 'Tous les fournisseurs');

            // Init daterangepicker for inventory
            var invDrp = $('#inventoryDateRange').daterangepicker({
                opens: 'right',
                alwaysShowCalendars: true,
                ranges: {
                    "Aujourd'hui": [moment(), moment()],
                    'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 derniers jours': [moment().subtract(6, 'days'), moment()],
                    '30 derniers jours': [moment().subtract(29, 'days'), moment()],
                    'Ce mois': [moment().startOf('month'), moment().endOf('month')],
                    'Mois passé': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'DD-MM-YYYY',
                    customRangeLabel: 'Plage personnalisée',
                    applyLabel: 'Appliquer',
                    cancelLabel: 'Annuler',
                    fromLabel: 'Du',
                    toLabel: 'Au',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.months(),
                    firstDay: 1
                }
            });
            invDrp.on('apply.daterangepicker', function(ev, picker) {
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                Datatable.draw();
            });
            invDrp.on('cancel.daterangepicker', function() {
                $('#start_date').val('');
                $('#end_date').val('');
                Datatable.draw();
            });

            $('#filter_product, #filter_supplier, #type').on('change', function() {
                Datatable.draw();
            });

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            $('#add').submit(function(event) {
                event.preventDefault();
                var form = this;
                var button = $(form).find('[type="submit"]');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('inventory.store') }}",
                    data: $(form).serialize(),
                    datatype: 'json',
                    beforeSend: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Enregistrement…');
                    },
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                            $('#addModal').modal('hide');
                            window.location.reload();
                        } else {
                            Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: "error", title: "Erreur", text: ajaxErrorMessage(xhr, "Impossible de communiquer avec le serveur."), timer: 3600 });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
                    }
                });
                return false;
            });

            $('#remove').submit(function(event) {
                event.preventDefault();
                var form = this;
                var button = $(form).find('[type="submit"]');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('inventory.remove') }}",
                    data: $(form).serialize(),
                    datatype: 'json',
                    beforeSend: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Retrait en cours…');
                    },
                    success: function(data) {
                        if (data.status) {
                            Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                            $('#removeModal').modal('hide');
                            window.location.reload();
                        } else {
                            Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: "error", title: "Erreur", text: ajaxErrorMessage(xhr, "Impossible de communiquer avec le serveur."), timer: 3600 });
                    },
                    complete: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
                    }
                });
                return false;
            });

            $('body').on('click', '.view', function() {
                const trigger = this;
                var id = $(trigger).data("id");
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(trigger, 'Chargement…');
                $('#show_response').empty();
                $('#showModal').modal('show');
                $.ajax({
                    url: '{{ url("component/inventory") }}/' + id,
                    dataType: 'html',
                    success: function(result) { $('#show_response').html(result); },
                    error: function(xhr) { $('#showModal').modal('hide'); Swal.fire({ icon: 'error', title: 'Chargement impossible', text: ajaxErrorMessage(xhr, 'Impossible de charger ce mouvement.') }); },
                    complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(trigger); }
                });
            });

            $('#exportPdf').on('click', function(e) {
                e.preventDefault();
                let params = $.param({
                    type: $('#type').val(),
                    product_id: $('#filter_product').val(),
                    supplier_id: $('#filter_supplier').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val()
                });
                window.open("{{ route('inventory.export.pdf') }}?" + params, '_blank');
            });

            $('.exportTabular').on('click', function(e) {
                e.preventDefault();
                const button = this;
                const params = $.param({
                    type: $('#type').val(),
                    product_id: $('#filter_product').val(),
                    supplier_id: $('#filter_supplier').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val()
                });
                const baseUrl = "{{ route('inventory.export.tabular', ['format' => '__FORMAT__']) }}";
                window.ServerButtonLoader.download(button, baseUrl.replace('__FORMAT__', button.dataset.format) + '?' + params)
                    .catch(error => Swal.fire({ icon: 'error', title: 'Export impossible', text: error.message }));
            });
        });
    </script>
    @endpush
@endsection
