<form id="update_form">
    @csrf
    <div class="card-body text-center">
        @if ($Product->image)
            <img class="mb-5" src="{{ asset('images/' . $Product->image) }}" alt="Image du produit" style="width: 150px; height: auto;">
        @else
            Pas d'image
        @endif
        <div class="row">
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Catégorie</label>

                <select class="form-select mb-3" name="category">
                    <option value="">selectionnez une catégorie</option>
                    @foreach ($Category as $cat)
                        <option value="{{ $cat->id }}" {{ $cat->id == $Product->category_id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Nom</label>
                <input type="text" name="name" value="{{$Product->name}}" class="form-control" id="exampleInputText0" placeholder="Nom">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText0">Quantité</label>
                <input type="number" name="qte" value="{{$Product->qte}}" class="form-control" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Marge de sécurité</label>
                <input type="number" name="margin" value="{{$Product->margin}}" value="0" class="form-control" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Prix unitaire</label>
                <input type="number" name="price" value="{{$Product->price}}" class="form-control" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText0">Prix d'achat</label>
                <input type="number" name="purchase_price" value="{{$Product->purchase_price}}" class="form-control" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-12 mb-3">
                <label for="exampleInputText0">Bénefice</label>
                <input type="number" name="profit" value="{{$Product->profit}} FCFA" class="form-control" id="exampleInputText0" readonly placeholder="0">
            </div>
            <div class="form-group col-12">
                <label class="form-label" for="smFile">Choisir une image</label>
                <input type="file" class="form-control form-control-sm" name="image" id="smFile">
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
                url: '{{ url('component/product/' . $Product->id) }}',
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