@extends('layouts.layout')
@push('css-scripts')
<style>
    #managers-table tbody tr { background-color: #f0f0f0; }
    #managers-table tbody tr:hover { background-color: #e0e0e0; }
    .current-logo { max-height: 80px; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Ecommerce</li>
            </ul>
            <h1 class="page-header">Configuration Ecommerce</h1>
            <hr class="mb-4">

            @if(!$company)
                <div class="alert alert-warning">
                    Aucune compagnie configuree. Veuillez d'abord configurer la compagnie dans
                    <a href="{{ route('company.index') }}">Parametres > Compagnie</a>.
                </div>
            @else
            <div class="row">
                <div class="col-xl-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title">Informations boutique</h4>
                            <form id="settingsForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Nom du site</label>
                                    <input type="text" class="form-control" id="site_name" value="{{ $company->name }}" readonly>
                                    <small class="text-muted">Utilise le nom de la compagnie</small>
                                </div>

                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" name="logo" class="form-control" id="logo" accept="image/*">
                                    @if($company->logo)
                                        <div class="mt-2">
                                            <img src="{{ asset($company->logo) }}" alt="Logo" class="current-logo">
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" class="form-control" id="description" rows="4" placeholder="Decrivez ce que l'entreprise propose comme solutions...">{{ $company->description }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="ecommerce_active" class="form-check-input" id="ecommerce_active" value="1" {{ $company->ecommerce_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ecommerce_active">Boutique en ligne active</label>
                                    </div>
                                    @if($company->ecommerce_active)
                                        <small class="text-muted">
                                            Votre boutique est accessible sur :
                                            <a href="{{ url('/shop') }}" target="_blank">{{ url('/shop') }}</a>
                                        </small>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <div id="settingsLoader" class="spinner-grow spinner-grow-sm" style="display:none;"></div>
                                    <span id="settingsSubmitText">Enregistrer</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title">Managers de la boutique</h4>
                            <p class="card-text">Les managers recus les notifications des nouvelles commandes.</p>

                            <form id="addManagerForm" class="mb-3">
                                @csrf
                                <input type="hidden" name="company_id" value="{{ $company->id }}">
                                <div class="input-group">
                                    <select name="user_id" class="form-select" required>
                                        <option value="">Selectionner un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-success">Ajouter</button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table id="managers-table" class="table text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="{{asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')}}"></script>
<script src="{{asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>

<script>
$(function() {
    $('#settingsLoader').hide();

    @if($company)
    var managersTable = $('#managers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('ecommerce.managers.list', $company->id) }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'user_name', name: 'user_name'},
            {data: 'user_email', name: 'user_email'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        language: {
            lengthMenu: "Afficher _MENU_ entrees",
            zeroRecords: "Aucun manager",
            info: "Affichage de _START_ a _END_ sur _TOTAL_ entrees",
            infoEmpty: "Affichage de 0 a 0 sur 0 entrees",
            search: "Rechercher:",
            paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Precedent" }
        },
    });

    $('#settingsForm').submit(function(e) {
        e.preventDefault();
        $('#settingsLoader').fadeIn();
        $('#settingsSubmitText').hide();

        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: "{{ route('ecommerce.settings.update') }}",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                $('#settingsLoader').hide();
                $('#settingsSubmitText').fadeIn();
                Swal.fire({
                    toast: true, position: 'top', icon: data.status ? 'success' : 'error',
                    title: data.title, showConfirmButton: false, timer: 3000, text: data.msg
                });
            },
            error: function() {
                $('#settingsLoader').hide();
                $('#settingsSubmitText').fadeIn();
                Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Erreur',
                    text: 'Impossible de communiquer avec le serveur.', showConfirmButton: false, timer: 3000 });
            }
        });
    });

    $('#addManagerForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: "{{ route('ecommerce.managers.add') }}",
            data: formData,
            success: function(data) {
                Swal.fire({
                    toast: true, position: 'top', icon: data.status ? 'success' : 'error',
                    title: data.title, showConfirmButton: false, timer: 3000, text: data.msg
                });
                if (data.status) {
                    managersTable.ajax.reload();
                    $('#addManagerForm')[0].reset();
                }
            }
        });
    });

    $(document).on('click', '.remove-manager', function() {
        var id = $(this).data('id');
        Swal.fire({
            icon: 'question', title: 'Retirer ce manager?',
            showCancelButton: true, confirmButtonText: 'Oui', cancelButtonText: 'Non'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: "{{ url('ecommerce/managers') }}/" + id,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        Swal.fire({ toast: true, position: 'top', icon: 'success',
                            title: data.title, showConfirmButton: false, timer: 3000 });
                        managersTable.ajax.reload();
                    }
                });
            }
        });
    });
    @endif
});
</script>
@endsection
