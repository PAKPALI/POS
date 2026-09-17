<form id="update_form">
    @csrf
    <div class="saas-form-grid">
        <x-ui.input id="edit-code-name-{{ $CodePromo->id }}" name="name" label="Nom" :value="$CodePromo->name" required />
        <x-ui.input id="edit-code-percent-{{ $CodePromo->id }}" name="percents" type="number" label="Pourcentage de remise" min="1" max="100" :value="$CodePromo->percents" required />
        <div class="saas-form-group"><label for="edit-code-value-{{ $CodePromo->id }}">Code</label><div class="saas-inline-actions"><input id="edit-code-value-{{ $CodePromo->id }}" type="text" name="code" value="{{ $CodePromo->code }}" maxlength="64" required><button class="saas-btn saas-btn-secondary generate-edit-code" type="button">Générer</button></div></div>
        <x-ui.input id="edit-code-expiry-{{ $CodePromo->id }}" name="expires_at" type="datetime-local" label="Expiration" :value="$CodePromo->expires_at?->format('Y-m-d\TH:i')" required />
        <x-ui.textarea id="edit-code-comments-{{ $CodePromo->id }}" name="comments" label="Description" placeholder="Ex. Remise de 10 % pour chaque client utilisant ce code avant la date d’expiration" rows="4" class="saas-form-group-wide">{{ $CodePromo->comments }}</x-ui.textarea>
    </div>
    <div class="saas-modal-actions">
        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" id="submit" class="saas-btn saas-btn-warning" data-loading-text="Enregistrement…">
            <i class="bi bi-check-lg" aria-hidden="true"></i><span>Enregistrer</span>
        </button>
    </div>
</form>

<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#update_form').on('submit', function(e) {
            e.preventDefault();
            var button = this.querySelector('[type="submit"]');
            window.ServerButtonLoader.withLoader(button, function() {
                return $.ajax({ data: $('#update_form').serialize(), url: '{{ url('code/code/' . $CodePromo->id) }}', type: 'PUT', dataType: 'json' });
            }, 'Enregistrement…').then(function(data) {
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

                        $('#editModal').modal('hide');
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
                }).catch(function() {
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
                });
        });
        $('.generate-edit-code').on('click', function() {
            $('#edit-code-value-{{ $CodePromo->id }}').val(Array.from({length:7},()=> 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'[Math.floor(Math.random()*36)]).join(''));
        });
    });
</script>
