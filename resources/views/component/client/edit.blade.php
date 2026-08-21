<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-12">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" id="name" value="{{$Client->name}}" placeholder="Nom">
            </div>
        </div>
    </div>
    <div class="card-footer mt-4">
        <button class="btn btn-warning" type="submit" data-loading-text="Modification en cours…">
            Modifier
        </button>
    </div>
</form>

<script>
    $(function() {
        $('#update_form').submit(function(event) {
            event.preventDefault();
            const form = this;

            $.ajax({
                data: $(form).serialize(),
                url: '{{ url('component/client/' . $Client->id) }}',
                type: 'PUT',
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'success',
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
                            icon: 'error',
                            title: data.title,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            text: data.msg,
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};
                    const validationErrors = response.errors ? Object.values(response.errors) : [];
                    const firstValidationError = validationErrors.length
                        ? (Array.isArray(validationErrors[0]) ? validationErrors[0][0] : validationErrors[0])
                        : null;

                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'error',
                        title: 'Modification impossible',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        text: response.msg || response.message || firstValidationError
                            || 'Une erreur est survenue, veuillez réessayer.',
                    });
                }
            });
        });
    });
</script>
