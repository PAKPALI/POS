@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <h1 class="page-header">Super utilisateur - Quotas SMS</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <form id="quota-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sms_count" class="form-label">Nombre de SMS classiques</label>
                                <input type="number" name="sms_count" id="sms_count" value="{{ $company->sms_count ?? 0 }}" min="0" class="form-control" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="whatsapp_count" class="form-label">Nombre de SMS WhatsApp</label>
                                <input type="number" name="whatsapp_count" id="whatsapp_count" value="{{ $company->whatsapp_count ?? 0 }}" min="0" class="form-control" />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer les quotas</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">SMS classiques restants</h5>
                            <p class="display-5">{{ $company->sms_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">WhatsApp restants</h5>
                            <p class="display-5">{{ $company->whatsapp_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#quota-form').submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: '{{ route('sms-quota.update') }}',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: response.title,
                            text: response.msg,
                            timer: 2500,
                            showConfirmButton: false,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.title,
                            text: response.msg,
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de mettre à jour les quotas.',
                    });
                }
            });
        });
    });
</script>
@endsection