<form id="update_form">
    @csrf
    <div class="row g-3">
        <div class="col-md-6 saas-form-group">
            <label>Nom</label>
            <input type="text" name="name" value="{{ $User->name }}" placeholder="Nom complet" required>
        </div>
        <div class="col-md-6 saas-form-group">
            <label>Pays du numéro</label>
            <select name="country_code" id="userCountryEdit" required>
                @foreach(config('african_countries') as $iso => $countryName)
                    <option value="{{ $iso }}" @selected($iso === ($User->country_code ?? 'TG'))>{{ $countryName }} ({{ $iso }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 saas-form-group">
            <label>Numéro de téléphone local</label>
            <input type="tel" name="phone" value="{{ $User->phone }}" inputmode="numeric" placeholder="Ex. 90859488">
        </div>
        <div class="col-md-6 saas-form-group">
            <label>Rôle dans cette compagnie</label>
            <select name="role_id" id="edit_role_id" required>
                <option value="">Sélectionnez un rôle</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected($membership->role_id === $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
        <button id="submit" class="saas-btn saas-btn-warning" type="submit" data-loading-text="Enregistrement…">
            <i class="bi bi-check-lg"></i> Enregistrer
        </button>
    </div>
</form>

<script>
$(function() {
    $('#submit').on('click', function(e) {
        e.preventDefault();
        var button = this;
        window.ServerButtonLoader.withLoader(button, function() {
            return $.ajax({
                data: $('#update_form').serialize(),
                url: '{{ url("user/" . $User->id) }}',
                type: "PUT",
                dataType: 'json'
            });
        }, 'Enregistrement…').then(function(data) {
            if (data.status) {
                Swal.fire({ toast: true, position: 'top', icon: "success", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                $('#editModal').modal('hide');
                window.dispatchEvent(new Event('datatableUpdated'));
            } else {
                Swal.fire({ toast: true, position: 'top', icon: "error", title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
            }
        }).catch(function() {
            Swal.fire({ toast: true, position: 'top', icon: "error", title: 'Erreur', showConfirmButton: false, timer: 3000, timerProgressBar: true, text: 'Une erreur est survenue, veuillez réessayer.' });
        });
    });
});
</script>
