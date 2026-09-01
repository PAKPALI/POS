<form id="update_form">
    @csrf
    <div class="row">
        <div class="col-md-12 saas-form-group">
            <label for="cash-name">Nom de la caisse</label>
            <input id="cash-name" type="text" name="name" value="{{ $cashAccount->name }}" required autofocus>
        </div>
        <div class="col-md-4 saas-form-group">
            <label>Caisse principale</label>
            <div class="form-check form-switch" style="margin-top: 6px;">
                <input class="form-check-input cash-role-toggle" type="checkbox" name="is_default" value="1"
                    {{ $cashAccount->is_default ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-md-4 saas-form-group">
            <label>Caisse de taxe</label>
            <div class="form-check form-switch" style="margin-top: 6px;">
                <input class="form-check-input cash-role-toggle" type="checkbox" name="is_tax" value="1"
                    {{ $cashAccount->is_tax ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-md-4 saas-form-group">
            <label>Statut</label>
            <div class="form-check form-switch" style="margin-top: 6px;">
                <input class="form-check-input" type="checkbox" name="status" value="1"
                    {{ $cashAccount->status ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-md-12 saas-form-group mt-3">
            <label>Description</label>
            <textarea name="description">{{ $cashAccount->description }}</textarea>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
        <button class="saas-btn saas-btn-warning" type="submit" data-loading-text="Modification en cours…">
            <i class="bi bi-pencil" aria-hidden="true"></i><span>Modifier</span>
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
