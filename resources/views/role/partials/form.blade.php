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
    $moduleIcons = [
        'dashboard' => 'bi-speedometer2',
        'catalog' => 'bi-box-seam',
        'inventory' => 'bi-clipboard-data',
        'sales' => 'bi-cart-check',
        'clients' => 'bi-people',
        'cash' => 'bi-wallet2',
        'ecommerce' => 'bi-shop',
        'members' => 'bi-person-gear',
        'company' => 'bi-building',
        'notifications' => 'bi-bell',
        'quota' => 'bi-phone',
        'reports' => 'bi-graph-up-arrow',
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
        'notifications.manage' => 'Configurer les canaux et les destinataires des notifications de ventes et d\'inventaire.',
        'quota.manage' => 'Consulter les quotas et acheter des crédits SMS ou WhatsApp pour la compagnie.',
        'reports.view_margin' => 'Consulter les marges, bénéfices et informations financières sensibles.',
    ];
@endphp

<div class="saas-form-group">
    <label>Nom du rôle</label>
    <input name="name" required maxlength="100" value="{{ old('name', $role?->name) }}" placeholder="Ex. Responsable de stock">
</div>

<p style="color: var(--ds-text-muted); font-size: .78rem; margin-bottom: 16px;">
    Activez une fonctionnalité depuis son en-tête. Ouvrez-la pour voir précisément les accès accordés.
</p>

<div class="accordion role-permissions" id="accordion-{{ $formKey }}">
    @foreach($permissions as $module => $modulePermissions)
        @php
            $moduleKey = $formKey.'-'.\Illuminate\Support\Str::slug($module);
            $modulePermissionIds = $modulePermissions->pluck('id')->all();
            $enabledCount = count(array_intersect($modulePermissionIds, $selectedPermissions));
            $moduleEnabled = $enabledCount === count($modulePermissionIds);
        @endphp
        <div class="accordion-item saas-accordion" data-permission-module style="margin-bottom: 8px;">
            <div style="display: flex; align-items: stretch;">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse-{{ $moduleKey }}" aria-expanded="false"
                    style="flex: 1; border: none; background: var(--ds-glass-1); color: var(--ds-text-secondary); border-radius: 11px; padding: 12px 16px; font-size: .82rem; font-weight: 600;">
                    <i class="bi {{ $moduleIcons[$module] ?? 'bi-grid' }}" style="color: var(--ds-accent); margin-right: 10px;"></i>
                    {{ $moduleLabels[$module] ?? ucfirst($module) }}
                    <span style="margin-left: auto; font-size: .7rem; color: var(--ds-text-muted);">{{ $enabledCount }}/{{ count($modulePermissionIds) }}</span>
                </button>
                <div style="display: flex; align-items: center; padding: 0 14px; background: var(--ds-glass-1); border-radius: 0 11px 11px 0; border-left: 1px solid var(--ds-border-soft);">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; font-size: .74rem; font-weight: 700; color: var(--ds-text-secondary); white-space: nowrap;">
                        <input class="module-permission-toggle" type="checkbox" id="module-{{ $moduleKey }}" @checked($moduleEnabled)
                            style="width: 16px; height: 16px; accent-color: var(--ds-accent);">
                        Tout
                    </label>
                </div>
            </div>
            <div id="collapse-{{ $moduleKey }}" class="accordion-collapse collapse"
                aria-labelledby="heading-{{ $moduleKey }}" data-bs-parent="#accordion-{{ $formKey }}">
                <div style="padding: 12px 0;">
                    @foreach($modulePermissions as $permission)
                        <div style="padding: 12px 14px; margin-bottom: 6px; background: var(--ds-bg-elevated); border: 1px solid var(--ds-border-soft); border-radius: 11px;">
                            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0;">
                                <input class="permission-item" type="checkbox" name="permissions[]"
                                    value="{{ $permission->id }}" id="permission-{{ $formKey }}-{{ $permission->id }}"
                                    @checked(in_array($permission->id, $selectedPermissions))
                                    style="width: 16px; height: 16px; margin-top: 2px; accent-color: var(--ds-accent);">
                                <div>
                                    <span style="font-size: .8rem; font-weight: 600; color: var(--ds-text-primary);">
                                        {{ $permission->description ?: $permission->key }}
                                    </span>
                                    <p style="margin: 3px 0 0; font-size: .72rem; color: var(--ds-text-muted); line-height: 1.4;">
                                        {{ $permissionDetails[$permission->key] ?? 'Autorise l\'accès aux opérations associées à cette fonctionnalité.' }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
