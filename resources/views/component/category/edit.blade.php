<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-12">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" id="name" value="{{$Category->name}}" placeholder="Nom">
            </div>
        </div>
    </div>
    <div class="card-footer mt-4">
        <button type="submit" class="btn btn-warning">
            <div class="loader" class="spinner-border text-light" role="status"></div>
            <div id="submit">Modifier</div>
        </button>
    </div>
</form>

<script>
    $(function() {
        // hide loader
        $('.loader').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#submit').click(function(e) {
            e.preventDefault();
            $(this).html('Mise à jour en cours...');
            var id = $(this).data("id");
            $.ajax({
                data: $('#update_form').serialize(),
                url: '{{ url('component/category/ '. $Category->id) }}',
                type: "PUT",
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        console.log(data)
                        $('#datatable').DataTable().ajax.reload(null, true);
                        $('#submit').html('Modifier');
                        $('#editModal').modal('hide');
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
                        // $('#add_user').trigger("reset");
                        $('#submit').html('Modifier');
                    }
                },
                error: function(data) {
                    console.log('Error:', data)
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