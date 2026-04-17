@extends('layouts.layout')

@push('css-scripts')
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }

    .badge-warning {
        background: #ffc107;
        color: #000;
    }

    .blink-badge {
        animation: glowBlink 1.5s infinite;
        font-weight: bold;
        padding: 6px 10px;
        border-radius: 10px;
    }

    /* effet lumineux */
    @keyframes glowBlink {
        0% {
            box-shadow: 0 0 5px #ffc107;
            opacity: 1;
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 20px #ffc107, 0 0 30px #ffdb58;
            opacity: 0.85;
            transform: scale(1.05);
        }
        100% {
            box-shadow: 0 0 5px #ffc107;
            opacity: 1;
            transform: scale(1);
        }
    }

    .card-white-shadow {
        background: #1e1e2f; /* optionnel si fond sombre */
        box-shadow: 0 0 18px rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    .card-white-shadow:hover {
        box-shadow: 0 0 30px rgba(255, 255, 255, 0.35);
        transform: translateY(-3px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- TOTAL TRANSACTIONS -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-color mb-3 card-white-shadow">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">TOTAL OPERATIONS</span>
                    </div>

                    <div class="row align-items-center mb-2">
                        <div class="col-7">
                            <h3 class="mb-0">{{ $totalTransactions->count }}</h3>
                            <span class="badge blink-badge">
                                {{ number_format($totalTransactions->total, 0, ',', ' ') }} F CFA
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ENTRÉES -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-color mb-3 card-white-shadow">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">ENTRÉES</span>
                    </div>

                    <div class="row align-items-center mb-2">
                        <div class="col-7">
                            <h3 class="mb-0">{{ $inTransactions->count }}</h3>
                            <span class="badge blink-badge">
                                {{ number_format($inTransactions->total, 0, ',', ' ') }} F CFA
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- SORTIES -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-color mb-3 card-white-shadow">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">SORTIES</span>
                    </div>

                    <div class="row align-items-center mb-2">
                        <div class="col-7">
                            <h3 class="mb-0">{{ $outTransactions->count }}</h3>
                            <span class="badge blink-badge">
                                {{ number_format($outTransactions->total, 0, ',', ' ') }} F CFA
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- TRANSFERTS -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-color mb-3 card-white-shadow">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">TRANSFERTS</span>
                    </div>

                    <div class="row align-items-center mb-2">
                        <div class="col-7">
                            <h3 class="mb-0">{{ $transferTransactions->count }}</h3>
                            <span class="badge blink-badge">
                                {{ number_format($transferTransactions->total, 0, ',', ' ') }} F CFA
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 text-center">
            <div class="card border-color mb-3 card-white-shadow">
                <div class="card-body">
                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">BALANCE NETTE (Entrees vs Sorties)</span>
                    </div>

                    <div class="row align-items-center mb-2">
                        <div class="col-12">
                            <h3 class="mb-0">
                                {{ number_format($netBalance, 0, ',', ' ') }} F CFA
                            </h3>

                            @if($netBalance >= 0)
                                <span class="badge bg-success">
                                    POSITIVE
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    NEGATIVE
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-12">
            <h1 class="page-header">OPERATIONS</h1>
            <hr class="mb-4">

            <!-- ADD MODAL -->
            <div class="modal fade" id="addModal">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <h3 class="modal-title">Nouvelle opération</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <form id="add">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Type</label>
                                        <select name="type" id="type" class="form-select" required>
                                            <option value="">-- Choisir --</option>
                                            <option value="IN">Entrée</option>
                                            <option value="OUT">Sortie</option>
                                            <option value="TRANSFER">Transfert</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mt-3" id="from_cash_div">
                                        <label>Caisse source</label>
                                        <select name="from_cash_id" class="form-select">
                                            <option value="">-- Choisir --</option>
                                            @foreach($cashes as $cash)
                                                <option value="{{ $cash->id }}">{{ $cash->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mt-3" id="to_cash_div">
                                        <label>Caisse destination</label>
                                        <select name="to_cash_id" class="form-select">
                                            <option value="">-- Choisir --</option>
                                            @foreach($cashes as $cash)
                                                <option value="{{ $cash->id }}">{{ $cash->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mt-3">
                                        <label>Montant</label>
                                        <input type="number" name="amount" class="form-control" required>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="mt-4 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <div id="loader" class="spinner-grow"></div>
                                        <span id="submitText">Valider</span>
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SHOW MODAL -->
            <div class="modal fade" id="showModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h3 class="modal-title">Détail transaction</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="show_response"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="card">
                <div class="card-body">

                    <div class="d-flex mb-3">
                        <h4 class="flex-grow-1">Liste des opérations</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            Ajouter
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>Destination</th>
                                    <th>Montant</th>
                                    <!-- <th>Description</th> -->
                                    <th>Utilisateur</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="{{asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')}}"></script>
<script src="{{asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>

<script>
    $(function(){

        $('#loader').hide();

        // DATATABLE
        var Datatable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('transaction.index') }}",
            columns: [
                {data: 'id'},
                {data: 'type'},
                {data: 'from_cash'},
                {data: 'to_cash'},
                {data: 'amount'},
                // {data: 'description'},
                {data: 'created_by'},
                {data: 'created_at'},
                {data: 'action', orderable: false, searchable: false},
            ],
            responsive: true,
            language: {
                "lengthMenu": "Afficher _MENU_ entrées",
                "zeroRecords": "Aucune donnée disponible",
                "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                "search": "Rechercher:",
                "paginate": {
                    "first": "Premier",
                    "last": "Dernier",
                    "next": "Suivant",
                    "previous": "Précédent"
                }
            },
            
            drawCallback: function() {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                $('#datatable').css('width','100%');
                $('#datatable tbody tr').each(function() {
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
                $('#datatable').css('width', '100%');
            },
        });

        // SHOW / HIDE FIELDS
        $('#type').change(function(){
            let type = $(this).val();

            if(type === 'IN'){
                $('#from_cash_div').hide();
                $('#to_cash_div').show();
            }
            else if(type === 'OUT'){
                $('#from_cash_div').show();
                $('#to_cash_div').hide();
            }
            else{
                $('#from_cash_div').show();
                $('#to_cash_div').show();
            }
        });

        // DEFAULT HIDE
        $('#from_cash_div').hide();
        $('#to_cash_div').hide();

        // ADD TRANSACTION
        $('#add').submit(function(e){
            e.preventDefault();

            $('#loader').show();
            $('#submitText').hide();

            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: "{{ route('transaction.store') }}",
                data: formData,
                processData: false,
                contentType: false,

                success: function(data){
                    $('#loader').hide();
                    $('#submitText').show();

                    if(data.status){
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: "success",
                            title: "Succès",
                            text: data.msg,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#addModal').modal('hide');
                        $('#add')[0].reset();
                        Datatable.draw();

                    }else{
                        Swal.fire({
                            icon: "error",
                            title: "Erreur",
                            text: data.msg
                        });
                    }
                },

                error: function(){
                    $('#loader').hide();
                    $('#submitText').show();

                    Swal.fire({
                        icon: "error",
                        title: "Erreur serveur"
                    });
                }
            });
        });

        $('body').on('click', '.view', function () {
            var id = $(this).data("id");

            $.ajax({
                url: '/ams/transaction/' + id,
                dataType: 'html',
                success: function(result){
                    $('#show_response').html(result);
                }
            });

            $('#showModal').modal('show');
        });

    });
</script>

@endsection