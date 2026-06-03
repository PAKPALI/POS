<form id="update_form">
    <?php echo csrf_field(); ?>
    <div class="card-body">
        <div class="row">
            <div class="form-group col-6">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" id="name" value="<?php echo e($User->name); ?>" placeholder="Nom">
            </div>
            <div class="form-group col-6">
                <label for="phone">Numéro de téléphone (sans espaces et indicatif)</label>
                <input type="number" name="phone" class="form-control" id="phone" value="<?php echo e($User->phone); ?>" placeholder="ex: 90859488">
            </div>
            <div class="form-group col-12 mt-3">
                <label for="user_type">Type d'utilisateur</label>
                <select class="form-select " name="user_type">
                    <option value="">selectionnez le type d'utilisateur</option>
                    <option value="2" <?php echo e($User->user_type == 2 ? 'selected' : ''); ?>>ADMIN</option>
                    <option value="3" <?php echo e($User->user_type == 3 ? 'selected' : ''); ?>>EMPLOYE</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer mt-4">
        <button id="submit" class="btn btn-warning" type="submit">
            <div id="edit_loader" class="spinner-grow" style="display: none;"></div>
            <div id="edit_submitText">Modifier</div>
        </button>
    </div>
</form>

<script>
    $(function() {
        // Cache le loader au chargement de la page
        $('#edit_loader').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#submit').click(function(e) {
            e.preventDefault();

            // Affiche le loader et cache le texte du bouton
            $('#edit_loader').fadeIn();
            $('#edit_submitText').hide();

            $.ajax({
                data: $('#update_form').serialize(),
                url: '<?php echo e(url('user/' . $User->id)); ?>',
                type: "PUT",
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        console.log(data);
                        // Cache le loader et affiche le texte du bouton
                        $('#edit_loader').fadeOut();
                        $('#edit_submitText').fadeIn();

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

                        $('#editModal').modal('hide');
                        window.dispatchEvent(new Event('datatableUpdated'));
                    } else {
                        $('#edit_loader').fadeOut();
                        $('#edit_submitText').fadeIn();

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
                    console.log('Error:', data);
                    $('#edit_loader').fadeOut();
                    $('#edit_submitText').fadeIn();

                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: 'Erreur',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        text: 'Une erreur est survenue, veuillez réessayer.',
                    });
                }
            });
        });
    });
</script>
<?php /**PATH C:\POS\resources\views/user/edit.blade.php ENDPATH**/ ?>