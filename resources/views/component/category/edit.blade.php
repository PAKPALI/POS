<form id="update_form">
    @csrf
    <div class="saas-form-group">
        <label for="edit-category-name">Nom de la catégorie <span aria-hidden="true">*</span></label>
        <input type="text" name="name" id="edit-category-name" value="{{ $Category->name }}" placeholder="Nom de la catégorie" maxlength="255" required>
        <small>Ce nom sera utilisé dans les listes et lors de la création des produits.</small>
    </div>
    <div class="saas-modal-actions">
        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
        <button id="submit" class="saas-btn saas-btn-warning" type="submit" data-loading-text="Enregistrement…">
            <i class="bi bi-check-lg" aria-hidden="true"></i><span>Enregistrer</span>
        </button>
    </div>
</form>

<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#update_form').submit(function(event) {
            event.preventDefault();
            $.ajax({
                data: $('#update_form').serialize(),
                url: '{{ url('component/category/' . $Category->id) }}',
                type: "PUT",
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
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
                error: function(xhr) {
                    const message = xhr.responseJSON
                        ? (xhr.responseJSON.msg || xhr.responseJSON.message)
                        : null;
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: 'Erreur',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        text: message || 'Une erreur est survenue, veuillez réessayer.',
                    });
                }
            });
        });
    });
</script>
