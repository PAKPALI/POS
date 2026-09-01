@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Configuration E-commerce</h1>
            <p>Paramétrez la boutique, le lien public, les managers et l'activation.</p>
        </div>
    </div>

    @if(!$company)
        <div class="saas-card" style="background: rgba(255, 98, 110, .08); border-color: rgba(255, 98, 110, .25);">
            <p style="margin: 0; color: var(--ds-danger, #FF626E); font-weight: 700; font-size: .88rem;">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                Aucune compagnie configurée. Veuillez d'abord configurer la compagnie dans
                <a href="{{ route('company.index') }}">Paramètres &gt; Compagnie</a>.
            </p>
        </div>
    @else
    <div class="row g-4">
        {{-- Paramètres boutique --}}
        <div class="col-lg-7">
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>Informations boutique</h2>
                        <p class="saas-card-description">Nom, slug, logo et description de la boutique publique.</p>
                    </div>
                </div>

                <form id="settingsForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 saas-form-group">
                            <label>Nom du site</label>
                            <input type="text" value="{{ $company->name }}" readonly style="opacity:.7;">
                            <small style="color: var(--ds-text-muted); font-size: .75rem;">Utilise le nom de la compagnie</small>
                        </div>
                        <div class="col-md-12 saas-form-group">
                            <label>Adresse personnalisée de la boutique</label>
                            <div style="display:flex; gap:0; border: 1px solid var(--ds-border-soft); border-radius: var(--ds-radius-control); overflow: hidden;">
                                <span style="min-width:0; flex: 0 0 auto; padding: 10px 12px; background: var(--ds-bg-elevated); color: var(--ds-text-muted); font-size: .78rem; border-right: 1px solid var(--ds-border-soft); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 52%;" title="{{ url('/boutique') }}/">{{ url('/boutique') }}/</span>
                                <input type="text" name="slug" id="boutique_slug" value="{{ $company->slug }}" data-original-slug="{{ $company->slug }}" minlength="3" maxlength="80" autocomplete="off" required style="border:0; border-radius:0; flex:1; min-width:0;">
                            </div>
                            <div id="slugStatus" style="min-height: 20px; font-size: .78rem; margin-top: 4px;" aria-live="polite"></div>
                            <small style="color: var(--ds-warning, #F5B942); font-size: .75rem;">Si vous changez cette adresse, les anciens liens déjà partagés ne fonctionneront plus.</small>
                        </div>
                        <div class="col-md-6 saas-form-group">
                            <label>Logo</label>
                            <input type="file" name="logo" accept="image/*">
                            @if($company->logo)
                                <div class="mt-2">
                                    <img src="{{ asset($company->logo) }}" alt="Logo" style="max-height: 80px; border-radius: 8px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12 saas-form-group">
                            <label>Description</label>
                            <textarea name="description" rows="4" placeholder="Décrivez ce que l'entreprise propose…">{{ $company->description }}</textarea>
                        </div>
                        <div class="col-md-12 saas-form-group">
                            <label>Boutique en ligne</label>
                            <div class="form-check form-switch" style="margin-top: 4px;">
                                <input class="form-check-input" type="checkbox" name="ecommerce_active" id="ecommerce_active" value="1" {{ $company->ecommerce_active ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Lien d'accès --}}
                    @php $storefrontUrl = route('storefront.home', $company); @endphp
                    <div style="background: rgba(32, 191, 169, .06); border: 1px solid rgba(32, 191, 169, .2); border-radius: 14px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px;">
                            <div style="font-weight: 700; font-size: .88rem; color: var(--ds-text-primary);">
                                <i class="bi bi-globe2 me-1"></i> Lien d'accès à la boutique
                            </div>
                            <span class="saas-status-badge {{ $company->ecommerce_active ? 'is-active' : 'is-inactive' }}">{{ $company->ecommerce_active ? 'En ligne' : 'Hors ligne' }}</span>
                        </div>
                        <p style="margin: 0 0 10px; color: var(--ds-text-muted); font-size: .78rem;">
                            {{ $company->ecommerce_active ? 'La boutique est actuellement accessible au public.' : 'Activez la boutique pour rendre ce lien accessible.' }}
                        </p>
                        <a id="storefrontUrl" href="{{ $storefrontUrl }}" target="_blank" rel="noopener" style="color: var(--ds-success, #35C98B); word-break: break-all; font-size: .82rem; font-weight: 600;">{{ $storefrontUrl }}</a>
                        <div style="display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap;">
                            <a href="{{ $storefrontUrl }}" target="_blank" rel="noopener" class="saas-btn saas-btn-outline saas-btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> Ouvrir la boutique</a>
                            <button type="button" id="copyStorefrontUrl" class="saas-btn saas-btn-ghost saas-btn-sm" data-url="{{ $storefrontUrl }}"><i class="bi bi-copy me-1"></i> Copier le lien</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end" style="border-top: 1px solid var(--ds-border-soft); padding-top: 16px;">
                        <button type="submit" class="saas-btn saas-btn-primary" data-loading-text="Enregistrement…">
                            <i class="bi bi-check-lg" aria-hidden="true"></i><span>Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Managers --}}
        <div class="col-lg-5">
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>Managers de la boutique</h2>
                        <p class="saas-card-description">Les managers reçoivent les notifications des nouvelles commandes.</p>
                    </div>
                </div>

                <form id="addManagerForm" style="margin-bottom: 16px;">
                    @csrf
                    <div style="display: flex; gap: 8px;">
                        <select name="user_id" id="managerUserSelect" class="form-select" required style="flex: 1;">
                            <option value="">Rechercher un utilisateur…</option>
                        </select>
                        <button type="submit" class="saas-btn saas-btn-primary saas-btn-sm" data-loading-text="Ajout…">Ajouter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="managers-table" class="table text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="{{ asset('hub/assets/plugins/datatables.net/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(function() {
        let slugCheckTimer = null;
        let slugCheckRequest = null;
        let slugAvailable = true;

        function updateStorefrontPreview(url) {
            if (!url) return;
            $('#storefrontUrl').attr('href', url).text(url);
            $('#copyStorefrontUrl').data('url', url).attr('data-url', url);
        }

        $('#boutique_slug').on('input', function() {
            const input = this;
            const value = input.value.trim();
            window.clearTimeout(slugCheckTimer);
            if (slugCheckRequest) slugCheckRequest.abort();

            if (!value) {
                slugAvailable = false;
                $('#slugStatus').css('color', 'var(--ds-danger, #FF626E)').text('Saisissez une adresse pour votre boutique.');
                return;
            }

            $('#slugStatus').css('color', 'var(--ds-accent)').html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Vérification…');

            slugCheckTimer = window.setTimeout(function() {
                slugCheckRequest = $.get("{{ route('ecommerce.slug.check') }}", {slug: value})
                    .done(function(data) {
                        slugAvailable = data.available;
                        $(input).data('normalized-slug', data.slug);
                        $('#slugStatus')
                            .css('color', data.available ? 'var(--ds-success, #35C98B)' : 'var(--ds-danger, #FF626E)')
                            .text(data.msg + (data.slug && data.slug !== value ? ' Adresse proposée : ' + data.slug : ''));
                        if (data.available) updateStorefrontPreview(data.storefront_url);
                    })
                    .fail(function(xhr, status) {
                        if (status === 'abort') return;
                        slugAvailable = false;
                        $('#slugStatus').css('color', 'var(--ds-danger, #FF626E)').text(xhr.responseJSON?.msg || 'La disponibilité ne peut pas être vérifiée.');
                    });
            }, 450);
        }).on('blur', function() {
            const normalized = $(this).data('normalized-slug');
            if (slugAvailable && normalized) $(this).val(normalized);
        });

        $('#copyStorefrontUrl').on('click', function() {
            navigator.clipboard.writeText($(this).data('url')).then(function() {
                Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Lien copié', showConfirmButton: false, timer: 1800 });
            });
        });

        $('#managerUserSelect').select2({
            width: '100%',
            placeholder: 'Rechercher un utilisateur…',
            allowClear: true,
            ajax: {
                url: "{{ route('ecommerce.users.search') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term || '',
                    page: params.page || 1
                }),
                processResults: data => data,
                cache: true
            }
        });

        @if($company)
        var managersTable = $('#managers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ecommerce.managers.list') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'user_name', name: 'user_name'},
                {data: 'user_email', name: 'user_email'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            responsive: true,
            language: {
                "lengthMenu": "Afficher _MENU_ entrées",
                "zeroRecords": "Aucun manager",
                "emptyTable": "Aucun manager configuré",
                "processing": "Chargement…",
                "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                "search": "Rechercher :",
                "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
            },
        });

        function settingsRequest(form, confirmedSlugChange) {
            const formData = new FormData(form);
            formData.set('confirm_slug_change', confirmedSlugChange ? '1' : '0');
            return $.ajax({ type: 'POST', url: "{{ route('ecommerce.settings.update') }}", data: formData, contentType: false, processData: false });
        }

        function settingsSaved(data) {
            $('#boutique_slug').val(data.slug).data('original-slug', data.slug).data('normalized-slug', data.slug);
            slugAvailable = true;
            updateStorefrontPreview(data.storefront_url);
            Swal.fire({ toast: true, position: 'top', icon: 'success', title: data.title, showConfirmButton: false, timer: 3000, text: data.msg });
        }

        $('#settingsForm').submit(function(e) {
            e.preventDefault();
            const form = this;
            const button = $(form).find('[type="submit"]');
            const normalizedSlug = $('#boutique_slug').data('normalized-slug') || $('#boutique_slug').val().trim();
            const originalSlug = $('#boutique_slug').data('original-slug');
            const slugChanged = normalizedSlug !== originalSlug;

            if (!slugAvailable) {
                Swal.fire({ icon: 'error', title: 'Adresse indisponible', text: 'Choisissez une adresse disponible avant d\'enregistrer.' });
                return;
            }

            if (slugChanged) {
                Swal.fire({
                    icon: 'warning', title: 'Changer le lien de la boutique ?',
                    text: "L'ancien lien ne fonctionnera plus. Le nouveau sera /boutique/" + normalizedSlug + ".",
                    showCancelButton: true, confirmButtonText: 'Oui, changer', cancelButtonText: 'Annuler',
                    confirmButtonColor: '#d97706', showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
                    preConfirm: function() {
                        if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Enregistrement…');
                        return settingsRequest(form, true).catch(function(xhr) {
                            Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible de modifier le lien.');
                            return false;
                        }).finally(function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]); });
                    }
                }).then(function(result) {
                    if (result.isConfirmed && result.value) settingsSaved(result.value);
                });
                return;
            }

            if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Enregistrement…');
            settingsRequest(form, false)
                .done(settingsSaved)
                .fail(function(xhr) {
                    Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Erreur', text: xhr.responseJSON?.msg || 'Impossible de communiquer avec le serveur.', showConfirmButton: false, timer: 3000 });
                })
                .always(function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]); });
        });

        $('#addManagerForm').submit(function(e) {
            e.preventDefault();
            var form = this;
            var button = $(form).find('[type="submit"]');
            if (window.ServerButtonLoader) window.ServerButtonLoader.start(button[0], 'Ajout…');
            $.ajax({
                type: 'POST', url: "{{ route('ecommerce.managers.add') }}", data: $(form).serialize(),
                success: function(data) {
                    Swal.fire({ toast: true, position: 'top', icon: data.status ? 'success' : 'error', title: data.title, showConfirmButton: false, timer: 3000, text: data.msg });
                    if (data.status) { managersTable.ajax.reload(); form.reset(); }
                },
                error: function(xhr) {
                    const data = xhr.responseJSON || {};
                    Swal.fire({ toast: true, position: 'top', icon: 'error', title: data.title || 'Ajout impossible', showConfirmButton: false, timer: 3500, text: data.msg || 'Impossible de communiquer avec le serveur.' });
                },
                complete: function() { if (window.ServerButtonLoader) window.ServerButtonLoader.stop(button[0]); }
            });
        });

        $(document).on('click', '.remove-manager', function() {
            var id = $(this).data('id');
            Swal.fire({
                icon: 'question', title: 'Retirer ce manager ?',
                showCancelButton: true, confirmButtonText: 'Oui', cancelButtonText: 'Non',
                buttonsStyling: false, customClass: { popup: 'saas-swal saas-swal-danger', confirmButton: 'saas-btn saas-btn-danger', cancelButton: 'saas-btn saas-btn-ghost' },
                showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
                preConfirm: function() {
                    return $.ajax({ type: 'DELETE', url: "{{ url('ecommerce/managers') }}/" + id, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })
                        .then(function(data) { if (!data.status) { Swal.showValidationMessage(data.msg || 'Le retrait a échoué.'); return false; } return data; })
                        .catch(function(xhr) { Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible de retirer ce manager.'); return false; });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: result.value.title, text: result.value.msg, showConfirmButton: false, timer: 3000 });
                    managersTable.ajax.reload(null, false);
                }
            });
        });
        @endif
    });
    </script>
    @endpush
@endsection
