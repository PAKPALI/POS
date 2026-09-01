@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Commandes en ligne</h1>
            <p>Suivez les commandes reçues de la boutique publique, validez ou annulez.</p>
        </div>
    </div>

    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Commandes</h2>
                <p class="saas-card-description">Historique complet des commandes avec statuts et actions.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="orders-table" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
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
        const ordersTable = $('#orders-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ecommerce.orders.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'code', name: 'code'},
                {data: 'customer_name', name: 'customer_name'},
                {data: 'customer_phone', name: 'customer_phone'},
                {data: 'total', name: 'total'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [[6, 'desc']],
            responsive: true,
            language: {
                "lengthMenu": "Afficher _MENU_ entrées",
                "zeroRecords": "Aucune commande",
                "emptyTable": "Aucune commande reçue pour le moment",
                "processing": "Chargement des commandes…",
                "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                "search": "Rechercher :",
                "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
            },
        });

        $(document).on('click', '.execute-order', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            Swal.fire({
                icon: 'question', title: 'Passer cette commande en vente ?',
                html: '<p class="saas-confirm-copy">La vente sera créée pour <strong>' + code + '</strong>. Le stock et les caisses seront mis à jour.</p>',
                showCancelButton: true, confirmButtonText: 'Oui, créer la vente', cancelButtonText: 'Annuler',
                buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
                preConfirm: function() {
                    return $.post("{{ url('ecommerce/orders') }}/" + id + '/execute', {_token: "{{ csrf_token() }}"})
                        .catch(function(xhr) { Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible de transformer cette commande en vente.'); return false; });
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    Swal.fire({icon: 'success', title: result.value.title, text: result.value.msg})
                        .then(() => ordersTable.ajax.reload(null, false));
                }
            });
        });

        $(document).on('click', '.cancel-order', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            Swal.fire({
                icon: 'warning', title: 'Annuler la commande ' + code + ' ?',
                input: 'textarea', inputLabel: 'Motif de l\'annulation', inputPlaceholder: 'Le client n\'a pas confirmé…',
                inputAttributes: {maxlength: 500},
                inputValidator: value => !value?.trim() ? 'Indiquez le motif de l\'annulation.' : undefined,
                showCancelButton: true, confirmButtonText: 'Oui, annuler', cancelButtonText: 'Retour',
                confirmButtonColor: '#dc3545', showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
                preConfirm: function(reason) {
                    return $.post("{{ url('ecommerce/orders') }}/" + id + '/cancel', {_token: "{{ csrf_token() }}", reason: reason})
                        .catch(function(xhr) { Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible d\'annuler cette commande.'); return false; });
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    Swal.fire({icon: 'success', title: result.value.title, text: result.value.msg})
                        .then(() => ordersTable.ajax.reload(null, false));
                }
            });
        });
    });
    </script>
    @endpush
@endsection
