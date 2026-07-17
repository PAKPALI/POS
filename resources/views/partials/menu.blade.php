<div id="sidebar" class="app-sidebar">
	<div class="app-sidebar-content" data-scrollbar="true" data-height="100%">

		<div class="menu">
			<div class="menu-header">Accueil</div>
			<div class="menu-item @if(Request::route()->getName() === 'dashboard') active @endif">
				<a href="{{ route('dashboard') }}"class="menu-link">
					<span class="menu-icon"><i class="bi bi-cpu"></i></span>
					<span class="menu-text">Tableau de bord</span>
				</a>
			</div>

			<!-- COMPOSANTS -->
			@if(auth()->user()->user_type!=3)
				<div class="menu-header">COMPOSANTS</div>
				<div class="menu-item has-sub @if(Request::route()->getName() === 'category.index') active @endif">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="fas fa-tags"></i>
						</div>
						<div class="menu-text d-flex align-items-center">Catégories</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'category.index') active @endif">
							<a href="{{ route('category.index') }}" class="menu-link">
								<div class="menu-text">Liste Catégories</div>
							</a>
						</div>
					</div>
				</div>

				<div class="menu-item has-sub @if(Request::route()->getName() === 'product.index' OR Request::route()->getName() === 'menu.index') active @endif">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="fas fa-cube"></i>
							<!-- <span class="w-5px h-5px rounded-3 bg-theme position-absolute top-0 end-0 mt-3px me-3px"></span> -->
						</div>
						<div class="menu-text d-flex align-items-center">Produits</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'product.index') active @endif">
							<a href="{{ route('product.index') }}" class="menu-link">
								<div class="menu-text">Liste Produits</div>
							</a>
						</div>
					</div>
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'menu.index') active @endif">
							<a href="{{ route('menu.index') }}" class="menu-link">
								<div class="menu-text">Liste menu</div>
							</a>
						</div>
					</div>

					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'inventory.index') active @endif">
							<a href="{{ route('inventory.index') }}" class="menu-link">
								<div class="menu-text">Inventaires</div>
							</a>
						</div>
					</div>
				</div>
			@endif

			<!-- POS -->
			<div class="menu-header">POS</div>
			<div class="menu-item has-sub @if(Request::route()->getName() === 'history') active @endif">
				<a href="javascript:;" class="menu-link">
					<div class="menu-icon">
						<i class="fas fa-store"></i>
						<!-- <span class="w-5px h-5px rounded-3 bg-theme position-absolute top-0 end-0 mt-3px me-3px"></span> -->
					</div>
					<div class="menu-text d-flex align-items-center">Point de ventes</div>
					<span class="menu-caret"><b class="caret"></b></span>
				</a>
				<div class="menu-submenu">
					<div class="menu-item">
						<a href="{{ route('sale.index') }}" class="menu-link">
							<div class="menu-text">Poste de vente</div>
						</a>
					</div>

					<div class="menu-item @if(Request::route()->getName() === 'history') active @endif">
						<a href="{{ route('history') }}" class="menu-link">
							<div class="menu-text">Historique de ventes</div>
						</a>
					</div>
					<!-- <div class="menu-item">
						<a href="pos_kitchen_order.html" target="_blank" class="menu-link">
							<div class="menu-text">Kitchen Order</div>
						</a>
					</div> -->
				</div>
			</div>

			<!-- PROMO CODE -->
			@if(auth()->user()->user_type!=3)
				<div class="menu-header">CODE PROMO</div>
				<div class="menu-item @if(Request::route()->getName() === 'code.index') active @endif">
					<a href="{{ route('code.index') }}" class="menu-link">
						<span class="menu-icon"><i class="fas fa-barcode"></i></span>
						<span class="menu-text">Code promo</span>
					</a>
				</div>
			@endif

			<!-- AMS -->
			@if(auth()->user()->user_type!=3)
				<div class="menu-header">COMPTABILITE</div>
				<div class="menu-item has-sub 
					@if(Request::route()->getName() === 'cash-account.index' || Request::route()->getName() === 'transaction.index' 
					|| Request::route()->getName() === 'ams.settings' || Request::route()->getName() === 'ams.dashboard')
						active 
					@endif">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="fas fa-balance-scale"></i>
						</div>
						<div class="menu-text d-flex align-items-center">Comptabilité</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<!--dashboard-->
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'ams.dashboard') active @endif">
							<a href="{{ route('ams.dashboard') }}" class="menu-link">
								<span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
								<span class="menu-text">Tableau de bord</span>
							</a>
						</div>
					</div>
					<!--cash-->
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'cash-account.index') active @endif">
							<a href="{{ route('cash-account.index') }}" class="menu-link">
								<span class="menu-icon"><i class="fas fa-wallet"></i></span>
								<span class="menu-text">Caisse</span>
							</a>
						</div>
					</div>
					<!--operations-->
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'transaction.index') active @endif">
							<a href="{{ route('transaction.index') }}" class="menu-link">
								<span class="menu-icon"><i class="fas fa-exchange-alt"></i></span>
								<span class="menu-text">Opérations</span>
							</a>
						</div>
					</div>
					<!--setting-->
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'ams.settings') active @endif">
							<a href="{{ route('ams.settings') }}" class="menu-link">
								<span class="menu-icon"><i class="fas fa-tools"></i></span>
								<span class="menu-text">Paramètres</span>
							</a>
						</div>
					</div>
				</div>
			@endif

			<!-- ECOMMERCE -->
			@if(auth()->user()->user_type!=3)
			<div class="menu-header">ECOMMERCE</div>
			<div class="menu-item has-sub
				@if(Request::route()->getName() === 'ecommerce.settings' || Request::route()->getName() === 'ecommerce.orders.index' || Request::route()->getName() === 'ecommerce.orders.show')
					active
				@endif">
				<a href="javascript:;" class="menu-link">
					<div class="menu-icon">
						<i class="fas fa-shopping-cart"></i>
					</div>
					<div class="menu-text d-flex align-items-center">Ecommerce</div>
					<span class="menu-caret"><b class="caret"></b></span>
				</a>
				<div class="menu-submenu">
					<div class="menu-item @if(Request::route()->getName() === 'ecommerce.settings') active @endif">
						<a href="{{ route('ecommerce.settings') }}" class="menu-link">
							<span class="menu-icon"><i class="fas fa-cog"></i></span>
							<span class="menu-text">Configuration</span>
						</a>
					</div>
				</div>
				<div class="menu-submenu">
					<div class="menu-item @if(Request::route()->getName() === 'ecommerce.orders.index' || Request::route()->getName() === 'ecommerce.orders.show') active @endif">
						<a href="{{ route('ecommerce.orders.index') }}" class="menu-link">
							<span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
							<span class="menu-text">Commandes</span>
						</a>
					</div>
				</div>
			</div>
			@endif

			<!-- UTILISATEURS -->
			<div class="menu-divider"> </div>
			@if(auth()->user()->user_type!=3)
				<div class="menu-header">UTILISATEURS</div>
				<div class="menu-item @if(Request::route()->getName() === 'user.index') active @endif">
					<a href="{{ route('user.index') }}" class="menu-link">
						<span class="menu-icon"><i class="bi bi-people"></i></span>
						<span class="menu-text">Utilisateurs</span>
					</a>
				</div>
			@endif
			@if(auth()->user()->user_type == 1)
				<div class="menu-header">SUPER UTILISATEUR</div>
				<div class="menu-item @if(Request::route()->getName() === 'sms-quota.index') active @endif">
					<a href="{{ route('sms-quota.index') }}" class="menu-link">
						<span class="menu-icon"><i class="bi bi-chat-left-text"></i></span>
						<span class="menu-text">Quota SMS</span>
					</a>
				</div>
			@endif
			<div class="menu-item @if(Request::route()->getName() === 'profil') active @endif">
				<a href="{{ route('profil') }}" class="menu-link">
					<span class="menu-icon"><i class="fas fa-id-badge"></i></span>
					<span class="menu-text">Profil</span>
				</a>
			</div>
			@if(auth()->user()->user_type!=3)
			<div class="menu-header">PARAMETRES</div>
			<div class="menu-item has-sub @if(Request::route()->getName() === 'company.index') active @endif">
				<a href="javascript:;" class="menu-link">
					<div class="menu-icon">
						<i class="bi bi-gear"></i>
					</div>
					<div class="menu-text d-flex align-items-center">Parametres</div>
					<span class="menu-caret"><b class="caret"></b></span>
				</a>
				<div class="menu-submenu">
					<div class="menu-item @if(Request::route()->getName() === 'company.index') active @endif">
						<a href="{{ route('company.index') }}" class="menu-link">
							<div class="menu-text">Compagnie</div>
						</a>
					</div>
				</div>
			</div>
			@endif
		</div>

		<div class="p-3 px-4 mt-auto">
			<a href="documentation/index.html" target="_blank" class="btn d-block btn-outline-theme">
				{{-- <!-- <i class="fa fa-code-branch me-2 ms-n2 opacity-5"></i>  --> --}}
				{{config('app.name')}}
			</a>
		</div>
	</div>

</div>


<button class="app-sidebar-mobile-backdrop" data-toggle-target=".app" data-toggle-class="app-sidebar-mobile-toggled"></button>
