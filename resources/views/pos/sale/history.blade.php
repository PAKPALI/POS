@extends('layouts.saas')
@section('title', 'Historique des ventes')
@push('styles')
<link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
<link href="{{ asset('hub/assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
<link href="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .sales-history-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
    .sales-history-top h1 { margin: 0; }
    .sales-export-actions { display: flex; gap: 10px; }
    .sales-top-thumb { position: relative; flex-shrink: 0; width: 56px; height: 56px; }
    .sales-top-thumb .saas-ranking-position { position: absolute; top: -5px; left: -5px; z-index: 1; }
    .sales-top-thumb .sales-top-sales-image { width: 56px; height: 56px; }
    .sales-top-sales-image { display: grid; place-items: center; object-fit: cover; background: var(--ds-bg-elevated); border: 1px solid var(--ds-border-soft); border-radius: 12px; }
    .sales-top-sales-image.is-placeholder { color: var(--ds-text-muted); }
    .sales-top-row { display: flex; gap: 14px; align-items: flex-start; }
    .sales-top-info { flex: 1; min-width: 0; }
    .sales-top-info strong { display: block; color: var(--ds-text-primary); font-size: .9rem; font-weight: 650; }
    .sales-top-info small { display: block; color: var(--ds-text-muted); font-size: .78rem; margin-top: 2px; }
    .sales-top-meta { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
    .sales-top-meta-item { display: flex; align-items: center; gap: 5px; font-size: .78rem; color: var(--ds-text-secondary); }
    .sales-top-meta-item strong { color: var(--ds-text-primary); font-variant-numeric: tabular-nums; }

    /* SweetAlert invoice delivery modal */
    .swal-country-list { max-height: 180px; overflow-y: auto; border: 1px solid var(--ds-border-soft); border-radius: 10px; background: var(--ds-bg-elevated); margin-bottom: 14px; }
    .swal-country-item { padding: 8px 14px; cursor: pointer; font-size: .82rem; color: var(--ds-text-secondary); border-bottom: 1px solid var(--ds-border-soft); transition: background .12s, color .12s; }
    .swal-country-item:last-child { border-bottom: none; }
    .swal-country-item:hover { background: rgba(255,255,255,.06); color: var(--ds-text-primary); }
    .swal-country-item.is-selected { background: var(--ds-accent-soft); color: var(--ds-accent); font-weight: 650; }
    .swal-country-item small { opacity: .55; font-weight: 400; }
    .swal-channels-row { display: flex; justify-content: center; gap: 20px; margin-top: 14px; }
    .swal-switch-label { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; padding: 6px 12px; border-radius: 10px; background: var(--ds-glass-1); border: 1px solid var(--ds-border-soft); }
    .swal-switch-label .saas-switch-control { flex: 0 0 40px; width: 40px; height: 22px; }
    .swal-switch-label .saas-switch-control::after { width: 16px; height: 16px; top: 3px; left: 3px; }
    .swal-switch-label .saas-switch-input:checked + .saas-switch-control::after { transform: translateX(18px); }
    .swal-switch-text { display: flex; align-items: center; gap: 5px; font-size: .82rem; color: var(--ds-text-primary); }
    .swal-switch-text small { color: var(--ds-text-muted); }
</style>
@endpush

@section('content')
    {{-- Page heading --}}
    <div class="saas-page-heading sales-history-top">
        <div>
            <h1>Historique des ventes</h1>
            <p style="color:var(--ds-text-muted);font-size:.85rem;margin-top:4px;">Analysez les ventes, filtrez les résultats et renvoyez les factures depuis un espace unifié.</p>
        </div>
        <a href="{{ route('sale.index') }}" class="saas-btn saas-btn-primary"><i class="bi bi-cart3"></i> Ouvrir le point de vente</a>
    </div>

    {{-- Metrics --}}
    <div class="sales-history-metric saas-metric-grid {{ $canViewFinancials ? '' : 'saas-metric-grid-3' }}">
        <article class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Ventes</span><span class="saas-metric-icon"><i class="bi bi-receipt"></i></span></div>
            <strong class="saas-metric-value" id="totalSale">0</strong>
        </article>
        <article class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Produits vendus</span><span class="saas-metric-icon"><i class="bi bi-box-seam"></i></span></div>
            <strong class="saas-metric-value" id="totalProduct">0</strong>
        </article>
        <article class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Chiffre d'affaires</span><span class="saas-metric-icon"><i class="bi bi-currency-exchange"></i></span></div>
            <strong class="saas-metric-value" id="totalAmount">0</strong>
        </article>
        @if ($canViewFinancials)
        <article class="saas-metric">
            <div class="saas-metric-head"><span class="saas-metric-label">Bénéfice</span><span class="saas-metric-icon"><i class="bi bi-graph-up-arrow"></i></span></div>
            <strong class="saas-metric-value" id="totalProfit">0</strong>
        </article>
        @endif
    </div>

    {{-- Filters --}}
    <details class="saas-accordion" open>
        <summary><i class="bi bi-funnel"></i> Filtrer les ventes et le classement des produits</summary>
        <div class="saas-accordion-body">
            <form id="searchForm" class="saas-filter-row">
                @csrf
                <div class="saas-form-group">
                    <label for="reportrange">Période</label>
                    <div class="saas-daterangepicker-wrap">
                        <i class="bi bi-calendar3 saas-dp-icon" aria-hidden="true"></i>
                        <input id="reportrange" type="text" class="form-control" readonly aria-label="Période de ventes">
                    </div>
                </div>
                <div class="saas-form-group">
                    <label for="historyClient">Client</label>
                    <select id="historyClient" class="form-select" data-placeholder="Tous les clients">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
                    </select>
                </div>
                <div class="saas-form-group">
                    <label for="historySupplier">Fournisseur</label>
                    <select id="historySupplier" class="form-select" data-placeholder="Tous les fournisseurs">
                        <option value="">Tous les fournisseurs</option>
                        @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach
                    </select>
                </div>
                <div class="saas-form-group" style="flex:0 0 auto;">
                    <button id="resetHistoryFilters" type="button" class="saas-btn saas-btn-ghost"><i class="bi bi-arrow-counterclockwise"></i> Réinitialiser</button>
                </div>
            </form>
            <p style="color:var(--ds-text-muted);font-size:.76rem;margin:10px 0 0;">Le tableau, les statistiques, les exports et le classement des produits utiliseront les mêmes critères.</p>
        </div>
    </details>

    {{-- Sales list --}}
    <section class="saas-card" style="margin-top:24px;">
        <div class="saas-card-head">
            <div><h2>Liste des ventes</h2><p class="saas-card-description">Ventes correspondant à la période et aux filtres sélectionnés.</p></div>
            <div class="sales-export-actions">
                <button class="saas-btn saas-btn-ghost" type="button" data-bs-toggle="collapse" data-bs-target="#exportCollapse" aria-expanded="false"><i class="bi bi-download"></i> Exporter</button>
            </div>
        </div>
        <div id="exportCollapse" class="collapse" style="padding:0 16px 12px;">
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="button" data-format="csv" class="exportSalesTabular saas-btn saas-btn-ghost"><i class="bi bi-filetype-csv"></i> CSV</button>
                <button type="button" data-format="excel" class="exportSalesTabular saas-btn saas-btn-ghost"><i class="bi bi-filetype-xlsx"></i> Excel</button>
                <button type="button" id="exportSalesPdf" class="saas-btn saas-btn-primary"><i class="bi bi-filetype-pdf"></i> PDF</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Chiffre d'affaires</th>
                        <th>Remise</th>
                        @if ($canViewFinancials)<th>Profit total</th>@endif
                        <th>Client</th>
                        <th>Date</th>
                        <th>Caissier</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    {{-- Top selling products --}}
    <section class="saas-card" id="mostSoldProductsSection" style="margin-top:24px;">
        <div class="saas-card-head">
            <div><h2 id="mostText">Produits les plus vendus</h2><p class="saas-card-description">Classement calculé avec les mêmes filtres que la liste des ventes.</p></div>
            <span class="saas-status-badge is-info">Top 10</span>
        </div>
        <div class="table-responsive">
            <table id="mostSoldProductsTable" class="saas-data-table">
                <thead>
                    <tr><th>Produit</th><th>Détails</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    {{-- Detail modal --}}
    <div class="modal fade saas-modal" id="showModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content saas-modal-content saas-modal-primary">
                <div class="modal-header saas-modal-header">
                    <div><span class="saas-modal-eyebrow">Vente</span><h3 class="modal-title" id="showModalTitle">Détail de la vente</h3></div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="modal-body saas-modal-body">
                    <div id="show_response"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('hub/assets/plugins/datatables.net/js/dataTables.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    $('#mostSoldProductsSection').hide();
    moment.locale('fr');

    var Datatable = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('history') }}",
            data: function(d) {
                d.daterange = $('#reportrange').val();
                d.client_id = $('#historyClient').val();
                d.supplier_id = $('#historySupplier').val();
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'code', name: 'code'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'discount', name: 'discount'},
            @if ($canViewFinancials)
            {data: 'total_profit', name: 'total_profit'},
            @endif
            {data: 'client', name: 'client'},
            {data: 'created_at', name: 'created_at'},
            {data: 'cashier', name: 'cashier'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        responsive: true,
        language: {
            "lengthMenu": "Afficher _MENU_ entrées",
            "zeroRecords": "Aucune donnée disponible",
            "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
            "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
            "search": "Rechercher:",
            "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
        },
        drawCallback: function(dataServer) {
            var json = dataServer.json;
            $('#totalSale').text(json.totalSale);
            $('#totalProduct').text(json.productCount);
            $('#totalAmount').text(json.totalAmount);
            @if ($canViewFinancials)
            $('#totalProfit').text(json.totalProfit);
            @endif

            if (json.mostSoldProducts !== undefined) {
                var countMostSoldProducts = json.mostSoldProducts.length;
                if (countMostSoldProducts > 0) {
                    $('#mostSoldProductsSection').fadeIn();
                } else {
                    $('#mostSoldProductsSection').fadeOut();
                }

                var mostSoldProductsContainer = $('#mostSoldProductsTable tbody').empty();

                json.mostSoldProducts.forEach(function(productDetail, index) {
                    var product = productDetail.product;
                    var imageHtml = product && product.image
                        ? '<img class="sales-top-sales-image" src="{{ asset("images") }}/' + encodeURIComponent(product.image) + '" alt="">'
                        : '<span class="sales-top-sales-image is-placeholder"><i class="bi bi-box-seam"></i></span>';
                    var price = product ? new Intl.NumberFormat('fr-FR').format(product.price_ttc && product.price_ttc > 0 ? product.price_ttc : product.price) + ' FCFA' : 'Référence indisponible';
                    var name = product ? product.name : 'Produit supprimé';

                    mostSoldProductsContainer.append(
                        '<tr><td><div class="sales-top-row">' +
                        '<div class="sales-top-thumb"><span class="saas-ranking-position">' + (index + 1) + '</span>' + imageHtml + '</div>' +
                        '<div class="sales-top-info"><strong>' + name + '</strong>' +
                        '<small>' + price + '</small></div>' +
                        '</div></td><td><span class="saas-status-badge is-success">' + productDetail.total_quantity + ' vendu(s)</span></td></tr>'
                    );
                });
            }
        },
    });

    // Auto-reload DataTable with current filters
    function reloadSalesData() {
        var daterange = $('#reportrange').val();
        if (daterange) {
            var dates = daterange.split(' - ');
            var date1 = moment(dates[0], 'DD-MM-YYYY');
            var date2 = moment(dates[1], 'DD-MM-YYYY');
            if (date1.isAfter(date2)) {
                Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Erreur de date', showConfirmButton: false, timer: 5000, timerProgressBar: true, text: 'La date de début doit être inférieure ou égale à la date de fin !' });
                return;
            }
        }
        Datatable.ajax.reload(null, true);
    }

    // PDF export
    $('#exportSalesPdf').on('click', function() {
        var tableInfo = Datatable.page.info();
        if (!tableInfo || tableInfo.recordsDisplay === 0) {
            Swal.fire({ icon: 'info', title: 'Aucune donnée à exporter', text: 'Aucune vente ne correspond aux filtres sélectionnés.', confirmButtonText: "D'accord", confirmButtonColor: '#0dcaf0' });
            return;
        }
        var params = new URLSearchParams({
            daterange: $('#reportrange').val(),
            client_id: $('#historyClient').val(),
            supplier_id: $('#historySupplier').val(),
            search: Datatable.search()
        });
        window.location.href = "{{ route('history.export.pdf') }}?" + params.toString();
    });

    // CSV/Excel export
    $('.exportSalesTabular').on('click', function() {
        var button = this;
        var params = new URLSearchParams({
            daterange: $('#reportrange').val(),
            client_id: $('#historyClient').val(),
            supplier_id: $('#historySupplier').val(),
            search: Datatable.search()
        });
        var baseUrl = "{{ route('history.export.tabular', ['format' => '__FORMAT__']) }}";
        window.ServerButtonLoader.download(button, baseUrl.replace('__FORMAT__', button.dataset.format) + '?' + params.toString())
            .catch(function(error) { Swal.fire({ icon: 'error', title: 'Export impossible', text: error.message }); });
    });

    // Daterangepicker
    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
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
            cancelLabel: 'Effacer',
            fromLabel: 'Du',
            toLabel: 'Au',
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        }
    }, function (pickStart, pickEnd) {
        cb(pickStart, pickEnd);
        reloadSalesData();
    });
    cb(start, end);

    // Auto-reload on select change
    $('#historyClient, #historySupplier').on('change', function () {
        reloadSalesData();
    });

    // Reset filters
    $('#resetHistoryFilters').on('click', function() {
        $('#historyClient, #historySupplier').val('').trigger('change');
        var picker = $('#reportrange').data('daterangepicker');
        picker.setStartDate(moment().subtract(29, 'days'));
        picker.setEndDate(moment());
        cb(moment().subtract(29, 'days'), moment());
        reloadSalesData();
    });

    // View sale detail
    $('body').on('click', '.view', function() {
        var id = $(this).data("id");
        $.ajax({
            url: '{{ url("pos/sale") }}/' + id,
            dataType: 'html',
            success: function(result) { $('#show_response').html(result); }
        });
        $('#showModal').modal('show');
    });

    // PDF invoice
    $('body').on('click', '.pdf', function() {
        var id = $(this).data("id");
        window.location.href = 'sale/invoice/' + id + '/pdf';
    });

    // Invoice delivery
    var invoiceWhatsappQuota = {{ (int) ($company?->whatsapp_count ?? 0) }};
    var invoiceSmsQuota = {{ (int) ($company?->sms_count ?? 0) }};
    var invoiceWhatsappAuthorized = {{ $company?->invoice_whatsapp_enabled ? 'true' : 'false' }};
    var invoiceSmsAuthorized = {{ $company?->invoice_sms_enabled ? 'true' : 'false' }};

    $('body').on('click', '.deliver-invoice', function() {
        var saleId = $(this).data('id');
        var clientPhone = $(this).data('phone') || '';
        var clientCountry = $(this).data('country') || '{{ $company->country_code ?? 'TG' }}';
        var countries = @json(collect(config('african_countries'))->map(fn($name, $iso) => ['iso' => $iso, 'name' => $name])->values());
        var countryListHtml = countries.map(function(c) {
            var sel = c.iso === clientCountry ? ' is-selected' : '';
            return '<div class="swal-country-item' + sel + '" data-iso="' + c.iso + '">' + c.name + ' <small>(' + c.iso + ')</small></div>';
        }).join('');
        Swal.fire({
            title: 'Envoyer la facture',
            html:
                '<input id="deliveryPhone" type="tel" inputmode="numeric" minlength="6" maxlength="15" class="swal2-input" value="' + String(clientPhone).replace(/"/g, '&quot;') + '" placeholder="Numéro local sans indicatif">' +
                '<input id="deliveryCountrySearch" type="text" class="swal2-input" placeholder="Rechercher un pays..." autocomplete="off" style="margin-bottom:6px;font-size:.9rem;">' +
                '<div id="deliveryCountryList" class="swal-country-list">' + countryListHtml + '</div>' +
                '<input type="hidden" id="deliveryCountry" value="' + clientCountry + '">' +
                '<div class="swal-channels-row">' +
                '<label class="swal-switch-label">' +
                    '<input type="checkbox" id="deliveryWhatsapp" class="saas-switch-input" role="switch" ' + (invoiceWhatsappAuthorized && invoiceWhatsappQuota > 0 ? 'checked' : 'disabled') + '>' +
                    '<span class="saas-switch-control"></span>' +
                    '<span class="swal-switch-text"><i class="bi bi-whatsapp"></i> WhatsApp <small>(' + invoiceWhatsappQuota + ')</small></span>' +
                '</label>' +
                '<label class="swal-switch-label">' +
                    '<input type="checkbox" id="deliverySms" class="saas-switch-input" role="switch" ' + (invoiceSmsAuthorized && invoiceSmsQuota > 0 ? '' : 'disabled') + '>' +
                    '<span class="saas-switch-control"></span>' +
                    '<span class="swal-switch-text"><i class="bi bi-chat-text"></i> SMS <small>(' + invoiceSmsQuota + ')</small></span>' +
                '</label>' +
                '</div>' +
                (!invoiceWhatsappAuthorized && !invoiceSmsAuthorized ? '<div class="small text-warning mt-3">Activez WhatsApp ou SMS dans la section « Envoi des factures aux clients » de Communications &gt; SMS &amp; WhatsApp &gt; Configuration.@if($currentMembership?->hasPermission("notifications.manage")) <a href="{{ route("notifications.index") }}" class="text-warning text-decoration-underline">Ouvrir la configuration</a>@endif</div>' : ''),
            showCancelButton: true,
            confirmButtonText: 'Envoyer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            allowEscapeKey: function() { return !Swal.isLoading(); },
            didOpen: function() {
                var list = document.getElementById('deliveryCountryList');
                var search = document.getElementById('deliveryCountrySearch');
                var hidden = document.getElementById('deliveryCountry');
                if (list) {
                    list.addEventListener('click', function(e) {
                        var item = e.target.closest('.swal-country-item');
                        if (!item) return;
                        list.querySelectorAll('.swal-country-item').forEach(function(el) { el.classList.remove('is-selected'); });
                        item.classList.add('is-selected');
                        hidden.value = item.dataset.iso;
                    });
                }
                if (search && list) {
                    search.addEventListener('input', function() {
                        var q = this.value.toLowerCase();
                        list.querySelectorAll('.swal-country-item').forEach(function(el) {
                            el.style.display = el.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
                        });
                    });
                    search.focus();
                }
            },
            preConfirm: async function() {
                var phone = document.getElementById('deliveryPhone').value.trim();
                var country_code = document.getElementById('deliveryCountry').value;
                var whatsapp = document.getElementById('deliveryWhatsapp').checked;
                var sms = document.getElementById('deliverySms').checked;
                if (!phone || (!whatsapp && !sms)) {
                    Swal.showValidationMessage('Saisissez un numéro et choisissez au moins un canal.');
                    return false;
                }
                try {
                    var url = '{{ route("sale.send-invoice", ["sale" => "__SALE__"]) }}'.replace('__SALE__', saleId);
                    var response = await fetch(url, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({phone: phone, country_code: country_code, whatsapp: whatsapp, sms: sms})
                    });
                    var data = await response.json();
                    invoiceWhatsappQuota = Number(data.whatsappQuota ?? invoiceWhatsappQuota);
                    invoiceSmsQuota = Number(data.smsQuota ?? invoiceSmsQuota);
                    if (!response.ok || !data.status) throw new Error(data.message || 'Envoi impossible.');
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(error.message || 'Envoi impossible.');
                    return false;
                }
            }
        }).then(function(result) {
            if (result.isConfirmed) Swal.fire({ icon: 'success', title: 'Facture envoyée', text: result.value.message });
        });
    });
});
</script>
@endpush
