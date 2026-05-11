<form id="update_form">
    @csrf

    <div class="card-body text-center">

        <!-- NOM CAISSE -->
        <div class="row">

            <div class="col-md-12 mb-3">
                <label>Nom de la caisse</label>
                <input type="text" name="name" value="{{ $cashAccount->name }}" class="form-control" required>
            </div>

            <!-- TOGGLE DEFAULT -->
            <div class="col-md-4 mt-3">
                <label class="form-label">Caisse principale</label>
                <div class="d-flex justify-content-center align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                            type="checkbox"
                            name="is_default"
                            value="1"
                            {{ isset($cashAccount) && $cashAccount->is_default ? 'checked' : '' }}>
                    </div>
                </div>
                <!-- <div class="text-center small text-muted mt-1">
                    Activer comme caisse par défaut
                </div> -->
            </div>

            <!-- TOGGLE TAX -->
            <div class="col-md-4 mt-3">
                <label class="form-label">Caisse de taxe</label>
                <div class="d-flex justify-content-center align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_tax" value="1"
                            {{ isset($cashAccount) && $cashAccount->is_tax ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            <!-- TOGGLE STATUS -->
            <div class="col-md-4 mt-3">
                <label class="form-label">Statut</label>
                <div class="d-flex justify-content-center align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                            type="checkbox"
                            name="status"
                            value="1"
                            {{ isset($cashAccount) && $cashAccount->status ? 'checked' : '' }}>
                    </div>
                </div>
                <!-- <div class="text-center small text-muted mt-1">
                    Caisse active / inactive
                </div> -->
            </div>

            <!-- DESCRIPTION -->
            <div class="col-md-12 mt-3">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ $cashAccount->description }}</textarea>
            </div>
        </div>

    </div>

    <!-- BUTTON -->
    <div class="card-footer mt-4">
        <button id="submit" class="btn btn-warning" type="submit">
            <div class="loader spinner-grow" style="display:none;"></div>
            <span id="submit_text">Modifier</span>
        </button>
    </div>
</form>

<script>
$(function() {

    $('.loader').hide();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#submit').click(function(e) {
        e.preventDefault();

        $('.loader').fadeIn();
        $('#submit_text').hide();

        var formData = new FormData($('#update_form')[0]);

        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');

        $.ajax({
            data: formData,
            url: "{{ url('ams/cash-account/' . $cashAccount->id) }}",
            type: "POST",
            processData: false,
            contentType: false,
            dataType: "json",

            success: function(data) {

                $('.loader').fadeOut();
                $('#submit_text').fadeIn();

                if (data.status) {

                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "success",
                        title: data.title,
                        text: data.msg,
                        timer: 3000,
                        showConfirmButton: false,
                    });

                    $('#editModal').modal('hide');

                    window.dispatchEvent(new Event('datatableUpdated'));

                } else {

                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: "error",
                        title: data.title,
                        text: data.msg,
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },

            error: function() {

                $('.loader').fadeOut();
                $('#submit_text').fadeIn();

                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: "error",
                    title: "Erreur",
                    text: "Impossible de mettre à jour la caisse",
                });
            }
        });
    });

});
</script>