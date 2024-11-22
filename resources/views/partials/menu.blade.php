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
							<i class="bi bi-bag-check"></i>
						</div>
						<div class="menu-text d-flex align-items-center">Catégories</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'category.index') active @endif">
							<a href="{{ route('category.index') }}" class="menu-link">
								<div class="menu-text">Ajouter</div>
							</a>
						</div>
					</div>
				</div>

				<div class="menu-item has-sub @if(Request::route()->getName() === 'product.index' OR Request::route()->getName() === 'menu.index') active @endif">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="bi bi-bag-check"></i>
							<!-- <span class="w-5px h-5px rounded-3 bg-theme position-absolute top-0 end-0 mt-3px me-3px"></span> -->
						</div>
						<div class="menu-text d-flex align-items-center">Produits</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'product.index') active @endif">
							<a href="{{ route('product.index') }}" class="menu-link">
								<div class="menu-text">Ajouter produit</div>
							</a>
						</div>
					</div>
					<div class="menu-submenu">
						<div class="menu-item @if(Request::route()->getName() === 'menu.index') active @endif">
							<a href="{{ route('menu.index') }}" class="menu-link">
								<div class="menu-text">Ajouter menu</div>
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
						<i class="bi bi-bag-check"></i>
						<!-- <span class="w-5px h-5px rounded-3 bg-theme position-absolute top-0 end-0 mt-3px me-3px"></span> -->
					</div>
					<div class="menu-text d-flex align-items-center">Point de ventes</div>
					<span class="menu-caret"><b class="caret"></b></span>
				</a>
				<div class="menu-submenu">
					<div class="menu-item">
						<a href="{{ route('sale.index') }}" target="_blank" class="menu-link">
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
			<div class="menu-item @if(Request::route()->getName() === 'profil') active @endif">
				<a href="{{ route('profil') }}" class="menu-link">
					<span class="menu-icon"><i class="bi bi-people"></i></span>
					<span class="menu-text">Profil</span>
				</a>
			</div>
			<div class="menu-item">
				<a href="settings.html" class="menu-link">
					<span class="menu-icon"><i class="bi bi-gear"></i></span>
					<span class="menu-text">Parametres</span>
				</a>
			</div>
		</div>

		<div class="p-3 px-4 mt-auto">
			<a href="documentation/index.html" target="_blank" class="btn d-block btn-outline-theme">
				<!-- <i class="fa fa-code-branch me-2 ms-n2 opacity-5"></i>  -->
				{{config('app.name')}}
			</a>
		</div>
	</div>

</div>


<button class="app-sidebar-mobile-backdrop" data-toggle-target=".app" data-toggle-class="app-sidebar-mobile-toggled"></button>