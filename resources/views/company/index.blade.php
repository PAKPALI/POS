@extends('layouts.layout')
@push('css-scripts')
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }
    .company-switch-card { height: 100%; border: 1px solid rgba(255,255,255,.1); border-radius: 16px; background: rgba(255,255,255,.025); transition: .2s ease; }
    .company-switch-card:hover { transform: translateY(-3px); border-color: rgba(25,195,125,.55); }
    .company-switch-card.is-active { border-color: #19c37d; box-shadow: 0 0 0 1px rgba(25,195,125,.15); }
    .company-switch-logo { width: 52px; height: 52px; border-radius: 14px; flex: 0 0 52px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg,#20cf8b,#117a58); color: #fff; font-weight: 800; }
    .company-switch-logo img { width: 100%; height: 100%; object-fit: cover; }
    .company-switch-name { color: #fff !important; overflow-wrap: anywhere; line-height: 1.3; }
    .company-role { display: inline-block; padding: .3rem .6rem; border-radius: 999px; background: rgba(255,255,255,.08); color: rgba(255,255,255,.7); font-size: .75rem; }
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
                            PARAMETRES
                        </h1>
                        <hr class="mb-4">

                        <section class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="h5 mb-1">Mes compagnies</h3>
                                    <p class="text-muted mb-0">Sélectionnez une compagnie ou créez un nouvel espace.</p>
                                </div>
                                @if($currentMembership?->role?->key === 'owner')
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                        <i class="bi bi-plus-lg me-1"></i> Ajouter une compagnie
                                    </button>
                                @endif
                            </div>
                            <div class="row g-3">
                                @foreach($memberships as $membership)
                                    @php $isActiveCompany = (int) $activeCompanyId === (int) $membership->company_id; @endphp
                                    <div class="col-xl-4 col-md-6">
                                        <div class="company-switch-card {{ $isActiveCompany ? 'is-active' : '' }} p-3 d-flex flex-column">
                                            <div class="d-flex gap-3 align-items-start mb-3">
                                                <div class="company-switch-logo">
                                                    @if($membership->company->logo)
                                                        <img src="{{ asset($membership->company->logo) }}" alt="Logo {{ $membership->company->name }}">
                                                    @else
                                                        {{ mb_strtoupper(mb_substr($membership->company->name, 0, 2)) }}
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <h4 class="h6 company-switch-name mb-1">{{ $membership->company->name }}</h4>
                                                    <div class="small text-muted" style="overflow-wrap:anywhere">{{ $membership->company->email ?: 'Aucun e-mail' }}</div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <span class="company-role">{{ $membership->role->name ?? 'Sans rôle' }}</span>
                                                @if($isActiveCompany)<span class="badge bg-success">Compagnie active</span>@endif
                                            </div>
                                            @if($isActiveCompany)
                                                <button type="button" class="btn btn-outline-success btn-sm mt-auto" disabled>Actuellement ouverte</button>
                                            @else
                                                <form method="POST" action="{{ route('companies.switch', $membership->company_id) }}" class="mt-auto">
                                                    @csrf
                                                    <button class="btn btn-theme btn-sm w-100">Ouvrir cette compagnie</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h3 class="modal-title">Ajouter compagnie</h3>
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
                                                        <label for="exampleInputText1">Email</label>
                                                        <input type="text" name="email" class="form-control" id="exampleInputText1" placeholder="Email">
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div class="form-group col-6">
                                                        <label for="exampleInputText2">Numéro 1</label>
                                                        <input type="number" name="number1" class="form-control" id="exampleInputText2" placeholder="Numéro 1">
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="exampleInputText3">Numéro 2</label>
                                                        <input type="number" name="number2" class="form-control" id="exampleInputText3" placeholder="Numéro 2">
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div class="form-group col-6">
                                                        <label for="exampleInputText4">Adresse</label>
                                                        <input type="text" name="adress" class="form-control" id="exampleInputText4" placeholder="Adresse">
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="exampleInputText5">Message</label>
                                                        <input type="text" name="message" class="form-control" id="exampleInputText5" placeholder="Message">
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div class="form-group col-6">
                                                        <label for="add_logo">Logo</label>
                                                        <input type="file" name="logo" class="form-control" id="add_logo" accept="image/*">
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="add_ecommerce_active" class="d-block">Boutique en ligne</label>
                                                        <div class="form-check form-switch mt-2">
                                                            <input type="checkbox" name="ecommerce_active" class="form-check-input" id="add_ecommerce_active" value="1">
                                                            <label class="form-check-label" for="add_ecommerce_active">Activer</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div class="form-group col-12">
                                                        <label for="add_description">Description (pour le site ecommerce)</label>
                                                        <textarea name="description" class="form-control" id="add_description" rows="3" placeholder="Description de l'entreprise"></textarea>
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

                        <!-- update modal -->
                        <div class="modal" id="editModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h3 class="modal-title text-dark ">Modifier compagnie</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="edit_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- view modal -->
                        <div class="modal fade" id="showModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h3 class="modal-title text-dark ">Détail</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="show_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex fw-bold small mb-3">
                                        <span class="flex-grow-1"><h4>Informations de la compagnie active</h4></span>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="table-responsive">
                                    <table id="datatable" class="table text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nom</th>
                                                <th>Email</th>
                                                <th>Adresse</th>
                                                <th>Numéro 1</th>
                                                <th>Numéro 2</th>
                                                <th>Créer le</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    
                                </div>

                                <div class="card-arrow">
                                    <div class="card-arrow-top-left"></div>
                                    <div class="card-arrow-top-right"></div>
                                    <div class="card-arrow-bottom-left"></div>
                                    <div class="card-arrow-bottom-right"></div>
                                </div>
                            </div>
                        </div>
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

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('company.index')}}",
                columns: [
                    {data: 'id',name: 'id'},
                    {data: 'name',name: 'name'},
                    {data: 'email',name: 'email'},
                    {data: 'adress',name: 'adress'},
                    {data: 'number1',name: 'number1'},
                    {data: 'number2',name: 'number2'},
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

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            //Add company
            $('#add').submit(function() {
                event.preventDefault();
                $('#loader').fadeIn();
                $('#submitText').hide();
                var formData = new FormData($('#add')[0]);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('company.store') }}",
                    data: formData,
                    contentType: false,
                    processData: false,
                    datatype: 'json',
                    success: function(data) {
                        console.log(data)
                        if (data.status) {
                            $('#loader').hide();
                            $('#submitText').fadeIn();
                            $('#addModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Compagnie créée',
                                text: 'Voulez-vous basculer vers « ' + data.company_name + ' » maintenant ?',
                                confirmButtonText: 'Oui, basculer',
                                cancelButtonText: 'Non, rester ici',
                                showCancelButton: true,
                                confirmButtonColor: '#16a34a'
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    submitCompanySwitch(data.switch_url);
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            $('#loader').hide();
                            $('#submitText').fadeIn();
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: "error",
                                title: data.title,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                text: data.msg,
                            });
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

            function submitCompanySwitch(url) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                document.body.appendChild(form);
                form.submit();
            }

            $('body').on('click', '.editModal', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('setting/company')}}/'+id+'/edit',
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#edit_response').html(result);
                    }
                });
                $('#editModal').modal('show');
            });

            $('body').on('click', '.view', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'{{url('setting/company')}}/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    }
                });
                $('#showModal').modal('show');
            });

            $('body').on('click', '.deleteUser', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir désactiver cet utilisateur?",
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
                            url: "utilisateurs/delete_user",
                            data: {id: id},
                            datatype: 'json',
                            success: function (data) {
                                if(data.status){
                                    Swal.fire({
                                        icon: "success",
                                        title: data.title,
                                        text: data.msg,
                                    }).then(() => {
                                        user_list.draw();
                                    })
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
