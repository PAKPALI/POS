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
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Commandes ecommerce</li>
            </ul>
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
    });
});
</script>
@endsection
