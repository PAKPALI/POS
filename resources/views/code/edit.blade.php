<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-6">
                <label for="exampleInputText0">Nom</label>
                <input type="text" name="name" class="form-control" id="exampleInputText0"
                    placeholder="Nom" value="{{$CodePromo->name}}">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText0">Pourcentage</label>
                <input type="number" name="percents" class="form-control" id="exampleInputText0"
                    placeholder="Votre pourcentage" value="{{$CodePromo->percents}}">
            </div>
        </div>
        <div class="row mt-3">
            <div class="form-group col-11">
                <label for="exampleInputText0">Code</label>
                <input id="code" type="text" name="code" class="form-control" id="exampleInputText0"
                    placeholder="Code" value="{{$CodePromo->code}}">
            </div>
            <div class="form-group col-1 text-end">
                <label for="exampleInputText0"></label>
                <div>
                    <a id="generateCode" class="btn btn-secondary">
                        <!-- <div id="" class="spinner-grow"></div> -->
                        <div id="">Générer</div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="form-group col-12">
                <label for="exampleInputText0">Description</label>
                <textarea name="comments" class="form-control" placeholder="Votre description" id="exampleInputText">
                    {{$CodePromo->comments}}
                </textarea>
            </div>
        </div>
    </div>
    <div class="card-footer mt-4">
        <button type="" id="submit" class="btn btn-warning">
            <div id="loader2" class="spinner-grow"></div>
            <div id="submitText">Modifier</div>
        </button>
    </div>
</form>

<script>
    $(function() {
        // Cache le loader au chargement de la page
        $('#loader2').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#submit').click(function(e) {
            e.preventDefault();

            // Affiche le loader et remplace le texte du bouton
            $('#loader2').fadeIn();
            $('#submit_text').hide();
            
            $.ajax({
                data: $('#update_form').serialize(),
                url: '{{ url('code/code/' . $CodePromo->id) }}',
                type: "PUT",
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        console.log(data);
                        // Cache le loader et remet le texte "Modifier"
                        $('#loader2').fadeOut();
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
                        $('#loader2').fadeOut();
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
                    $('#loader2').fadeOut();
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