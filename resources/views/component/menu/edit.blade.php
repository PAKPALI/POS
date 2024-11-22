<form id="update_form">
    @csrf
    <div class="card-body text-center">
         @if ($Product->image)
            <img class="mb-5" src="{{ asset('images/' . $Product->image) }}" alt="Image du produit" style="width: 150px; height: auto;">
        @else
            Pas d'image
        @endif
        <div class="row">
            <input type="hidden" name="type" value="2" class="form-control" id="exampleInputText0" placeholder="0">
            <div class="form-group col-12 mb-3 text-center bg-light">
                <label for="exampleInputText0"><h5 class="text-dark">informations menu</h5></label>
            </div>
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
                <input type="number" name="margin" value="{{$Product->margin}}" class="form-control" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Prix unitaire</label>
                <input type="number" name="price" value="{{$Product->price}}" class="form-control price1" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText0">Prix d'achat</label>
                <input type="number" name="purchase_price" value="{{$Product->purchase_price}}" class="form-control purchase_price1" id="exampleInputText0" placeholder="0">
            </div>

            <div class="form-group col-12 mb-3">
                <label for="exampleInputText0">Bénefice</label>
                <input type="number" name="profit" value="{{$Product->profit}}" class="form-control profit1" id="exampleInputText0" readonly placeholder="0">
            </div>
            <div class="form-group col-12">
                <label class="form-label" for="smFile">Choisir une image</label>
                <input type="file" class="form-control form-control-sm" name="image" id="smFile">
            </div>
        </div>

        <div class="row mt-3">
            <div class="form-group col-12 mb-3 text-center bg-light">
                <label for="exampleInputText0"><h5 class="text-dark">informations produits</h5></label>
            </div>
            <button type="button" class="btn btn-info add-product-field2 mb-2">
                <i class="fa fa-plus"></i> Ajouter un produit
            </button>
            <div id="product-fields-container2">
                <!-- Dynamic field will be here -->
                @foreach($MenuProduct as $mp)
                    <div class="row product-field2">
                        <div class="form-group col-5 mb-2 mt-2">
                            <label for="exampleInputText0">Produits </label>
                            <select class="form-select mb-3 product-select2" name="products[]">
                                <option value="">selectionnez un produit </option>
                                @foreach ($Products as $product)
                                    <option value="{{ $product->id }}" {{ $product->id == $mp->product_id ? 'selected' : '' }}>
                                        {{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-6 mb-2 mt-2">
                            <label for="exampleInputText0">Quantité</label>
                            <input type="number" name="quantities[]" value="{{$mp->quantity}}" class="form-control product-quantity2" placeholder="0">
                        </div>

                        <div class="form-group col-1 mb-2 mt-2 d-flex align-items-center">
                            <button type="button" class="btn btn-danger remove-product-field">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
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

<!-- template for dynamic field -->
<template id="product-field-template2">
    <div class="row product-field2">
        <div class="form-group col-5 mb-2">
            <label for="exampleInputText0">Produits</label>
            <select class="form-select mb-3 product-select2" name="products[]">
                <option value="">selectionnez un produit</option>
                @foreach ($Products as $product)
                    <option value="{{$product->id}}">{{$product->name}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-6 mb-2">
            <label for="exampleInputText0">Quantité</label>
            <input type="number" name="quantities[]" class="form-control product-quantity2" placeholder="0">
        </div>

        <div class="form-group col-1 mb-2 d-flex align-items-center">
            <button type="button" class="btn btn-danger remove-product-field">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
    $(function() {
        // Cache le loader au chargement de la page
        $('.loader').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Add product field
        $('.add-product-field2').on('click', function () {
            let template = $('#product-field-template2').html(); // get template model
            // template exist on index blade
            $('#product-fields-container2').append(template);   // Add template in container
        });

        // Delete product field
        $('#product-fields-container2').on('click', '.remove-product-field', function () {
            $(this).closest('.product-field2').remove(); // Delete parent bloc
        });

        $('#submit').click(function(e) {
            e.preventDefault();
            // Affiche le loader et remplace le texte du bouton
            $('.loader').fadeIn();
            $('#submit_text').hide();
            let isValid = true; 
            let products = [];

            // Construire la liste des produits
            $('.product-field2').each(function () {
                let productSelect = $(this).find('.product-select2').val();
                let productQuantity = $(this).find('.product-quantity2').val();

                // if (!productSelect && !productQuantity) {
                //     return true; // Ignore les champs vides
                // }

                if (!productSelect) {
                    alert("Veuillez sélectionner un produit !")
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: "ERREUR",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        text: "Veuillez sélectionner un produit !",
                    });
                    isValid = false;
                    return false;
                }

                if (!productQuantity || productQuantity <= 0) {
                    alert("Veuillez saisir une quantité valide !")
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: "ERREUR",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        text: "Veuillez saisir une quantité valide !",
                    });
                    isValid = false;
                    return false;
                }

                products.push({
                    product_id: productSelect,
                    quantity: parseInt(productQuantity)
                });
            });

            // Vérifier si la validation a échoué ou si aucun produit n'a été ajouté
            if (!isValid || products.length === 0) {
                $('.loader').hide();
                $('#submit_text').fadeIn();
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: "error",
                    title: "ERREUR",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    text: "Ajoutez au moins un produit valide avant de soumettre !",
                });
                return false;
            }

            // Préparer les données du formulaire
            let formData = new FormData($('#update_form')[0]);

            // Ajouter les produits dans le FormData
            products.forEach((product, index) => {
                formData.append(`products[${index}][product_id]`, product.product_id);
                formData.append(`products[${index}][quantity]`, product.quantity);
            });

            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');

            $.ajax({
                data: formData,
                url: '{{ url('component/menu/' . $Product->id) }}',
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
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

        // when unit price and purchase price are updated
        $('.price1, .purchase_price1').on('input', function() {
            // Récupérer les valeurs des champs
            var unitPrice = parseFloat($('.price1').val()) || 0;
            var purchasePrice = parseFloat($('.purchase_price1').val()) || 0;
            
            // Calculate profit
            var profit = unitPrice - purchasePrice;

            // Display result in profit field
            $('.profit1').val(profit);
        });
    });
</script>