
<?php $__env->startPush('css-scripts'); ?>
<style>
    #datatable tbody tr {
        background-color: #f0f0f0;
    }
    #datatable tbody tr:hover {
        background-color: #e0e0e0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                            INVENTAIRES
                        </h1>
                        <hr class="mb-4">
                        <!-- add modal -->
                        <div class="modal modal fade" id="addModal">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h3 class="modal-title">Ajouter inventaire</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form id="add">
                                        <?php echo csrf_field(); ?>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label for="product_id">Produit</label>
                                                    <select name="product_id" id="product_id" class="form-select">
                                                        
                                                        <option value="">Sélectionner un produit</option>
                                                        <?php $__currentLoopData = $Product; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?> (<?php echo e($item->qte); ?>)</option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="qte_added">Quantité ajoutée</label>
                                                    <input type="number" name="qte_added" id="qte_added" class="form-control" placeholder="Quantité ajoutée">
                                                </div>
                                                <div class="form-group col-12 mt-3">
                                                    <label for="qte_before">Note</label>
                                                    <textarea name="note" id="note" class="form-control" placeholder="Note"></textarea>
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
                                        <span class="flex-grow-1"><h4>Listes des inventaires</h4></span>
                                        <button type="button" class="btn btn-primary mb-1 me-3 text-right" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter</button>
                                        <a href="#" data-toggle="card-expand" class="text-inverse text-opacity-50 text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                                    </div>
                                    <div class="row mb-5">

                                        <div class="col-md-4">
                                            <label>Produit</label>
                                            <select class="form-select" id="filter_product">
                                                <option value="">Tous les produits</option>

                                                <?php $__currentLoopData = $Product; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($prod->id); ?>">
                                                        <?php echo e($prod->name); ?> (<?php echo e($prod->qte); ?>)
                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Date début</label>
                                            <input type="date" class="form-control" id="start_date">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Date fin</label>
                                            <input type="date" class="form-control" id="end_date">
                                        </div>

                                    </div>
                                    <div class="table-responsive">
                                        <table id="datatable" class="table text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Produit</th>
                                                    <th>Qté avant</th>
                                                    <th>Qté ajoutée</th>
                                                    <th>Qté après</th>
                                                    <th>Créer par</th>
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
                ajax: {
                    url: "<?php echo e(route('inventory.index')); ?>",
                    data: function (d) {
                        d.product_id = $('#filter_product').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    {data: 'id',name: 'id'},
                    {data: 'product_id',name: 'product_id'},
                    {data: 'qte_before',name: 'qte_before'},
                    {data: 'qte_added',name: 'qte_added'},
                    {data: 'qte_after',name: 'qte_after'},
                    {data: 'created_by',name: 'created_by'},
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

            $('#filter_product, #start_date, #end_date').on('change', function(){
                Datatable.draw();
            });

            window.addEventListener('datatableUpdated', function() {
                Datatable.ajax.reload(null, false);
            });

            //Add category
            $('#add').submit(function() {
                event.preventDefault();
                $('#loader').fadeIn();
                $('#submitText').hide();
                $.ajax({
                    type: 'POST',
                    url: "<?php echo e(route('inventory.store')); ?>",
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

            $('body').on('click', '.view', function () {
                var id = $(this).data("id");
                $.ajax({
                    url:'<?php echo e(url('component/inventory')); ?>/'+id,
                    dataType: 'html',
                    success:function(result)
                    {
                        $('#show_response').html(result);
                    }
                });
                $('#showModal').modal('show');
            });
        }); 
    </script>

    <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\POS\resources\views/component/inventory/index.blade.php ENDPATH**/ ?>