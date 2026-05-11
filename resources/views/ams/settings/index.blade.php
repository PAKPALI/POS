@extends('layouts.layout')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-8">

            <h1 class="page-header">PARAMÈTRES AMS</h1>
            <hr>

            <div class="card">
                <div class="card-body text-center">

                    <!-- ICON -->
                    <div class="mb-4">
                        <i class="bi bi-tools" style="font-size: 60px; color: #efeeec;"></i>
                    </div>

                    <form id="settingsForm">
                        @csrf

                        <div class="row">

                            <div class="col-md-12 mb-3">
                                <label>Caisse par défaut</label>
                                <select name="default_cash_id" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($cashes as $cash)
                                        <option value="{{ $cash->id }}"
                                            @if(isset($setting) && $setting->default_cash_id == $cash->id) selected @endif>
                                            {{ $cash->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Taxe par défaut (%)</label>
                                <input type="number" step="0.01" name="default_tax"
                                    value="{{ $setting->default_tax ?? 0 }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Caisse TAXE</label>
                                <select name="tax_cash_id" class="form-select">
                                    <option value="">-- Aucune --</option>
                                    @foreach($cashes as $cash)
                                        <option value="{{ $cash->id }}"
                                            @if(isset($setting) && $setting->tax_cash_id == $cash->id) selected @endif>
                                            {{ $cash->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <div id="loader" class="spinner-grow"></div>
                            <span id="submitText">Enregistrer</span>
                        </button>

                    </form>

                </div>

                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
$(function(){

    $('#loader').hide();

    $('#settingsForm').submit(function(e){
        e.preventDefault();

        $('#loader').show();
        $('#submitText').hide();

        $.ajax({
            type: 'POST',
            url: "{{ route('ams.settings.store') }}",
            data: $(this).serialize(),

            success: function(data){

                $('#loader').hide();
                $('#submitText').show();

                if(data.status){
                    Swal.fire({
                        toast:true,
                        position: 'top',
                        icon: "success",
                        title: "Succès",
                        text: data.msg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }else{
                    Swal.fire({
                        icon: "error",
                        title: "Erreur",
                        text: data.msg
                    });
                }
            },

            error: function(){
                $('#loader').hide();
                $('#submitText').show();

                Swal.fire({
                    icon: "error",
                    title: "Erreur serveur"
                });
            }
        });
    });

});
</script>

@endsection