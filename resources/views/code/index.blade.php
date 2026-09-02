@extends('layouts.saas')

@section('title', 'Codes promotionnels')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-18" rel="stylesheet">
@endpush

@section('content')
<div class="saas-page-heading">
    <div><span class="saas-eyebrow"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Catalogue</span><h1>Codes promotionnels</h1><p>Créez, consultez et gérez les remises appliquées lors des ventes.</p></div>
    <x-ui.button variant="primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg" aria-hidden="true"></i> Ajouter un code</x-ui.button>
</div>

<x-ui.modal id="addModal" title="Ajouter un code promotionnel" eyebrow="Catalogue" size="lg">
    <form id="add">
        @csrf
        <div class="saas-form-grid">
            <x-ui.input id="code-name" name="name" label="Nom" placeholder="Nom de la campagne" required />
            <x-ui.input id="code-percent" name="percents" type="number" label="Pourcentage" placeholder="Ex. 10" min="0" max="100" required />
            <div class="saas-form-group"><label for="code">Code</label><div class="saas-inline-actions"><input id="code" type="text" name="code" placeholder="Générez ou saisissez un code" readonly required><button id="generateCode" class="saas-btn saas-btn-secondary" type="button">Générer</button></div></div>
            <x-ui.textarea id="code-comments" name="comments" label="Description" placeholder="Conditions ou contexte de la remise" rows="4" class="saas-form-group-wide" />
        </div>
        <div class="saas-modal-actions"><button class="saas-btn saas-btn-ghost" type="button" data-bs-dismiss="modal">Annuler</button><x-ui.button type="submit" variant="primary" data-loading-text="Création…"><i class="bi bi-check-lg" aria-hidden="true"></i> Créer le code</x-ui.button></div>
    </form>
</x-ui.modal>

<x-ui.modal id="editModal" title="Modifier le code promo" eyebrow="Catalogue" variant="warning" size="lg"><div id="edit_response"><x-ui.skeleton :lines="4" /></div></x-ui.modal>
<x-ui.modal id="showModal" title="Détail du code promo" eyebrow="Catalogue" size="lg"><div id="show_response"><x-ui.skeleton :lines="3" /></div></x-ui.modal>

<x-ui.card title="Liste des codes promotionnels" description="Utilisez la recherche, le tri et la pagination sans quitter cet écran.">
    <x-ui.table-shell><x-slot:table id="datatable" class="w-100"><thead><tr><th>#</th><th>Nom</th><th>Code</th><th>Pourcentage</th><th>Description</th><th>Créé par</th><th>Créé le</th><th>Statut</th><th>Actions</th></tr></thead><tbody></tbody></x-slot:table></x-ui.table-shell>
</x-ui.card>
@endsection

@push('scripts')
<script src="{{ asset('hub/assets/plugins/datatables.net/js/dataTables.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script>
$(function () {
    const table = $('#datatable').DataTable({
        processing:true, serverSide:true, responsive:true, ajax:"{{ route('code.index') }}",
        columns:[{data:'id',name:'id'},{data:'name',name:'name'},{data:'code',name:'code'},{data:'percents',name:'percents'},{data:'comments',name:'comments'},{data:'created_by',name:'created_by'},{data:'created_at',name:'created_at'},{data:'status',name:'status'},{data:'action',name:'action',orderable:false,searchable:false}],
        language:{lengthMenu:'Afficher _MENU_ entrées',zeroRecords:'Aucune donnée disponible',info:'Affichage de _START_ à _END_ sur _TOTAL_ entrées',infoEmpty:'Affichage de 0 à 0 sur 0 entrées',infoFiltered:'(filtré à partir de _MAX_ entrées au total)',search:'Rechercher:',paginate:{first:'Premier',last:'Dernier',next:'Suivant',previous:'Précédent'}}
    });
    window.addEventListener('datatableUpdated', () => table.ajax.reload(null, false));
    $('#add').on('submit', function (event) {
        event.preventDefault(); const button=this.querySelector('[type=submit]');
        window.ServerButtonLoader.withLoader(button,$.ajax({type:'POST',url:"{{ route('code.store') }}",data:$(this).serialize(),dataType:'json'}),'Création…')
            .then(data=>{if(!data.status)throw new Error(data.msg||'Création impossible');bootstrap.Modal.getOrCreateInstance(document.getElementById('addModal')).hide();table.draw(false);return Swal.fire({toast:true,position:'top',icon:'success',title:data.title,showConfirmButton:false,timer:3000,text:data.msg});})
            .catch(error=>Swal.fire({icon:'error',title:'Création impossible',text:error.message||'Impossible de communiquer avec le serveur.'}));
    });
    $('#generateCode').on('click',()=>$('#code').val(Array.from({length:7},()=> 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'[Math.floor(Math.random()*36)]).join('')));
    $('body').on('click','.editModal,.view',function(){const id=$(this).data('id'),editing=$(this).hasClass('editModal'),target=editing?'#edit_response':'#show_response';$(target).html('<div class="saas-empty-state is-compact">Chargement…</div>');bootstrap.Modal.getOrCreateInstance(document.getElementById(editing?'editModal':'showModal')).show();$.ajax({url:"{{ url('code/code') }}/"+id+(editing?'/edit':''),dataType:'html'}).done(html=>$(target).html(html)).fail(()=>$(target).html('<div class="saas-alert saas-alert-danger" role="alert">Chargement impossible. Réessayez.</div>'));});
    $('body').on('click','.pdf',function(){window.location.href='/code/code-promo/'+$(this).data('id')+'/pdf';});
    function confirmMutation(selector,title,method,variant){$('body').on('click',selector,function(){const trigger=this,id=$(this).data('id');Swal.fire({icon:'warning',title,text:'Cette action modifie l’état du code promotionnel.',showCancelButton:true,confirmButtonText:'Continuer',cancelButtonText:'Annuler',buttonsStyling:false,customClass:{confirmButton:'saas-btn '+variant,cancelButton:'saas-btn saas-btn-ghost'}}).then(result=>{if(!result.isConfirmed)return;window.ServerButtonLoader.withLoader(trigger,$.ajax({headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')},url:"{{ url('code/code') }}/"+id,type:method,dataType:'json'}),'Traitement…').then(data=>{if(!data.status)throw new Error(data.msg||'Action impossible');table.draw(false);return Swal.fire({toast:true,position:'top',icon:'success',title:data.title,showConfirmButton:false,timer:3000,text:data.msg});}).catch(error=>Swal.fire({icon:'error',title:'Action impossible',text:error.message||'Impossible de communiquer avec le serveur.'}));});});}
    confirmMutation('.archive','Désactiver ce code ?','DELETE','saas-btn-danger');
    confirmMutation('.restore','Restaurer ce code ?','DELETE','saas-btn-primary');
});
</script>
@endpush
