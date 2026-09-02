<form id="update_form">
    @csrf
    <div class="row">
        <div class="col-md-12 saas-form-group">
            <label for="cash-name">Nom de la caisse</label>
            <input id="cash-name" type="text" name="name" value="{{ $cashAccount->name }}" required autofocus>
        </div>
        <div class="col-md-4 saas-form-group">
            <label class="saas-switch-line" for="cash-is-default"><span><strong>Caisse principale</strong><small>Utilisée par défaut pour les ventes</small></span><input class="saas-switch-input cash-role-toggle" type="checkbox" name="is_default" id="cash-is-default" value="1" {{ $cashAccount->is_default ? 'checked' : '' }}><span class="saas-switch-control" aria-hidden="true"></span></label>
        </div>
        <div class="col-md-4 saas-form-group">
            <label class="saas-switch-line" for="cash-is-tax"><span><strong>Caisse de taxe</strong><small>Réservée aux opérations de taxe</small></span><input class="saas-switch-input cash-role-toggle" type="checkbox" name="is_tax" id="cash-is-tax" value="1" {{ $cashAccount->is_tax ? 'checked' : '' }}><span class="saas-switch-control" aria-hidden="true"></span></label>
        </div>
        <div class="col-md-4 saas-form-group">
            <label class="saas-switch-line" for="cash-status"><span><strong>Statut</strong><small>Autoriser l’utilisation de cette caisse</small></span><input class="saas-switch-input" type="checkbox" name="status" id="cash-status" value="1" {{ $cashAccount->status ? 'checked' : '' }}><span class="saas-switch-control" aria-hidden="true"></span></label>
        </div>
        <div class="col-md-12 saas-form-group mt-3">
            <label>Description</label>
            <textarea name="description">{{ $cashAccount->description }}</textarea>
        </div>
    </div>
    <div class="saas-modal-actions">
        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
        <button class="saas-btn saas-btn-warning" type="submit" data-loading-text="Enregistrement…">
            <i class="bi bi-check-lg" aria-hidden="true"></i><span>Enregistrer</span>
        </button>
    </div>
</form>

<script>
$(function() {
    $(document).on('change', '.cash-role-toggle', function () {
        if (this.checked) {
            const otherName = this.name === 'is_default' ? 'is_tax' : 'is_default';
            $(this).closest('form').find('input[name="' + otherName + '"]').prop('checked', false);
        }
    });

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('#update_form').submit(function(e) {
        e.preventDefault();
        var form = this;
        var button = $(form).find('[type="submit"]');
        var formData = new FormData(form);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');

        $.ajax({
            data: formData,
            url: "{{ url('ams/cash-account/' . $cashAccount->id) }}",
            type: "POST",
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function() {
                if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Modification en cours…');
            },
            success: function(data) {
                if (data.status) {
                    Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, text: data.msg, timer: 3000, showConfirmButton: false });
                    $('#editModal').modal('hide');
                    window.dispatchEvent(new Event('datatableUpdated'));
                } else {
                    Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, text: data.msg, timer: 3000, showConfirmButton: false });
                }
            },
            error: function() {
                Swal.fire({ toast: true, position: 'top', icon: "error", title: "Erreur", text: "Impossible de mettre à jour la caisse." });
            },
            complete: function() {
                if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]);
            }
        });
    });
});
</script>
