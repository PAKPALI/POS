<form id="update_form">
    @csrf
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <div class="card-body text-center">
        @if ($Product->image)
            <img class="mb-5" src="{{ asset('images/' . $Product->image) }}" alt="Image du produit" style="width: 150px; height: auto;">
        @else
            Pas d'image
        @endif
        <div class="row">
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Catégorie</label>

                <select class="form-select mb-3 select2-category" name="category">
                    <option value="">selectionnez une catégorie</option>
                    @foreach ($Category as $cat)
                        <option value="{{ $cat->id }}" {{ $cat->id == $Product->category_id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-6 mb-3">
                <label for="supplier_id">Fournisseur</label>
                <select class="form-select mb-3" name="supplier_id">
                    <option value="">Aucun fournisseur</option>
                    @foreach ($Supplier as $sup)
                        <option value="{{ $sup->id }}" {{ $sup->id == $Product->supplier_id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Nom</label>
                <input type="text" name="name" value="{{$Product->name}}" class="form-control" id="exampleInputText0" placeholder="Nom">
            </div>
            <!-- <div class="form-group col-6">
                <label for="exampleInputText0">Quantité</label>
                <input type="number" name="qte" value="{{$Product->qte}}" class="form-control" id="exampleInputText0" placeholder="0">
            </div> -->
            <div class="form-group col-12 mb-3">
                <label for="exampleInputText0">Marge de sécurité</label>
                <input type="number" name="margin" value="{{$Product->margin}}" class="form-control" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Prix de vente</label>
                <input type="number" name="price" value="{{$Product->price}}" class="form-control price1" id="exampleInputText0" placeholder="0">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText0">Prix d'achat</label>
                <input type="number" name="purchase_price" value="{{$Product->purchase_price}}" class="form-control purchase_price1" id="exampleInputText0" placeholder="0">
            </div>

            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Bénefice</label>
                <input type="number" name="profit" value="{{$Product->profit}}" class="form-control profit1" id="exampleInputText0" readonly placeholder="0">
            </div>

            <div class="form-group col-6 mb-3">
                <label for="exampleInputText0">Prix TTC</label>
                <input type="number" class="form-control price_ttc1" readonly>
            </div>
            <div class="form-group col-12">
                <label class="form-label" for="smFile">Choisir une image</label>
                <input type="file" class="form-control form-control-sm" name="image" id="smFile">
            </div>
        </div>
    </div>
    <div class="saas-modal-actions">
        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">
            Annuler
        </button>
        <button id="submit" class="saas-btn saas-btn-warning" type="submit" data-loading-text="Enregistrement…">
            <i class="bi bi-check-lg" aria-hidden="true"></i>
            <span>Enregistrer</span>
        </button>
    </div>
</form>

<script>
    // Select2 is loaded in this file, so we can initialize it immediately
    setTimeout(function() {
        var $select = jQuery('.select2-category');
        
        if ($select.length && typeof jQuery.fn.select2 !== 'undefined') {
            $select.select2({
                dropdownParent: jQuery('#editModal'),
                width: '100%',
                placeholder: 'Sélectionnez une catégorie',
                allowClear: true
            });
        }
    }, 100);
    
    jQuery(function() {
        jQuery.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        jQuery('#submit').click(function(e) {
            e.preventDefault();
            var submitButton = document.getElementById('submit');
            if (window.ServerButtonLoader) window.ServerButtonLoader.start(submitButton, 'Enregistrement…');
            var formData = new FormData(jQuery('#update_form')[0]);

            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');

            jQuery.ajax({
                data: formData,
                url: '{{ url('component/product/' . $Product->id) }}',
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
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

                        jQuery('#editModal').modal('hide');
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
                error: function() {
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
                },
                complete: function() {
                    if (window.ServerButtonLoader) window.ServerButtonLoader.stop(submitButton);
                }
            });
        });

        let TAX = {{ \App\Models\AMS\Setting::first()->default_tax ?? 0 }};
        // when unit price and purchase price are updated
        jQuery('.price1, .purchase_price1').on('input', function() {
            // Récupérer les valeurs des champs
            var unitPrice = parseFloat(jQuery('.price1').val()) || 0;
            var purchasePrice = parseFloat(jQuery('.purchase_price1').val()) || 0;
            
            // Calculate profit
            var profit = unitPrice - purchasePrice;
            jQuery('.profit1').val(profit);

            var ttc = unitPrice + (unitPrice * TAX / 100);
            jQuery('.price_ttc1').val(ttc.toFixed(0));
        });
    });
</script>
