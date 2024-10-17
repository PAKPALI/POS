<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-12">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" id="name" value="{{$User->name}}" placeholder="Nom">
            </div>
        </div>
    </div>
    <div class="card-footer mt-4">
        <button id="submit" class="btn btn-warning">
            <div id="edit_loader" class="spinner-grow"></div>
            <div id="edit_submitText">Valider</div>
        </button>
    </div>
</form>

<script>
    $(function() {
        // hide loader
        $('#edit_loader').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#submit').click(function(e) {
            e.preventDefault();
            $(this).html('Mise à jour en cours...');
            var id = $(this).data("id");
            $('#edit_loader').fadeIn();
            $('#edit_submitText').hide();
            $.ajax({
                data: $('#update_form').serialize(),
                url: '{{ url('user/ '. $User->id) }}',
                type: "PUT",
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        console.log(data)
                        $('#edit_loader').hide();
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
                        // $('#datatable').DataTable().ajax.reload(null, true);
                    } else {
                        $('#edit_loader').hide();
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
                        // $('#add_user').trigger("reset");
                        $('#submit').html('Modifier');
                    }
                },
                error: function(data) {
                    console.log('Error:', data)
                    $('#edit_loader').hide();
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
                    $('#submit').html('Modifier');
                }
            });
            $(this).html('Modifier');
        });
    });
</script>