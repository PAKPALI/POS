<form id="update_form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-6">
                <label for="exampleInputText0">Nom</label>
                <input type="text" name="name" class="form-control" id="exampleInputText0"
                    placeholder="Nom" value="{{$CodePromo->name}}">
            </div>
            <div class="form-group col-6">
                <label for="exampleInputText0">Pourcentage</label>
                <input type="number" name="percents" class="form-control" id="exampleInputText0"
                    placeholder="Votre pourcentage" value="{{$CodePromo->percents}}">
            </div>
        </div>
        <div class="row mt-3">
            <div class="form-group col-11">
                <label for="exampleInputText0">Code</label>
                <input id="code" type="text" name="code" class="form-control" id="exampleInputText0"
                    placeholder="Code" value="{{$CodePromo->code}}">
            </div>
            <div class="form-group col-1 text-end">
                <label for="exampleInputText0"></label>
                <div>
                    <a id="generateCode" class="btn btn-secondary">
                        <div id="">Générer</div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="form-group col-12">
                <label for="exampleInputText0">Description</label>
                <textarea name="comments" class="form-control" placeholder="Votre description" id="exampleInputText">
                    {{$CodePromo->comments}}
                </textarea>
            </div>
        </div>
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
    });
</script>
