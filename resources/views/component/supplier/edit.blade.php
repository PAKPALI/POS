<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-6 mb-3">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" id="name" value="{{$Supplier->name}}" placeholder="Nom">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="contact">Contact / Adresse</label>
                <input type="text" name="contact" class="form-control" id="contact" value="{{$Supplier->contact}}" placeholder="Contact ou adresse">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="phone">Téléphone</label>
                <input type="text" name="phone" class="form-control" id="phone" value="{{$Supplier->phone}}" placeholder="Téléphone">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" name="whatsapp" class="form-control" id="whatsapp" value="{{$Supplier->whatsapp}}" placeholder="Numéro WhatsApp">
            </div>
        </div>
    </div>
    <div class="card-footer mt-4">
        <button id="submit" class="btn btn-warning" type="submit" data-loading-text="Modification…">
            Modifier
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
                url: '{{ url('component/supplier/' . $Supplier->id) }}',
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
