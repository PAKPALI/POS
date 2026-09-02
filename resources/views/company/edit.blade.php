<form id="update_form" enctype="multipart/form-data">
    @csrf
    <div class="saas-form-grid">
        <div class="saas-form-group"><label for="company_name">Nom de l’entreprise</label><input id="company_name" name="name" value="{{ $Company->name }}" required></div>
        <div class="saas-form-group"><label for="company_email">E-mail</label><input id="company_email" name="email" type="email" value="{{ $Company->email }}" required></div>
        <div class="saas-form-group"><label for="company_number1">Téléphone principal</label><input id="company_number1" name="number1" value="{{ $Company->number1 }}" required></div>
        <div class="saas-form-group"><label for="company_number2">Téléphone secondaire</label><input id="company_number2" name="number2" value="{{ $Company->number2 }}"></div>
        <div class="saas-form-group"><label for="company_address">Adresse</label><input id="company_address" name="adress" value="{{ $Company->adress }}"></div>
        <div class="saas-form-group"><label for="company_message">Message d’accueil</label><input id="company_message" name="message" value="{{ $Company->message }}"></div>
        <div class="saas-form-group"><label for="company_logo">Logo <span class="saas-help">PNG, JPG ou WebP, 2 Mo maximum</span></label><input id="company_logo" name="logo" type="file" accept="image/*">@if($Company->logo)<img class="saas-company-logo-preview" src="{{ asset($Company->logo) }}" alt="Logo actuel de {{ $Company->name }}">@endif</div>
        <div class="saas-form-group"><label class="saas-switch-line" for="ecommerce_active"><span><strong>Boutique E-commerce</strong><small>Rendre la boutique publique accessible</small></span><input class="saas-switch-input" type="checkbox" name="ecommerce_active" id="ecommerce_active" value="1" {{ $Company->ecommerce_active ? 'checked' : '' }}><span class="saas-switch-control" aria-hidden="true"></span></label></div>
        <div class="saas-form-group saas-form-group-wide"><label for="company_description">Description de la boutique</label><textarea id="company_description" name="description" rows="3">{{ $Company->description }}</textarea></div>
    </div>
    <div class="saas-modal-actions"><button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button><button id="submit" class="saas-btn saas-btn-primary" type="submit" data-loading-text="Enregistrement…"><i class="bi bi-check-lg" aria-hidden="true"></i> Enregistrer</button></div>
</form>
<script>
$('#update_form').on('submit',function(e){e.preventDefault();const form=this,button=document.getElementById('submit'),data=new FormData(form);data.append('_method','PUT');window.ServerButtonLoader.withLoader(button,fetch('{{ url('setting/company/' . $Company->id) }}',{method:'POST',body:data,headers:{Accept:'application/json'}}).then(r=>r.json()).then(result=>{if(!result.status)throw new Error(result.msg||'Enregistrement impossible');return Swal.fire({toast:true,position:'top',icon:'success',title:'Enregistré',text:result.msg,showConfirmButton:false,timer:1800}).then(()=>location.reload())})).catch(error=>Swal.fire({icon:'error',title:'Enregistrement impossible',text:error.message}))});
</script>
