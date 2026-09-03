@php
    $membership = $currentMembership ?? null;
    $allowed = fn (string $permission) => $membership?->hasPermission($permission) ?? false;
    $salesActive = request()->routeIs('sale.*') || request()->routeIs('history');
    $catalogActive = request()->routeIs('product.*') || request()->routeIs('category.*') || request()->routeIs('menu.*') || request()->routeIs('supplier.*');
    $accountingActive = request()->routeIs('ams.*') || request()->routeIs('cash-account.*') || request()->routeIs('transaction.*');
    $ecommerceActive = request()->routeIs('ecommerce.*');
    $teamActive = request()->routeIs('user.*') || request()->routeIs('roles.*');
    $communicationsActive = request()->routeIs('notifications.*') || request()->routeIs('sms-quota.*') || request()->routeIs('communications.*');
@endphp
<aside class="saas-sidebar" id="saasSidebar" aria-label="Navigation principale">
    <div class="saas-sidebar-head">
        <a class="saas-brand" href="{{ $allowed('dashboard.view') ? route('dashboard') : route('companies.select') }}">
            <span class="saas-brand-mark">{{ strtoupper(substr(config('app.name', 'POS'), 0, 2)) }}</span>
            <span class="saas-brand-copy"><strong>{{ config('app.name') }}</strong><small>Espace professionnel</small></span>
        </a>
        <button type="button" class="saas-icon-button saas-collapse-button" data-saas-sidebar-collapse aria-label="Réduire le menu"><i class="bi bi-layout-sidebar-inset"></i></button>
    </div>

    <nav class="saas-nav">
        @if($allowed('dashboard.view'))
            <div class="saas-nav-section"><span>Vue générale</span></div>
            <a class="saas-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i><span>Tableau de bord</span></a>
        @endif

        @if($allowed('sales.manage') || $allowed('catalog.manage') || $allowed('inventory.manage') || $allowed('clients.manage'))
            <div class="saas-nav-section"><span>Activité</span></div>
        @endif
        @if($allowed('catalog.manage'))
            <details class="saas-nav-group {{ $catalogActive ? 'is-active' : '' }}" @if($catalogActive) open @endif>
                <summary class="{{ $catalogActive ? 'is-active' : '' }}"><span><i class="bi bi-box-seam"></i><span>Catalogue</span></span><i class="bi bi-chevron-down saas-nav-chevron"></i></summary>
                <div><a class="{{ request()->routeIs('category.*') ? 'is-active' : '' }}" href="{{ route('category.index') }}">Catégories</a><a class="{{ request()->routeIs('product.*') ? 'is-active' : '' }}" href="{{ route('product.index') }}">Produits</a><a class="{{ request()->routeIs('menu.*') ? 'is-active' : '' }}" href="{{ route('menu.index') }}">Menus</a><a class="{{ request()->routeIs('supplier.*') ? 'is-active' : '' }}" href="{{ route('supplier.index') }}">Fournisseurs</a></div>
            </details>
        @endif
        @if($allowed('sales.manage'))
            <details class="saas-nav-group {{ $salesActive ? 'is-active' : '' }}" @if($salesActive) open @endif>
                <summary class="{{ $salesActive ? 'is-active' : '' }}"><span><i class="bi bi-cart3"></i><span>Ventes</span></span><i class="bi bi-chevron-down saas-nav-chevron"></i></summary>
                <div><a class="{{ request()->routeIs('sale.index') ? 'is-active' : '' }}" href="{{ route('sale.index') }}">Point de vente</a><a class="{{ request()->routeIs('history') ? 'is-active' : '' }}" href="{{ route('history') }}">Historique</a></div>
            </details>
        @endif
        @if($allowed('inventory.manage'))<a class="saas-nav-link {{ request()->routeIs('inventory.*') ? 'is-active' : '' }}" href="{{ route('inventory.index') }}"><i class="bi bi-boxes"></i><span>Inventaires</span></a>@endif
        @if($allowed('clients.manage'))<a class="saas-nav-link {{ request()->routeIs('client.*') ? 'is-active' : '' }}" href="{{ route('client.index') }}"><i class="bi bi-people"></i><span>Clients</span></a>@endif

        @if($allowed('cash.manage') || $allowed('ecommerce.manage'))
            <div class="saas-nav-section"><span>Gestion</span></div>
        @endif
        @if($allowed('cash.manage'))
            <details class="saas-nav-group {{ $accountingActive ? 'is-active' : '' }}" @if($accountingActive) open @endif>
                <summary class="{{ $accountingActive ? 'is-active' : '' }}"><span><i class="bi bi-wallet2"></i><span>Comptabilité</span></span><i class="bi bi-chevron-down saas-nav-chevron"></i></summary>
                <div><a class="{{ request()->routeIs('ams.dashboard') ? 'is-active' : '' }}" href="{{ route('ams.dashboard') }}">Vue comptable</a><a class="{{ request()->routeIs('cash-account.*') ? 'is-active' : '' }}" href="{{ route('cash-account.index') }}">Caisses</a><a class="{{ request()->routeIs('transaction.*') ? 'is-active' : '' }}" href="{{ route('transaction.index') }}">Opérations</a></div>
            </details>
        @endif
        @if($allowed('ecommerce.manage'))
            <details class="saas-nav-group {{ $ecommerceActive ? 'is-active' : '' }}" @if($ecommerceActive) open @endif>
                <summary class="{{ $ecommerceActive ? 'is-active' : '' }}"><span><i class="bi bi-shop"></i><span>E-commerce</span></span><i class="bi bi-chevron-down saas-nav-chevron"></i></summary>
                <div><a class="{{ request()->routeIs('ecommerce.orders.*') ? 'is-active' : '' }}" href="{{ route('ecommerce.orders.index') }}">Commandes</a><a class="{{ request()->routeIs('ecommerce.settings') ? 'is-active' : '' }}" href="{{ route('ecommerce.settings') }}">Configuration</a></div>
            </details>
        @endif

        @if($allowed('members.manage') || $allowed('notifications.manage') || $allowed('quota.manage') || $allowed('communications.view') || $allowed('company.manage'))
            <div class="saas-nav-section"><span>Administration</span></div>
        @endif
        @if($allowed('members.manage'))
            <details class="saas-nav-group {{ $teamActive ? 'is-active' : '' }}" @if($teamActive) open @endif>
                <summary class="{{ $teamActive ? 'is-active' : '' }}"><span><i class="bi bi-person-gear"></i><span>Équipe</span></span><i class="bi bi-chevron-down saas-nav-chevron"></i></summary>
                <div><a class="{{ request()->routeIs('user.*') ? 'is-active' : '' }}" href="{{ route('user.index') }}">Utilisateurs</a><a class="{{ request()->routeIs('roles.*') ? 'is-active' : '' }}" href="{{ route('roles.index') }}">Rôles et permissions</a></div>
            </details>
        @endif
        @if($allowed('notifications.manage') || $allowed('quota.manage') || $allowed('communications.view'))
            <details class="saas-nav-group {{ $communicationsActive ? 'is-active' : '' }}" @if($communicationsActive) open @endif>
                <summary class="{{ $communicationsActive ? 'is-active' : '' }}"><span><i class="bi bi-chat-square-dots"></i><span>Communications</span></span><i class="bi bi-chevron-down saas-nav-chevron"></i></summary>
                <div>@if($allowed('notifications.manage'))<a class="{{ request()->routeIs('notifications.*') ? 'is-active' : '' }}" href="{{ route('notifications.index') }}">Configuration</a>@endif @if($allowed('quota.manage'))<a class="{{ request()->routeIs('sms-quota.*') ? 'is-active' : '' }}" href="{{ route('sms-quota.index') }}">Quota</a>@endif @if($allowed('communications.view'))<a class="{{ request()->routeIs('communications.*') ? 'is-active' : '' }}" href="{{ route('communications.index') }}">Consommation</a>@endif</div>
            </details>
        @endif
        @if($allowed('company.manage'))<a class="saas-nav-link {{ request()->routeIs('company.*') ? 'is-active' : '' }}" href="{{ route('company.index') }}"><i class="bi bi-gear"></i><span>Paramètres</span></a>@endif
        @if(in_array($membership?->role?->key, ['owner', 'admin'], true) && $allowed('subscription.manage'))<a class="saas-nav-link {{ request()->routeIs('subscriptions.*') ? 'is-active' : '' }}" href="{{ route('subscriptions.index') }}"><i class="bi bi-credit-card"></i><span>Abonnement</span></a>@endif
    </nav>

    <div class="saas-sidebar-foot">
        <a class="saas-user-compact" href="{{ route('profil') }}"><span class="saas-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>Voir mon profil</small></span></a>
    </div>
</aside>
