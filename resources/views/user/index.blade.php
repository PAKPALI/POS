@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-17" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Équipe</h1>
            <p>Gérez les membres, invitations et accès de votre entreprise.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="saas-btn saas-btn-outline {{ !$canAddUser ? 'is-disabled' : '' }}" @if(!$canAddUser) aria-disabled="true" data-limit-message="La limite d’utilisateurs de votre plan d’abonnement est atteinte." @else data-bs-toggle="modal" data-bs-target="#inviteUserModal" @endif>
                <i class="bi bi-envelope-plus"></i> Inviter par e-mail
            </button>
            <button type="button" class="saas-btn saas-btn-secondary {{ !$canAddUser ? 'is-disabled' : '' }}" @if(!$canAddUser) aria-disabled="true" data-limit-message="La limite d’utilisateurs de votre plan d’abonnement est atteinte." @else data-bs-toggle="modal" data-bs-target="#attachExistingModal" @endif>
                <i class="bi bi-person-plus"></i> Utilisateur existant
            </button>
        </div>
    </div>

    {{-- Modale Invitation --}}
    <div class="modal fade" id="inviteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content saas-modal-content saas-modal-primary">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Invitation</p>
                        <h3 class="modal-title">Inviter par e-mail</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <form id="inviteUserForm">
                    @csrf
                    <div class="modal-body">
                        <p style="color: var(--ds-text-secondary); font-size: .82rem; margin-bottom: 16px;">
                            L'accès ne sera créé qu'après acceptation. Le lien sécurisé est valable 48 heures.
                        </p>
                        <div class="saas-form-group">
                            <label>Adresse e-mail</label>
                            <input type="email" name="email" required placeholder="membre@exemple.com">
                        </div>
                        <div class="saas-form-group">
                            <label>Rôle proposé</label>
                            <select name="role_id" id="invitation_role" required>
                                <option value="">Sélectionnez un rôle</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--ds-border-soft);">
                        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Envoi en cours…">
                            <i class="bi bi-send"></i> Envoyer l'invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modale Rattacher existant --}}
    <div class="modal fade" id="attachExistingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content saas-modal-content saas-modal-primary">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Rattachement</p>
                        <h3 class="modal-title">Ajouter un utilisateur existant</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <form id="attachExistingForm">
                    @csrf
                    <div class="modal-body">
                        <p style="color: var(--ds-text-secondary); font-size: .82rem; margin-bottom: 16px;">
                            Utilisez l'e-mail exact du compte. L'utilisateur conservera ses accès dans ses autres compagnies.
                        </p>
                        <div class="saas-form-group">
                            <label>E-mail du compte existant</label>
                            <input type="email" name="email" id="existing_user_email" required placeholder="utilisateur@exemple.com">
                        </div>
                        <div class="saas-form-group">
                            <label>Rôle dans cette compagnie</label>
                            <select name="role_id" id="existing_user_role" required>
                                <option value="">Sélectionnez un rôle</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--ds-border-soft);">
                        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Rattachement…">
                            <i class="bi bi-person-check"></i> Rattacher à cette compagnie
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modale Intégrer dans une autre compagnie --}}
    <div class="modal fade" id="cloneUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content saas-modal-content">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Intégration</p>
                        <h3 class="modal-title">Intégrer dans une compagnie</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <form id="cloneUserForm">
                    @csrf
                    <div class="modal-body">
                        <p style="color: var(--ds-text-secondary); font-size: .82rem; margin-bottom: 16px;">
                            Vous allez donner à <strong id="cloneUserName" style="color: var(--ds-text-primary);"></strong> un accès supplémentaire. Son accès actuel sera conservé.
                        </p>
                        <div class="saas-form-group">
                            <label>Compagnie cible</label>
                            <select id="cloneCompany" name="company_id" required>
                                <option value="">Chargement…</option>
                            </select>
                        </div>
                        <div class="saas-form-group">
                            <label>Rôle dans cette compagnie</label>
                            <select id="cloneRole" name="role_id" required disabled>
                                <option value="">Sélectionnez d'abord une compagnie</option>
                            </select>
                        </div>
                        <div id="cloneNoCompany" class="saas-alert saas-alert-danger" style="display: none;">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Aucune autre compagnie gérable n'est disponible.</span>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--ds-border-soft);">
                        <button type="button" class="saas-btn saas-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" id="cloneSubmit" class="saas-btn saas-btn-primary" disabled data-loading-text="Intégration…">
                            <i class="bi bi-check-lg"></i> Approuver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modale Modifier --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content saas-modal-content saas-modal-warning">
                <div class="modal-header">
                    <div>
                        <p class="saas-modal-eyebrow">Modification</p>
                        <h3 class="modal-title">Modifier l'utilisateur</h3>
                    </div>
                    <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="edit_response"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des utilisateurs --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Membres actifs</h2>
                <p class="saas-card-description">Utilisateurs ayant accès à cette compagnie.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Liste des invitations --}}
    <div class="saas-card">
        <div class="saas-card-head">
            <div>
                <h2>Invitations</h2>
                <p class="saas-card-description">Suivez les invitations, renvoyez un lien ou révoquez un accès en attente.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="invitationsTable" class="table text-nowrap w-100">
                <thead>
                    <tr>
                        <th>E-mail</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Expiration</th>
                        <th>Invité par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $invitation)
                        @php
                            $invitationStatusClass = match (true) {
                                $invitation->isAccepted() => 'is-active',
                                (bool) $invitation->declined_at => 'is-danger',
                                (bool) $invitation->revoked_at => 'is-neutral',
                                $invitation->isExpired() => 'is-expired',
                                default => 'is-pending',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight: 600;">{{ $invitation->email }}</td>
                            <td>{{ $invitation->role?->name ?? 'Rôle supprimé' }}</td>
                            <td>
                                <span class="saas-status-badge {{ $invitationStatusClass }}">
                                    {{ $invitation->status_label }}
                                </span>
                            </td>
                            <td>{{ $invitation->expires_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $invitation->inviter?->name ?? 'Système' }}</td>
                            <td>
                                @if(!$invitation->accepted_at && !$invitation->declined_at && !$invitation->revoked_at)
                                    <div class="saas-action-group">
                                        <button class="saas-action-btn resendInvitation" data-id="{{ $invitation->id }}" data-email="{{ $invitation->email }}" title="Renvoyer">
                                            <i class="bi bi-send"></i>
                                        </button>
                                        <button class="saas-action-btn btn-action-danger revokeInvitation" data-id="{{ $invitation->id }}" data-email="{{ $invitation->email }}" title="Révoquer">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                @else
                                    <span style="color: var(--ds-text-muted);">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('hub/assets/plugins/datatables.net/js/dataTables.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    // --- DataTable ---
    var Datatable = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('user.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'role_name', name: 'active_role.name' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        responsive: true,
        language: {
            lengthMenu: "Afficher _MENU_ entrées",
            zeroRecords: "Aucun membre trouvé",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Affichage de 0 à 0 sur 0 entrée",
            infoFiltered: "(filtré à partir de _MAX_ entrées au total)",
            search: "Rechercher :",
            paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Précédent" }
        },
    });

    window.addEventListener('datatableUpdated', function() {
        Datatable.ajax.reload(null, false);
    });

    // --- Invitations DataTable (client-side) ---
    var invitationsTable = $('#invitationsTable').DataTable({
        processing: false,
        serverSide: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        order: [[3, 'desc']],
        responsive: true,
        language: {
            lengthMenu: "Afficher _MENU_ entrées",
            zeroRecords: "Aucune invitation",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ invitations",
            infoEmpty: "Aucune invitation",
            infoFiltered: "(filtré à partir de _MAX_ invitations)",
            search: "Rechercher :",
            paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Précédent" }
        },
    });

    // --- Select2 pour les selects de rôle (dropdownParent = modal pour z-index) ---
    $('#invitation_role').select2({
        width: '100%',
        placeholder: 'Sélectionnez un rôle',
        allowClear: true,
        dropdownParent: $('#inviteUserModal'),
        language: 'fr'
    });
    $('#existing_user_role').select2({
        width: '100%',
        placeholder: 'Sélectionnez un rôle',
        allowClear: true,
        dropdownParent: $('#attachExistingModal'),
        language: 'fr'
    });
    $('#cloneRole').select2({
        width: '100%',
        placeholder: 'Sélectionnez un rôle',
        allowClear: true,
        dropdownParent: $('#cloneUserModal'),
        language: 'fr'
    });

    // --- Invitation ---
    $('#inviteUserForm').on('submit', function(event) {
        event.preventDefault();
        var form = this;
        var email = $(form).find('[name="email"]').val().trim();
        var role = $('#invitation_role option:selected').text();
        var safeEmail = $('<div>').text(email).html();
        var safeRole = $('<div>').text(role).html();

        Swal.fire({
            icon: 'question',
            title: 'Envoyer cette invitation ?',
            html: 'Adresse : <strong>' + safeEmail + '</strong><br>Rôle : <strong>' + safeRole + '</strong>',
            showCancelButton: true,
            confirmButtonText: 'Oui, envoyer',
            cancelButtonText: 'Vérifier',
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            var button = form.querySelector('[type="submit"]');
            window.ServerButtonLoader.withLoader(button, function() {
                return $.post("{{ route('user.invitations.store') }}", $(form).serialize());
            }, 'Envoi en cours…').then(function(data) {
                Swal.fire({
                    icon: 'success', title: data.title, text: data.msg,
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
                }).then(function() { window.location.reload(); });
            }).catch(function(xhr) {
                var msg = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Une erreur est survenue.';
                if (xhr.status === 422 && msg.toLowerCase().includes('limite')) return showPlanLimitAlert(msg);
                Swal.fire({
                    icon: 'error', title: 'Invitation impossible', text: msg,
                    buttonsStyling: false,
                    customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
                });
            });
        });
    });

    // --- Renvoi invitation ---
    $(document).on('click', '.resendInvitation', function() {
        var id = $(this).data('id');
        var email = $(this).data('email');
        Swal.fire({
            icon: 'question',
            title: 'Renvoyer cette invitation ?',
            text: 'Un nouveau lien sera envoyé à ' + email + ' et l\'ancien sera invalidé.',
            showCancelButton: true,
            confirmButtonText: 'Oui, renvoyer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            allowEscapeKey: function() { return !Swal.isLoading(); },
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
            preConfirm: function() {
                return new Promise(function(resolve) {
                    $.post("{{ url('user/invitations') }}/" + id + '/resend', {_token: "{{ csrf_token() }}"})
                        .done(function(data) { resolve(data); })
                        .fail(function(xhr) {
                            Swal.showValidationMessage(xhr.responseJSON?.message || 'Renvoi impossible.');
                            resolve(false);
                        });
                });
            }
        }).then(function(result) {
            if (!result.isConfirmed || !result.value) return;
            Swal.fire({icon:'success', title:result.value.title, text:result.value.msg,
                buttonsStyling: false,
                customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
            }).then(function(){ window.location.reload(); });
        });
    });

    // --- Révocation invitation ---
    $(document).on('click', '.revokeInvitation', function() {
        var id = $(this).data('id');
        var email = $(this).data('email');
        Swal.fire({
            icon: 'warning',
            title: 'Révoquer cette invitation ?',
            text: 'Le lien envoyé à ' + email + ' deviendra définitivement inutilisable.',
            showCancelButton: true,
            confirmButtonText: 'Oui, révoquer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            allowEscapeKey: function() { return !Swal.isLoading(); },
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
            preConfirm: function() {
                return new Promise(function(resolve) {
                    $.ajax({ url: "{{ url('user/invitations') }}/" + id, type: 'DELETE', data: {_token: "{{ csrf_token() }}"} })
                        .done(function(data) { resolve(data); })
                        .fail(function(xhr) {
                            Swal.showValidationMessage(xhr.responseJSON?.message || 'Révocation impossible.');
                            resolve(false);
                        });
                });
            }
        }).then(function(result) {
            if (!result.isConfirmed || !result.value) return;
            Swal.fire({icon:'success', title:result.value.title, text:result.value.msg,
                buttonsStyling: false,
                customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
            }).then(function(){ window.location.reload(); });
        });
    });

    // --- Rattacher existant ---
    $('#attachExistingForm').submit(function(event) {
        event.preventDefault();
        var form = this;
        var button = form.querySelector('[type="submit"]');
        window.ServerButtonLoader.withLoader(button, function() {
            return $.ajax({ type: 'POST', url: "{{ route('user.attach-existing') }}", data: $(form).serialize(), dataType: 'json' });
        }, 'Rattachement…').then(function(data) {
            if (data.status) {
                $('#attachExistingModal').modal('hide');
                $(form)[0].reset();
                $('#existing_user_role').val(null).trigger('change');
                Datatable.ajax.reload(null, false);
            }
            Swal.fire({ icon: data.status ? 'success' : 'warning', title: data.title, text: data.msg,
                buttonsStyling: false,
                customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
            });
        }).catch(function(xhr) {
            var msg = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Impossible de rattacher cet utilisateur.';
            Swal.fire({ icon: 'error', title: 'Erreur', text: msg,
                buttonsStyling: false,
                customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
            });
        });
    });

    // --- Intégrer dans une autre compagnie ---
    var cloneCompanies = [];
    var clonedUserId = null;

    $(document).on('click', '.cloneUser', function() {
        clonedUserId = $(this).data('id');
        $('#cloneUserName').text($(this).data('name'));
        $('#cloneCompany').html('<option value="">Chargement…</option>');
        $('#cloneRole').html('<option value="">Sélectionnez d\'abord une compagnie</option>').prop('disabled', true);
        $('#cloneSubmit').prop('disabled', true);
        $('#cloneNoCompany').hide();
        $('#cloneUserModal').modal('show');

        $.get("{{ url('user') }}/" + clonedUserId + '/transfer-options')
            .done(function(data) {
                cloneCompanies = data.companies || [];
                var options = '<option value="">Sélectionnez une compagnie</option>';
                cloneCompanies.forEach(function(company) {
                    options += '<option value="' + company.id + '" ' + (company.already_member ? 'disabled' : '') + '>' +
                        $('<div>').text(company.name).html() + (company.already_member ? ' — déjà membre' : '') + '</option>';
                });
                $('#cloneCompany').html(options);
                if (cloneCompanies.length === 0) $('#cloneNoCompany').show();
            })
            .fail(function(xhr) {
                $('#cloneUserModal').modal('hide');
                Swal.fire({icon: 'error', title: 'Erreur', text: xhr.responseJSON?.message || 'Impossible de charger les compagnies.',
                    buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }});
            });
    });

    $('#cloneCompany').on('change', function() {
        var company = cloneCompanies.find(function(item) { return String(item.id) === String(this.value); }.bind(this));
        var options = '<option value="">Sélectionnez un rôle</option>';
        (company?.roles || []).forEach(function(role) {
            options += '<option value="' + role.id + '">' + $('<div>').text(role.name).html() + '</option>';
        });
        $('#cloneRole').html(options).prop('disabled', !company || company.already_member);
        $('#cloneSubmit').prop('disabled', true);
    });

    $('#cloneRole').on('change', function() {
        $('#cloneSubmit').prop('disabled', !this.value);
    });

    $('#cloneUserForm').on('submit', function(event) {
        event.preventDefault();
        var companyName = $('#cloneCompany option:selected').text();
        var roleName = $('#cloneRole option:selected').text();
        Swal.fire({
            icon: 'question',
            title: 'Approuver cette intégration ?',
            html: 'Compagnie : <strong>' + $('<div>').text(companyName).html() + '</strong><br>Rôle : <strong>' + $('<div>').text(roleName).html() + '</strong>',
            showCancelButton: true,
            confirmButtonText: 'Oui, intégrer',
            cancelButtonText: 'Annuler',
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            var button = document.getElementById('cloneSubmit');
            window.ServerButtonLoader.withLoader(button, function() {
                return $.post("{{ url('user') }}/" + clonedUserId + '/transfer-company', $('#cloneUserForm').serialize());
            }, 'Intégration…').then(function(data) {
                if (data.status) $('#cloneUserModal').modal('hide');
                Swal.fire({icon: data.status ? 'success' : 'warning', title: data.title, text: data.msg,
                    buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }});
            }).catch(function(xhr) {
                Swal.fire({icon: 'error', title: 'Erreur', text: xhr.responseJSON?.message || 'Intégration impossible.',
                    buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }});
            });
        });
    });

    // --- Modifier utilisateur ---
    $('body').on('click', '.editModal', function() {
        var id = $(this).data("id");
        $.ajax({
            url: '{{ url("user") }}/' + id + '/edit',
            dataType: 'html',
            success: function(result) {
                $('#edit_response').html(result);
                // Init Select2 après chargement AJAX
                $('#edit_role_id').select2({
                    width: '100%',
                    placeholder: 'Sélectionnez un rôle',
                    allowClear: true,
                    dropdownParent: $('#editModal'),
                    language: 'fr'
                });
            }
        });
        $('#editModal').modal('show');
    });

    // --- Archiver ---
    $('body').on('click', '.archive', function() {
        var id = $(this).data("id");
        Swal.fire({
            icon: "question",
            title: "Désactiver cet utilisateur ?",
            text: "L'utilisateur perdra l'accès à cette compagnie mais conservera ses autres adhésions.",
            confirmButtonText: "Oui, désactiver",
            showCancelButton: true,
            cancelButtonText: "Annuler",
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            allowEscapeKey: function() { return !Swal.isLoading(); },
            preConfirm: function() {
                return new Promise(function(resolve) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: "DELETE", url: 'user/' + id, dataType: 'json'
                    }).done(function(data) { resolve(data); })
                    .fail(function(xhr) {
                        Swal.showValidationMessage(xhr.responseJSON?.message || 'Désactivation impossible.');
                        resolve(false);
                    });
                });
            }
        }).then(function(result) {
            if (!result.isConfirmed || !result.value) return;
            var data = result.value;
            if (data.status) {
                Datatable.ajax.reload(null, false);
            }
            Swal.fire({ icon: data.status ? 'success' : 'error', title: data.title, text: data.msg,
                buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }});
        });
    });

    // --- Restaurer ---
    $('body').on('click', '.restore', function() {
        var id = $(this).data("id");
        Swal.fire({
            icon: "question",
            title: "Restaurer cet utilisateur ?",
            text: "L'utilisateur retrouvera l'accès à cette compagnie avec son rôle précédent.",
            confirmButtonText: "Oui, restaurer",
            showCancelButton: true,
            cancelButtonText: "Annuler",
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            allowEscapeKey: function() { return !Swal.isLoading(); },
            preConfirm: function() {
                return new Promise(function(resolve) {
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: "DELETE", url: 'user/' + id, dataType: 'json'
                    }).done(function(data) { resolve(data); })
                    .fail(function(xhr) {
                        Swal.showValidationMessage(xhr.responseJSON?.message || 'Restauration impossible.');
                        resolve(false);
                    });
                });
            }
        }).then(function(result) {
            if (!result.isConfirmed || !result.value) return;
            var data = result.value;
            if (data.status) {
                Datatable.ajax.reload(null, false);
            }
            Swal.fire({ icon: data.status ? 'success' : 'error', title: data.title, text: data.msg,
                buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }});
        });
    });
    function showPlanLimitAlert(message) {
        var canUpgrade = @json(app(\App\Services\CompanyContext::class)->hasPermission('subscription.manage'));
        return Swal.fire({ icon: 'warning', title: 'Limite du plan atteinte', text: message, showCancelButton: canUpgrade, confirmButtonText: canUpgrade ? 'Améliorer mon plan' : 'OK', cancelButtonText: 'Fermer', buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' } }).then(function(result) { if (canUpgrade && result.isConfirmed) window.location.href = '{{ route('subscriptions.index') }}'; });
    }
    $(document).on('click', '[data-limit-message]', function(event) {
        event.preventDefault();
        showPlanLimitAlert(this.dataset.limitMessage);
    });
});
</script>
@endpush
