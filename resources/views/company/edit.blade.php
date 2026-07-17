<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-6">
                <label for="exampleInputText0">Nom</label>
                <input type="text" name="name" value="{{$Company->name}}" class="form-control" id="exampleInputText0" placeholder="Nom">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText1">Email</label>
                <input type="text" name="email" value="{{$Company->email}}" class="form-control" id="exampleInputText1" placeholder="Email">
            </div>
        </div>

        <div class="row mt-4">
            <div class="form-group col-6">
                <label for="exampleInputText2">Numéro 1</label>
                <input type="text" name="number1" value="{{$Company->number1}}" class="form-control" id="exampleInputText2" placeholder="Numéro 1">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText3">Numéro 2</label>
                <input type="text" name="number2" value="{{$Company->number2}}" class="form-control" id="exampleInputText3" placeholder="Numéro 2">
            </div>
        </div>

        <div class="row mt-4">
            <div class="form-group col-6">
                <label for="exampleInputText4">Adresse</label>
                <input type="text" name="adress" value="{{$Company->adress}}" class="form-control" id="exampleInputText4" placeholder="Adresse">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText5">Message</label>
                <input type="text" name="message" value="{{$Company->message}}" class="form-control" id="exampleInputText5" placeholder="Message">
            </div>
        </div>

        <div class="row mt-4">
            <div class="form-group col-6">
                <label for="logo">Logo</label>
                <input type="file" name="logo" class="form-control" id="logo" accept="image/*">
                @if($Company->logo)
                    <div class="mt-2">
                        <img src="{{asset($Company->logo)}}" alt="Logo" style="max-height:60px;">
                    </div>
                @endif
            </div>
            <div class="form-group col-6">
                <label for="ecommerce_active" class="d-block">Boutique en ligne</label>
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" name="ecommerce_active" class="form-check-input" id="ecommerce_active" value="1" {{$Company->ecommerce_active ? 'checked' : ''}}>
                    <label class="form-check-label" for="ecommerce_active">Activer</label>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="form-group col-12">
                <label for="description">Description (pour le site ecommerce)</label>
                <textarea name="description" class="form-control" id="description" rows="3" placeholder="Description de l'entreprise">{{$Company->description}}</textarea>
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
            
            var formData = new FormData($('#update_form')[0]);
            formData.append('_method', 'PUT');
            $.ajax({
                data: formData,
                url: '{{ url('setting/company/' . $Company->id) }}',
                type: "POST",
                dataType: 'json',
                contentType: false,
                processData: false,
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
