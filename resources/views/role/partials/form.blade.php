@php
    $formKey = 'role-'.($role?->id ?? 'new');
    $moduleLabels = [
        'dashboard' => 'Tableau de bord',
        'catalog' => 'Catalogue et produits',
        'inventory' => 'Gestion des stocks',
        'sales' => 'Ventes et point de vente',
        'clients' => 'Gestion des clients',
        'cash' => 'Caisses et comptabilité',
        'ecommerce' => 'Commerce en ligne',
        'members' => 'Utilisateurs, rôles et permissions',
        'company' => 'Paramètres de la compagnie',
        'notifications' => 'Gestion des notifications',
        'quota' => 'Quotas de communication',
        'reports' => 'Rapports et bénéfices',
    ];
    $permissionDetails = [
        'dashboard.view' => 'Consulter le tableau de bord et les indicateurs généraux de la compagnie.',
        'catalog.manage' => 'Créer, modifier et organiser les produits, menus, catégories et fournisseurs.',
        'inventory.manage' => 'Enregistrer les entrées et sorties de stock et consulter les mouvements.',
        'sales.manage' => 'Effectuer des ventes, consulter les reçus et accéder aux opérations du point de vente.',
        'clients.manage' => 'Créer, modifier et consulter les fiches clients de la compagnie.',
        'cash.manage' => 'Configurer les caisses, consulter leurs soldes et enregistrer des transactions.',
        'ecommerce.manage' => 'Configurer la boutique en ligne et gérer les commandes reçues.',
        'members.manage' => 'Ajouter des utilisateurs, leur attribuer un rôle et configurer les permissions.',
        'company.manage' => 'Modifier les informations et les réglages généraux de la compagnie.',
        'notifications.manage' => 'Configurer les canaux et les destinataires des notifications de ventes et d’inventaire.',
        'quota.manage' => 'Consulter les quotas et acheter des crédits SMS ou WhatsApp pour la compagnie.',
        'reports.view_margin' => 'Consulter les marges, bénéfices et informations financières sensibles.',
    ];
@endphp

<div class="mb-4">
    <label class="form-label">Nom du rôle</label>
    <input name="name" class="form-control" required maxlength="100" value="{{ old('name', $role?->name) }}" placeholder="Ex. Responsable de stock">
</div>

<p class="text-muted mb-3">Activez une fonctionnalité depuis son en-tête. Ouvrez-la pour voir précisément les accès accordés.</p>

<div class="accordion role-permissions" id="accordion-{{ $formKey }}">
    @foreach($permissions as $module => $modulePermissions)
        @php
            $moduleKey = $formKey.'-'.\Illuminate\Support\Str::slug($module);
            $modulePermissionIds = $modulePermissions->pluck('id')->all();
            $enabledCount = count(array_intersect($modulePermissionIds, $selectedPermissions));
            $moduleEnabled = $enabledCount === count($modulePermissionIds);
        @endphp
        <div class="accordion-item" data-permission-module>
            <h2 class="accordion-header d-flex align-items-stretch" id="heading-{{ $moduleKey }}">
                <button class="accordion-button collapsed flex-grow-1" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse-{{ $moduleKey }}" aria-expanded="false">
                    <i class="bi bi-grid me-2"></i>{{ $moduleLabels[$module] ?? ucfirst($module) }}
                </button>
                <div class="d-flex align-items-center px-3 border-start">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input module-permission-toggle" type="checkbox"
                            id="module-{{ $moduleKey }}" @checked($moduleEnabled)>
                        <label class="form-check-label fw-bold text-nowrap" for="module-{{ $moduleKey }}">Activer</label>
                    </div>
                </div>
            </h2>
            <div id="collapse-{{ $moduleKey }}" class="accordion-collapse collapse"
                aria-labelledby="heading-{{ $moduleKey }}" data-bs-parent="#accordion-{{ $formKey }}">
                <div class="accordion-body">
                    @foreach($modulePermissions as $permission)
                        <div class="border rounded p-3 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input permission-item" type="checkbox" name="permissions[]"
                                    value="{{ $permission->id }}" id="permission-{{ $formKey }}-{{ $permission->id }}"
                                    @checked(in_array($permission->id, $selectedPermissions))>
                                <label class="form-check-label fw-bold" for="permission-{{ $formKey }}-{{ $permission->id }}">
                                    {{ $permission->description ?: ($moduleLabels[$module] ?? ucfirst($module)) }}
                                </label>
                            </div>
                            <p class="text-muted small mb-0 mt-2">
                                {{ $permissionDetails[$permission->key] ?? 'Autorise l’accès aux opérations associées à cette fonctionnalité.' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
