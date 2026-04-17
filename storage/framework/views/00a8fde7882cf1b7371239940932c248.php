<div id="sidebar" class="app-sidebar">
	<div class="app-sidebar-content" data-scrollbar="true" data-height="100%">

		<div class="menu">
			<div class="menu-header">Accueil</div>
			<div class="menu-item <?php if(Request::route()->getName() === 'dashboard'): ?> active <?php endif; ?>">
				<a href="<?php echo e(route('dashboard')); ?>"class="menu-link">
					<span class="menu-icon"><i class="bi bi-cpu"></i></span>
					<span class="menu-text">Tableau de bord</span>
				</a>
			</div>

			<!-- COMPOSANTS -->
			<?php if(auth()->user()->user_type!=3): ?>
				<div class="menu-header">COMPOSANTS</div>
				<div class="menu-item has-sub <?php if(Request::route()->getName() === 'category.index'): ?> active <?php endif; ?>">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="fas fa-tags"></i>
						</div>
						<div class="menu-text d-flex align-items-center">Catégories</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'category.index'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('category.index')); ?>" class="menu-link">
								<div class="menu-text">Liste Catégories</div>
							</a>
						</div>
					</div>
				</div>

				<div class="menu-item has-sub <?php if(Request::route()->getName() === 'product.index' OR Request::route()->getName() === 'menu.index'): ?> active <?php endif; ?>">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="fas fa-cube"></i>
							<!-- <span class="w-5px h-5px rounded-3 bg-theme position-absolute top-0 end-0 mt-3px me-3px"></span> -->
						</div>
						<div class="menu-text d-flex align-items-center">Produits</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'product.index'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('product.index')); ?>" class="menu-link">
								<div class="menu-text">Liste Produits</div>
							</a>
						</div>
					</div>
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'menu.index'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('menu.index')); ?>" class="menu-link">
								<div class="menu-text">Liste menu</div>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- POS -->
			<div class="menu-header">POS</div>
			<div class="menu-item has-sub <?php if(Request::route()->getName() === 'history'): ?> active <?php endif; ?>">
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
						<a href="<?php echo e(route('sale.index')); ?>" target="_blank" class="menu-link">
							<div class="menu-text">Poste de vente</div>
						</a>
					</div>

					<div class="menu-item <?php if(Request::route()->getName() === 'history'): ?> active <?php endif; ?>">
						<a href="<?php echo e(route('history')); ?>" class="menu-link">
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
			<?php if(auth()->user()->user_type!=3): ?>
				<div class="menu-header">CODE PROMO</div>
				<div class="menu-item <?php if(Request::route()->getName() === 'code.index'): ?> active <?php endif; ?>">
					<a href="<?php echo e(route('code.index')); ?>" class="menu-link">
						<span class="menu-icon"><i class="fas fa-barcode"></i></span>
						<span class="menu-text">Code promo</span>
					</a>
				</div>
			<?php endif; ?>

			<!-- AMS -->
			<?php if(auth()->user()->user_type!=3): ?>
				<div class="menu-header">COMPTABILITE</div>
				<div class="menu-item has-sub 
					<?php if(Request::route()->getName() === 'cash-account.index' || Request::route()->getName() === 'transaction.index' 
					|| Request::route()->getName() === 'ams.settings' || Request::route()->getName() === 'ams.dashboard'): ?>
						active 
					<?php endif; ?>">
					<a href="javascript:;" class="menu-link">
						<div class="menu-icon">
							<i class="fas fa-balance-scale"></i>
						</div>
						<div class="menu-text d-flex align-items-center">Comptabilité</div>
						<span class="menu-caret"><b class="caret"></b></span>
					</a>
					<!--dashboard-->
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'ams.dashboard'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('ams.dashboard')); ?>" class="menu-link">
								<span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
								<span class="menu-text">Tableau de bord</span>
							</a>
						</div>
					</div>
					<!--cash-->
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'cash-account.index'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('cash-account.index')); ?>" class="menu-link">
								<span class="menu-icon"><i class="fas fa-wallet"></i></span>
								<span class="menu-text">Caisse</span>
							</a>
						</div>
					</div>
					<!--operations-->
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'transaction.index'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('transaction.index')); ?>" class="menu-link">
								<span class="menu-icon"><i class="fas fa-exchange-alt"></i></span>
								<span class="menu-text">Opérations</span>
							</a>
						</div>
					</div>
					<!--setting-->
					<div class="menu-submenu">
						<div class="menu-item <?php if(Request::route()->getName() === 'ams.settings'): ?> active <?php endif; ?>">
							<a href="<?php echo e(route('ams.settings')); ?>" class="menu-link">
								<span class="menu-icon"><i class="fas fa-tools"></i></span>
								<span class="menu-text">Paramètres</span>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- UTILISATEURS -->
			<div class="menu-divider"> </div>
			<?php if(auth()->user()->user_type!=3): ?>
				<div class="menu-header">UTILISATEURS</div>
				<div class="menu-item <?php if(Request::route()->getName() === 'user.index'): ?> active <?php endif; ?>">
					<a href="<?php echo e(route('user.index')); ?>" class="menu-link">
						<span class="menu-icon"><i class="bi bi-people"></i></span>
						<span class="menu-text">Utilisateurs</span>
					</a>
				</div>
			<?php endif; ?>
			<div class="menu-item <?php if(Request::route()->getName() === 'profil'): ?> active <?php endif; ?>">
				<a href="<?php echo e(route('profil')); ?>" class="menu-link">
					<span class="menu-icon"><i class="fas fa-id-badge"></i></span>
					<span class="menu-text">Profil</span>
				</a>
			</div>
			<?php if(auth()->user()->user_type!=3): ?>
			<div class="menu-header">PARAMETRES</div>
			<div class="menu-item has-sub <?php if(Request::route()->getName() === 'company.index'): ?> active <?php endif; ?>">
				<a href="javascript:;" class="menu-link">
					<div class="menu-icon">
						<i class="bi bi-gear"></i>
					</div>
					<div class="menu-text d-flex align-items-center">Parametres</div>
					<span class="menu-caret"><b class="caret"></b></span>
				</a>
				<div class="menu-submenu">
					<div class="menu-item <?php if(Request::route()->getName() === 'company.index'): ?> active <?php endif; ?>">
						<a href="<?php echo e(route('company.index')); ?>" class="menu-link">
							<div class="menu-text">Compagnie</div>
						</a>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<div class="p-3 px-4 mt-auto">
			<a href="documentation/index.html" target="_blank" class="btn d-block btn-outline-theme">
				
				<?php echo e(config('app.name')); ?>

			</a>
		</div>
	</div>

</div>


<button class="app-sidebar-mobile-backdrop" data-toggle-target=".app" data-toggle-class="app-sidebar-mobile-toggled"></button><?php /**PATH C:\POS\resources\views/partials/menu.blade.php ENDPATH**/ ?>