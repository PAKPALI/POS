<form id="update_form">
    @csrf
    <div class="row">
        <div class="col-md-6 saas-form-group">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" value="{{$Client->name}}" placeholder="Nom du client" required autofocus>
        </div>
        <div class="col-md-6 saas-form-group">
            <label for="clientCountryEdit">Pays du numéro</label>
            <select name="country_code" id="clientCountryEdit" class="form-select country-select" data-placeholder="Rechercher un pays" required>
                @foreach(config('african_countries') as $iso => $countryName)
                    <option value="{{ $iso }}" @selected($iso === ($Client->country_code ?? 'TG'))>{{ $countryName }} ({{ $iso }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 saas-form-group">
            <label for="phone">Téléphone local</label>
            <input type="tel" name="phone" id="phone" value="{{$Client->phone}}" inputmode="numeric" pattern="[0-9]{6,15}" minlength="6" maxlength="15" placeholder="Ex. 90000000">
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
        $('#update_form').submit(function(event) {
            event.preventDefault();
            const form = this;

            window.ServerButtonLoader.withLoader($(form).find('[type="submit"]')[0], function() {
                return $.ajax({
                    data: $(form).serialize(),
                    url: '{{ url('component/client/' . $Client->id) }}',
                    type: 'PUT',
                    dataType: 'json'
                });
            }, 'Enregistrement…').then(function(data) {
                if (data.status) {
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                    $('#editModal').modal('hide');
                    window.dispatchEvent(new Event('datatableUpdated'));
                } else {
                    Swal.fire({ toast: true, position: 'top', icon: 'error', title: data.title, showConfirmButton: false, timer: 3000, timerProgressBar: true, text: data.msg });
                }
            }).catch(function(xhr) {
                const response = xhr.responseJSON || {};
                const validationErrors = response.errors ? Object.values(response.errors) : [];
                const firstValidationError = validationErrors.length
                    ? (Array.isArray(validationErrors[0]) ? validationErrors[0][0] : validationErrors[0])
                    : null;
                Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Modification impossible', showConfirmButton: false, timer: 3000, timerProgressBar: true, text: response.msg || response.message || firstValidationError || 'Une erreur est survenue, veuillez réessayer.' });
            });
        });
    });
</script>
