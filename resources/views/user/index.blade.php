@extends('layouts.layout')
@push('css-scripts')
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-xl-12">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            UTILISATEURS
                        </h1>
                        <hr class="mb-4">
                        <!-- add modal -->
                        <div class="modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h3 class="modal-title">Ajouter utilisateur</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label for="exampleInputText0">Nom</label>
                                                    <input type="text" name="name" class="form-control" id="exampleInputText0" placeholder="Nom">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="exampleInputText0">Email</label>
                                                    <input type="email" name="email" class="form-control" id="exampleInputText0" placeholder="Email">
                                                </div>
                                                <div class="form-group col-6 mt-3">
                                                    <label for="phone">Numéro de téléphone</label>
                                                    <input type="number" name="phone" class="form-control" id="phone" value="" placeholder="ex: 90859488">
                                                </div>
                                                <div class="form-group col-6 mt-3">
                                                    <label for="role_id">Rôle dans cette compagnie</label>
                                                    <select class="form-select" name="role_id" id="role_id" required>
                                                        <option value="">Sélectionnez un rôle</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <div id="loader" class="spinner-grow"></div>
                                                <div id="submitText">Valider</div>
                                            </button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="inviteUserModal">
                            <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
                                <div class="modal-header bg-primary"><h3 class="modal-title"><i class="bi bi-envelope-plus me-2"></i>Inviter par e-mail</h3><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form id="inviteUserForm">@csrf<div class="modal-body">
                                    <div class="alert alert-info">L’accès ne sera créé qu’après acceptation. Le lien sécurisé est valable 48 heures et ne peut être utilisé qu’une fois.</div>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Adresse e-mail</label><input type="email" name="email" class="form-control" required></div>
                                        <div class="col-md-6"><label class="form-label">Rôle proposé</label><select name="role_id" id="invitation_role" class="form-select" required><option value="">Sélectionnez un rôle</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
                                    </div>
                                </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary"><span id="inviteUserLoader" class="spinner-border spinner-border-sm me-1 d-none"></span>Envoyer l’invitation</button></div></form>
                            </div></div>
                        </div>

                        <!-- rattacher un compte déjà existant -->
                        <div class="modal fade" id="attachExistingModal">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-success">
                                        <h3 class="modal-title">Ajouter un utilisateur existant</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            Utilisez l’e-mail exact du compte. L’utilisateur conservera ses accès dans ses autres compagnies et pourra basculer entre elles.
                                        </div>
                                        <form id="attachExistingForm">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="existing_user_email" class="form-label">E-mail du compte existant</label>
                                                    <input type="email" name="email" id="existing_user_email" class="form-control" required placeholder="utilisateur@exemple.com">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="existing_user_role" class="form-label">Rôle dans cette compagnie</label>
                                                    <select name="role_id" id="existing_user_role" class="form-select" data-placeholder="Rechercher un rôle" required>
                                                        <option value="">Sélectionnez un rôle</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2 mt-4">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-success">
                                                    <span id="attachExistingLoader" class="spinner-border spinner-border-sm me-1" style="display:none"></span>
                                                    Rattacher à cette compagnie
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- update modal -->
                        <div class="modal fade" id="cloneUserModal" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-dark">
                                        <h3 class="modal-title"><i class="fas fa-clone me-2"></i>Intégrer dans une compagnie</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form id="cloneUserForm">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                Vous allez donner à <strong id="cloneUserName"></strong> un accès supplémentaire. Son accès actuel sera conservé.
                                            </div>
                                            <div class="mb-3">
                                                <label for="cloneCompany" class="form-label">Compagnie cible</label>
                                                <select id="cloneCompany" name="company_id" class="form-select" required></select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="cloneRole" class="form-label">Rôle dans cette compagnie</label>
                                                <select id="cloneRole" name="role_id" class="form-select" required disabled></select>
                                                <div class="form-text">Le rôle ne prendra effet qu’après votre confirmation.</div>
                                            </div>
                                            <div id="cloneNoCompany" class="alert alert-warning d-none">
                                                Aucune autre compagnie dans laquelle vous pouvez gérer les utilisateurs n’est disponible.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" id="cloneSubmit" class="btn btn-info" disabled>
                                                <span id="cloneLoader" class="spinner-border spinner-border-sm me-1 d-none"></span>
                                                Vérifier et approuver
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- update modal -->
                        <div class="modal fade" id="editModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h3 class="modal-title text-dark ">Modifier utilisateur</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="edit_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- admin list -->
                        <div class="col-xl-12">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex fw-bold small mb-3">
                                        <span class="flex-grow-1"><h4>Listes des utilisateurs</h4></span>
                                        <button type="button" class="btn btn-outline-primary mb-1 me-2" data-bs-toggle="modal" data-bs-target="#inviteUserModal"><i class="bi bi-envelope-plus me-1"></i>Inviter par e-mail</button>
                                        <button type="button" class="btn btn-success mb-1 me-3" data-bs-toggle="modal" data-bs-target="#attachExistingModal">Ajouter un utilisateur existant</button>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="datatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nom</th>
                                                    <th>Email</th>
                                                    <th>Numéro</th>
                                                    <th>Rôle</th>
                                                    <th>Statut</th>
                                                    <th>Créer le</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center mt-3"></div>

                                <div class="card-arrow">
                                    <div class="card-arrow-top-left"></div>
                                    <div class="card-arrow-top-right"></div>
                                    <div class="card-arrow-bottom-left"></div>
                                    <div class="card-arrow-bottom-right"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12"><div class="card mb-3"><div class="card-body">
                            <h4>Invitations</h4><p class="text-muted">Suivez les invitations, renvoyez un lien ou révoquez un accès en attente.</p>
                            <div class="table-responsive"><table class="table table-striped align-middle">
                                <thead><tr><th>E-mail</th><th>Rôle</th><th>Statut</th><th>Expiration</th><th>Invité par</th><th>Actions</th></tr></thead><tbody>
                                @forelse($invitations as $invitation)
                                <tr><td>{{ $invitation->email }}</td><td>{{ $invitation->role?->name ?? 'Rôle supprimé' }}</td>
                                    <td><span class="badge bg-{{ $invitation->status_badge_class }}">{{ $invitation->status_label }}</span></td>
                                    <td>{{ $invitation->expires_at->format('d/m/Y H:i') }}</td><td>{{ $invitation->inviter?->name ?? 'Système' }}</td><td>
                                    @if(!$invitation->accepted_at && !$invitation->declined_at && !$invitation->revoked_at)
                                        <button class="btn btn-info btn-sm resendInvitation" data-id="{{ $invitation->id }}" data-email="{{ $invitation->email }}" title="Renvoyer"><i class="bi bi-send"></i></button>
                                        <button class="btn btn-danger btn-sm revokeInvitation" data-id="{{ $invitation->id }}" data-email="{{ $invitation->email }}" title="Révoquer"><i class="bi bi-x-circle"></i></button>
                                    @else — @endif</td></tr>
                                @empty<tr><td colspan="6" class="text-center text-muted">Aucune invitation envoyée.</td></tr>@endforelse
                                </tbody></table></div>
                        </div></div></div>
                        <!-- employe list -->
                        <!-- <div class="col-xl-12">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex fw-bold small mb-3">
                                        <span class="flex-grow-1"><h4>Listes des employés</h4></span>
                                        <button type="button" class="btn btn-primary mb-1 me-3 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="employeDatatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nom</th>
                                                    <th>Email</th>
                                                    <th>Statut</th>
                                                    <th>Créer le</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center mt-3"></div>

                                <div class="card-arrow">
                                    <div class="card-arrow-top-left"></div>
                                    <div class="card-arrow-top-right"></div>
                                    <div class="card-arrow-bottom-left"></div>
                                    <div class="card-arrow-bottom-right"></div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                    {{-- <div class="col-xl-2">
                        <nav id="sidebar-bootstrap" class="navbar navbar-sticky d-none d-xl-block">
                            <nav class="nav">
                                <a class="nav-link text-danger" href="#datatable" data-toggle="scroll-to"><strong> Lux Grill</strong></a>
                                <!-- <a class="nav-link text-danger" href="#bootstrapTable" data-toggle="scroll-to">GRILL</a> -->
                            </nav>
                        </nav>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.flash.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.html5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.print.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/bootstrap-table/dist/bootstrap-table.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/js/demo/table-plugins.demo.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/js/demo/sidebar-scrollspy.demo.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>

    <script>
        $(function() {
            // hide loader
            $('#loader').hide();
            $('.pre_loader').hide();

            // admin datatable
            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user.index')}}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name',name: 'name'},
                    {data: 'email',name: 'email'},
                    {data: 'phone',name: 'phone'},
                    {data: 'user_type', name: 'active_role.name'},
                    {data: 'status',name: 'status'},
                    {data: 'created_at',name: 'created_at'},
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

            let cloneCompanies = [];
            let clonedUserId = null;

            $(document).on('click', '.cloneUser', function() {
                clonedUserId = $(this).data('id');
                $('#cloneUserName').text($(this).data('name'));
                $('#cloneCompany').html('<option value="">Chargement...</option>');
                $('#cloneRole').html('<option value="">Sélectionnez d’abord une compagnie</option>').prop('disabled', true);
                $('#cloneSubmit').prop('disabled', true);
                $('#cloneNoCompany').addClass('d-none');
                $('#cloneUserModal').modal('show');

                $.get("{{ url('user') }}/" + clonedUserId + '/transfer-options')
                    .done(function(data) {
                        cloneCompanies = data.companies || [];
                        let options = '<option value="">Sélectionnez une compagnie</option>';
                        cloneCompanies.forEach(function(company) {
                            options += '<option value="' + company.id + '" ' + (company.already_member ? 'disabled' : '') + '>' +
                                $('<div>').text(company.name).html() + (company.already_member ? ' — déjà membre' : '') + '</option>';
                        });
                        $('#cloneCompany').html(options);
                        $('#cloneNoCompany').toggleClass('d-none', cloneCompanies.length > 0);
                    })
                    .fail(function(xhr) {
                        $('#cloneUserModal').modal('hide');
                        Swal.fire({icon: 'error', title: 'Erreur', text: xhr.responseJSON?.message || 'Impossible de charger les compagnies.'});
                    });
            });

            $('#cloneCompany').on('change', function() {
                const company = cloneCompanies.find(item => String(item.id) === String(this.value));
                let options = '<option value="">Sélectionnez un rôle</option>';
                (company?.roles || []).forEach(function(role) {
                    options += '<option value="' + role.id + '">' + $('<div>').text(role.name).html() + '</option>';
                });
                $('#cloneRole').html(options).prop('disabled', !company || company.already_member);
                $('#cloneSubmit').prop('disabled', true);
            });

            $('#cloneRole').on('change', function() {
                $('#cloneSubmit').prop('disabled', !this.value);
            });

            $('#cloneUserForm').on('submit', function(event) {
                event.preventDefault();
                const companyName = $('#cloneCompany option:selected').text();
                const roleName = $('#cloneRole option:selected').text();
                Swal.fire({
                    icon: 'question',
                    title: 'Approuver cette intégration ?',
                    html: 'Compagnie : <strong>' + $('<div>').text(companyName).html() + '</strong><br>Rôle : <strong>' + $('<div>').text(roleName).html() + '</strong>',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, intégrer',
                    cancelButtonText: 'Annuler'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $('#cloneLoader').removeClass('d-none');
                    $('#cloneSubmit').prop('disabled', true);
                    $.post("{{ url('user') }}/" + clonedUserId + '/transfer-company', $('#cloneUserForm').serialize())
                        .done(function(data) {
                            if (data.status) $('#cloneUserModal').modal('hide');
                            Swal.fire({icon: data.status ? 'success' : 'warning', title: data.title, text: data.msg});
                        })
                        .fail(function(xhr) {
                            Swal.fire({icon: 'error', title: 'Erreur', text: xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Intégration impossible.'});
                        })
                        .always(function() {
                            $('#cloneLoader').addClass('d-none');
                            $('#cloneSubmit').prop('disabled', !$('#cloneRole').val());
                        });
                });
            });

            // employe datatable
            // var employeDatatable = $('#employeDatatable').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: "{{ route('user.index')}}",
            //     columns: [
            //         {data: 'id',name: 'id'},
            //         {data: 'name',name: 'name'},
            //         {data: 'email',name: 'email'},
            //         {data: 'status',name: 'status'},
            //         {data: 'created_at',name: 'created_at'},
            //         {data: 'action', name: 'action', orderable: false, searchable: false},
            //     ],
            //     responsive: true, 
            //     language: {
            //         "lengthMenu": "Afficher _MENU_ entrées",
            //         "zeroRecords": "Aucune donnée disponible",
            //         "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            //         "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
            //         "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
            //         "search": "Rechercher:",
            //         "paginate": {
            //             "first": "Premier",
            //             "last": "Dernier",
            //             "next": "Suivant",
            //             "previous": "Précédent"
            //         }
            //     },
                
            //     drawCallback: function() {
            //         $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            //         $('#datatable').css('width','100%');
            //         $('#datatable tbody tr').each(function() {
            //             $(this).css('background-color', 'black');  // Appliquer un fond personnalisé
            //             $(this).css('color', 'white');
            //         });
            //         $('.dataTables_info, .dataTables_paginate').css('color', 'white');
            //         $('.dataTables_paginate .paginate_button a').css('color', 'white');
            //         $('.dataTables_length select option').css('color', 'black'); // Mettre la couleur noire pour les options
            //         $('.dataTables_length select option').css('background-color', 'white'); // Fond blanc pour les options

            //         // Appliquer la couleur blanche au texte des labels
            //         $('.dataTables_length label').css('color', 'white'); // Couleur blanche pour "Afficher _MENU_ entrées"
            //         $('.dataTables_filter label').css('color', 'white'); // Couleur blanche pour "Rechercher:"
                    
            //         // Appliquer les styles pour le dropdown et le champ de recherche
            //         $('.dataTables_length select').css({
            //             'background-color': 'black', // Fond noir
            //             'color': 'white' // Texte en blanc
            //         });

            //         $('.dataTables_filter input').css({
            //             'background-color': 'black', // Fond noir
            //             'color': 'white' // Texte en blanc
            //         });
            //         $('.dataTables_filter input::placeholder').css('color', 'white'); // Placeholder en blanc
            //         $('#datatable').css('width', '100%');
            //     },
            // });
            // window.addEventListener('datatableUpdated', function() {
            //     Datatable.ajax.reload(null, false);
            //     employeDatatable.ajax.reload(null, false);
            // });

            $('#inviteUserForm').on('submit', function(event) {
                event.preventDefault();
                const form = this;
                const email = $(form).find('[name="email"]').val().trim();
                const role = $('#invitation_role option:selected').text();
                const safeEmail = $('<div>').text(email).html();
                const safeRole = $('<div>').text(role).html();

                Swal.fire({
                    icon: 'question',
                    title: 'Envoyer cette invitation ?',
                    html: 'Adresse : <strong>' + safeEmail + '</strong><br>Rôle : <strong>' + safeRole + '</strong>',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, envoyer',
                    cancelButtonText: 'Vérifier l’adresse'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $('#inviteUserLoader').removeClass('d-none');
                    $.post("{{ route('user.invitations.store') }}", $(form).serialize())
                        .done(function(data) {
                            Swal.fire({icon: 'success', title: data.title, text: data.msg}).then(function() { window.location.reload(); });
                        })
                        .fail(function(xhr) {
                            Swal.fire({icon: 'error', title: 'Invitation impossible', text: xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Une erreur est survenue.'});
                        })
                        .always(function() { $('#inviteUserLoader').addClass('d-none'); });
                });
            });

            $(document).on('click', '.resendInvitation', function() {
                const id = $(this).data('id');
                const email = $(this).data('email');
                Swal.fire({
                    icon: 'question',
                    title: 'Renvoyer cette invitation ?',
                    text: 'Un nouveau lien sera envoyé à ' + email + ' et l’ancien lien sera invalidé.',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, renvoyer',
                    cancelButtonText: 'Annuler',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() {
                        const cancelButton = Swal.getCancelButton();
                        if (cancelButton) cancelButton.disabled = true;

                        return new Promise(function(resolve) {
                            $.post("{{ url('user/invitations') }}/" + id + '/resend', {_token: "{{ csrf_token() }}"})
                                .done(function(data) { resolve(data); })
                                .fail(function(xhr) {
                                    if (cancelButton) cancelButton.disabled = false;
                                    Swal.showValidationMessage(xhr.responseJSON?.message || 'Renvoi impossible.');
                                    resolve(false);
                                });
                        });
                    }
                })
                    .then(function(result) {
                        if (!result.isConfirmed || !result.value) return;
                        const data = result.value;
                        Swal.fire({icon:'success', title:data.title, text:data.msg}).then(function(){ window.location.reload(); });
                    });
            });

            $(document).on('click', '.revokeInvitation', function() {
                const id = $(this).data('id');
                const email = $(this).data('email');
                Swal.fire({
                    icon: 'warning',
                    title: 'Révoquer cette invitation ?',
                    text: 'Le lien envoyé à ' + email + ' deviendra définitivement inutilisable.',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, révoquer',
                    cancelButtonText: 'Annuler',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: function() { return !Swal.isLoading(); },
                    allowEscapeKey: function() { return !Swal.isLoading(); },
                    preConfirm: function() {
                        const cancelButton = Swal.getCancelButton();
                        if (cancelButton) cancelButton.disabled = true;

                        return new Promise(function(resolve) {
                            $.ajax({
                                url: "{{ url('user/invitations') }}/" + id,
                                type: 'DELETE',
                                data: {_token: "{{ csrf_token() }}"}
                            })
                                .done(function(data) { resolve(data); })
                                .fail(function(xhr) {
                                    if (cancelButton) cancelButton.disabled = false;
                                    Swal.showValidationMessage(xhr.responseJSON?.message || 'Révocation impossible.');
                                    resolve(false);
                                });
                        });
                    }
                })
                    .then(function(result) {
                        if (!result.isConfirmed || !result.value) return;
                        const data = result.value;
                        Swal.fire({icon:'success', title:data.title, text:data.msg}).then(function(){ window.location.reload(); });
                    });
            });

            $('#attachExistingForm').submit(function(event) {
                event.preventDefault();
                $('#attachExistingLoader').show();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('user.attach-existing') }}",
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(data) {
                        $('#attachExistingLoader').hide();
                        if (data.status) {
                            $('#attachExistingModal').modal('hide');
                            $('#attachExistingForm')[0].reset();
                            $('#existing_user_role').val(null).trigger('change');
                            Datatable.ajax.reload(null, false);
                        }
                        Swal.fire({
                            icon: data.status ? 'success' : 'warning',
                            title: data.title,
                            text: data.msg,
                            confirmButtonText: "D'accord"
                        });
                    },
                    error: function(xhr) {
                        $('#attachExistingLoader').hide();
                        const message = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Impossible de rattacher cet utilisateur.';
                        Swal.fire({ icon: 'error', title: 'Erreur', text: message });
                    }
                });
            });

            //Add user
            $('#add').submit(function() {
                event.preventDefault();
                $('#loader').fadeIn();
                $('#submitText').hide();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('user.store') }}",
                    //enctype: 'multipart/form-data',
                    data: $('#add').serialize(),
                    datatype: 'json',
                    success: function(data) {
                        console.log(data)
                        if (data.status) {
                            $('#loader').hide();
                            $('#submitText').fadeIn();
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: "success",
                                title: data.title,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                text: data.msg,
                            });
                            $('#addModal').modal('hide');
                            Datatable.draw();
                            $('#add')[0].reset();
                        } else {
                            $('#loader').hide();
                            $('#submitText').fadeIn();
                            Swal.fire({
                                title: data.title,
                                text: data.msg,
                                icon: 'error',
                                confirmButtonText: "D'accord",
                                confirmButtonColor: '#A40000',
                            })
                        }
                    },
                    error: function(data) {
                        console.log(data)
                        $('#loader').hide();
                        $('#submitText').fadeIn();
                        Swal.fire({
                            icon: "error",
                            title: "erreur",
                            text: "Impossible de communiquer avec le serveur.",
                            timer: 3600,
                        })
                    }
                });
                return false;
            });

            $('body').on('click', '.editModal', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('user')}}/'+id+'/edit',
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#edit_response').html(result);
                        // Le formulaire arrive par AJAX après l'ouverture du modal.
                        if (typeof initSearchableSelects === 'function') {
                            initSearchableSelects(document.getElementById('edit_response'));
                        }
                    }
                });
                $('#editModal').modal('show');
            });

            $('body').on('click', '.viewUser', function (e) {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data('id');
                $('#view_response').empty();
                $.ajax({
                    url:'classroom/view/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#view_response').html(result);
                    }
                });
                $('#modal-view').modal('show');
            });

            $(document).on('click','.editUser',function(e){
                var modalHeader = $("#modal-header-edit");
                modalHeader.attr("class", "modal-header bg-success text-light");
                e.preventDefault();
            });

            $('body').on('click', '.archive', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir archiver cet utilisateur?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    confirmButtonColor: 'red',
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    cancelButtonColor: 'blue',
                }).then((result) => {
                    if (result.isConfirmed){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            type: "post",
                            url: 'user/'+id,
                            type: "DELETE",
                            datatype: 'json',
                            success: function (data) {
                                if(data.status){
                                    Swal.fire({
                                        toast: true,
                                        position: 'top',
                                        icon: "success",
                                        title: data.title,
                                        showConfirmButton: false,
                                        timer: 5000,
                                        timerProgressBar: true,
                                        text: data.msg,
                                    });
                                    Datatable.draw();
                                }else{
                                    Swal.fire({
                                        icon: "error",
                                        title: data.title,
                                        text: data.msg,
                                    })
                                }
                            },
                            error: function (data) {
                                console.log('Error:', data);
                            }
                        });
                    }
                })
            });

            $('body').on('click', '.restore', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir restaurer cet utilisateur?",
                    // text: " Les éléments liés a la ville seront supprimés ; la confirmation est irréversible",
                    confirmButtonText: "Oui",
                    confirmButtonColor: 'green',
                    showCancelButton: true,
                    cancelButtonText: "Non",
                    cancelButtonColor: 'blue',
                }).then((result) => {
                    if (result.isConfirmed){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            type: "post",
                            url: 'user/'+id,
                            type: "DELETE",
                            datatype: 'json',
                            success: function (data) {
                                if(data.status){
                                    Swal.fire({
                                        toast: true,
                                        position: 'top',
                                        icon: "success",
                                        title: data.title,
                                        showConfirmButton: false,
                                        timer: 5000,
                                        timerProgressBar: true,
                                        text: data.msg,
                                    });
                                    Datatable.draw();
                                }else{
                                    Swal.fire({
                                        icon: "error",
                                        title: data.title,
                                        text: data.msg,
                                    })
                                }
                            },
                            error: function (data) {
                                console.log('Error:', data);
                            }
                        });
                    }
                })
            });
        });
    </script>

    @endsection
