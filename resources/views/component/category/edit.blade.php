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
        <button id="submit" class="btn btn-warning" type="submit">
            <div class="loader spinner-grow" style="display: none;"></div>
            <span id="submit_text">Modifier</span>
        </button>
    </div>
</form>

<script>
    $(function() {
        // Cache le loader au chargement de la page
        $('.loader').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#submit').click(function(e) {
            e.preventDefault();

            // Affiche le loader et remplace le texte du bouton
            $('.loader').fadeIn();
            $('#submit_text').hide();
            
            $.ajax({
                data: $('#update_form').serialize(),
                url: '{{ url('component/category/' . $Category->id) }}',
                type: "PUT",
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        console.log(data);
                        // Cache le loader et remet le texte "Modifier"
                        $('.loader').fadeOut();
                        $('#submit_text').fadeIn();

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
                        $('.loader').fadeOut();
                        $('#submit_text').fadeIn();

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
                        $('#submit').html('Modifier');
                    }
                },
                error: function(data) {
                    console.log('Error:', data);
                    $('.loader').fadeOut();
                    $('#submit_text').fadeIn();

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
                    $('#submit').html('Modifier');
                }
            });
        });
    });
</script>
