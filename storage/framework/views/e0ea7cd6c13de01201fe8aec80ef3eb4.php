<?php $__env->startPush('css-scripts'); ?>
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }

    /* Transform button in circle */
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
    /* Switch OFF (rouge) */
    .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
        background-color: #dc3545 !important; /* rouge */
        border: none;
    }

    /* Switch ON (vert) */
    .form-check-input:checked {
        background-color: #28a745 !important; /* vert */
        border: none;
    }

    /* Smooth animation */
    .form-check-input {
        transition: all 0.2s ease-in-out;
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="row">
                    <!-- TOTAL CAISSES -->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card border-color mb-3 card-white-shadow">
                            <div class="card-body">
                                <div class="d-flex fw-bold small mb-3">
                                    <span class="flex-grow-1">TOTAL CAISSES</span>
                                </div>

                                <div class="row align-items-center mb-2">
                                    <div class="col-7">
                                        <h3 class="mb-0"><?php echo e($totalCash->count); ?></h3>
                                        <span class="badge blink-badge">
                                            <?php echo e($totalCash ? number_format($totalCash->balance, 2, ',', ' ') : 0); ?> F CFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CAISSES ACTIVES -->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card border-color mb-3 card-white-shadow">
                            <div class="card-body">
                                <div class="d-flex fw-bold small mb-3">
                                    <span class="flex-grow-1">CAISSES ACTIVES</span>
                                </div>

                                <div class="row align-items-center mb-2">
                                    <div class="col-7">
                                        <h3 class="mb-0"><?php echo e($activeCash->count); ?></h3>
                                        <span class="badge blink-badge">
                                            <?php echo e($activeCash ? number_format($activeCash->balance, 2, ',', ' ') : 0); ?> F CFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CAISSES INACTIVES -->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card border-color mb-3 card-white-shadow">
                            <div class="card-body">
                                <div class="d-flex fw-bold small mb-3">
                                    <span class="flex-grow-1">CAISSES INACTIVES</span>
                                </div>

                                <div class="row align-items-center mb-2">
                                    <div class="col-7">
                                        <h3 class="mb-0"><?php echo e($inactiveCash->count); ?></h3>
                                        <span class="badge blink-badge">
                                            <?php echo e($inactiveCash ? number_format($inactiveCash->balance, 2, ',', ' ') : 0); ?> F CFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CAISSE PRINCIPALE -->
                    <div class="col-xl-6 col-lg-6">
                        <div class="card border-color mb-3 card-white-shadow">
                            <div class="card-body">
                                <div class="d-flex fw-bold small mb-3">
                                    <span class="flex-grow-1">CAISSE PRINCIPALE</span>
                                </div>

                                <div class="row align-items-center mb-2">
                                    <div class="col-7">
                                        <h3 class="mb-0"><?php echo e($defaultCashName ?? 'Aucune'); ?></h3>
                                        <span class="badge blink-badge">
                                            <?php echo e($defaultCash ? number_format($defaultCash->balance, 2, ',', ' ') : 0); ?> F CFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-xl-6 col-lg-6">
                        <div class="card border-color mb-3 card-white-shadow">
                            <div class="card-body">
                                <div class="d-flex fw-bold small mb-3">
                                    <span class="flex-grow-1">CAISSE TAXE</span>
                                </div>

                                <div class="row align-items-center mb-2">
                                    <div class="col-7">
                                        <h3 class="mb-0"><?php echo e($taxCash ? $taxCash->name : 'Aucune'); ?></h3>
                                        <span class="badge blink-badge">
                                            <?php echo e($taxCash ? number_format($taxCash->balance, 2, ',', ' ') : 0); ?> FCFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <ul class="breadcrumb">
                            <!-- <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li> -->
                        </ul>
                        <h1 class="page-header">
                            CAISSES
                            <!-- <img src="<?php echo e(asset('images/1729538166.jpg')); ?>" alt="Image du produit"> -->
                        </h1>
                        <hr class="mb-4">
                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h3 class="modal-title">Ajouter caisse</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="add">
                                            <?php echo csrf_field(); ?>
                                            <div class="card-body">
                                                <div class="row text-center">

                                                    <div class="col-md-12 mb-4">
                                                        <label>Nom de la caisse</label>
                                                        <input type="text" name="name" class="form-control" required>
                                                    </div>

                                                    <!-- TOGGLE DEFAULT -->
                                                    <div class="col-md-4 mt-3">
                                                        <label class="form-label">Caisse principale</label>
                                                        <div class="d-flex justify-content-center align-items-center">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" name="is_default" value="1">
                                                                <!-- <label class="form-check-label">Activer comme caisse par défaut</label> -->
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TOGGLE TAX -->
                                                    <div class="col-md-4 mt-3">
                                                        <label class="form-label">Caisse de taxe</label>
                                                        <div class="d-flex justify-content-center align-items-center">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" name="is_tax" value="1">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TOGGLE STATUS -->
                                                    <div class="col-md-4 mt-3">
                                                        <label class="form-label">Statut</label>
                                                        <div class="d-flex justify-content-center align-items-center">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                                                                <!-- <label class="form-check-label">Caisse active</label> -->
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 mt-4">
                                                        <label>Description</label>
                                                        <textarea name="description" class="form-control"></textarea>
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
                                        <h3 class="modal-title text-dark ">Modifier produit</h3>
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
                                        <span class="flex-grow-1"><h4>Listes des caisses</h4></span>
                                        <button type="button" class="btn btn-primary mb-1 me-3 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="datatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Code</th>
                                                    <th>Nom</th>
                                                    <th>Solde</th>
                                                    <!-- <th>Devise</th> -->
                                                    <th>Principale</th>
                                                    <th>Status</th>
                                                    <th>Créé par</th>
                                                    <th>Créé le</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
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
                    
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-buttons/js/buttons.flash.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-buttons/js/buttons.html5.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-buttons/js/buttons.print.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/plugins/bootstrap-table/dist/bootstrap-table.min.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/js/demo/table-plugins.demo.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="<?php echo e(asset('hub/assets/js/demo/sidebar-scrollspy.demo.js')); ?>" type="3e072b31e4d62a351cb180e3-text/javascript"></script>

    <script>
        $(function() {
            // hide loader
            $('#loader').hide();

            var Datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "<?php echo e(route('cash-account.index')); ?>",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'code', name: 'code'},
                    {data: 'name', name: 'name'},
                    {data: 'balance', name: 'balance'},
                    // {data: 'currency', name: 'currency'},
                    {data: 'is_default', name: 'is_default'},
                    {data: 'status', name: 'status'},
                    {data: 'created_by', name: 'created_by'},
                    {data: 'created_at', name: 'created_at'},
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

            //Add cash
            $('#add').submit(function() {
                $('#loader').fadeIn();
                $('#submitText').hide();

                var formData = new FormData($('#add')[0]);

                $.ajax({
                    type: 'POST',
                    url: "<?php echo e(route('cash-account.store')); ?>",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(data) {
                        $('#loader').hide();
                        $('#submitText').fadeIn();
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: "success",
                            title: "Succès",
                            text: "Caisse créée avec succès",
                            timer: 3000,
                            showConfirmButton: false,
                        });
                        $('#addModal').modal('hide');
                        $('#add')[0].reset();
                        Datatable.draw();
                    },

                    error: function() {
                        $('#loader').hide();
                        $('#submitText').fadeIn();

                        Swal.fire({
                            icon: "error",
                            title: "Erreur",
                            text: "Impossible d'enregistrer la caisse",
                        });
                    }
                });

                return false;
            });

            $('body').on('click', '.editModal', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'<?php echo e(url('ams/cash-account')); ?>/'+id+'/edit',
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
                    url:'<?php echo e(url('ams/cash-account')); ?>/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    }
                });
                $('#showModal').modal('show');
            });
            
            $('body').on('click', '.archive', function () {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var id = $(this).data("id");   
                
                Swal.fire({
                    icon: "question",
                    title: "Etes vous sur de vouloir archiver cette caisse?",
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
                            url: 'cash-account/'+id,
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
                    title: "Etes vous sur de vouloir restaurer cette caisse?",
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
                            url: 'cash-account/'+id,
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

    <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\POS\resources\views/ams/cash/index.blade.php ENDPATH**/ ?>