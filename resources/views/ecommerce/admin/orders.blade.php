@extends('layouts.layout')
@push('css-scripts')
<style>
    #orders-table tbody tr { background-color: #f0f0f0; }
    #orders-table tbody tr:hover { background-color: #e0e0e0; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <!-- <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Commandes ecommerce</li>
            </ul> -->
            <h1 class="page-header">Commandes en ligne</h1>
            <hr class="mb-4">

            <div class="card mb-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="orders-table" class="table text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Client</th>
                                    <th>Telephone</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')}}"></script>
<script src="{{asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>

<script>
$(function() {
    $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('ecommerce.orders.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'code', name: 'code'},
            {data: 'customer_name', name: 'customer_name'},
            {data: 'customer_phone', name: 'customer_phone'},
            {data: 'total', name: 'total'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [[6, 'desc']],
        language: {
            lengthMenu: "Afficher _MENU_ entrees",
            zeroRecords: "Aucune commande",
            info: "Affichage de _START_ a _END_ sur _TOTAL_ entrees",
            infoEmpty: "Affichage de 0 a 0 sur 0 entrees",
            search: "Rechercher:",
            paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Precedent" }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            $('#orders-table').css('width','100%');
            $('#orders-table tbody tr').each(function() {
                $(this).css('background-color', 'black');  // Appliquer un fond personnalisé
                $(this).css('color', 'white');
            });
            $('.dataTables_info, .dataTables_paginate').css('color', 'white');
            $('.dataTables_paginate .paginate_button a').css('color', 'white');
            $('.dataTables_length select option').css('color', 'black'); // Mettre la couleur noire pour les options
            $('.dataTables_length select option').css('background-color', 'white'); // Fond blanc pour les options

            // Appliquer la couleur blanche au texte des labels
            $('.dataTables_length label').css('color', 'white'); // Couleur blanche pour "Afficher _MENU_ entrées"
            $('.dataTables_filter label').css('color', 'white'); // Couleur blanche pour "Rechercher:"
            
            // Appliquer les styles pour le dropdown et le champ de recherche
            $('.dataTables_length select').css({
                'background-color': 'black', // Fond noir
                'color': 'white' // Texte en blanc
            });

            $('.dataTables_filter input').css({
                'background-color': 'black', // Fond noir
                'color': 'white' // Texte en blanc
            });
            $('.dataTables_filter input::placeholder').css('color', 'white'); // Placeholder en blanc
            $('#orders-table').css('width', '100%');
        },
    });
});
</script>
@endsection
