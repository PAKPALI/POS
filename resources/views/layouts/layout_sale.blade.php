<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
	<head>
		<meta charset="utf-8">
		<title>POS-sale</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content>
		<meta name="author" content>

		<!-- Inclure PDF.js -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js"></script>

		<link href="{{asset('hub/assets/css/vendor.min.css')}}" rel="stylesheet">
		<link href="{{asset('hub/assets/css/app.min.css')}}" rel="stylesheet">
		<link href="{{asset('hub/assets/plugins/jvectormap-next/jquery-jvectormap.css')}}" rel="stylesheet">
		<!-- DataTables CSS -->
		<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
		 <!--link j-query-->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<style>
			/* hover effect on product */
			.product-hover {
				transform: scale(1.05);
				transition: transform 0.2s ease;
			}

			/* click effect on  */

			/* Animation de l'ombre */
			@keyframes shadowWhiteToRed {
				0% {
					box-shadow: 0 0 10px rgba(255, 255, 255, 0.8); /* Ombre blanche */
				}
				100% {
					box-shadow: 0 0 10px rgba(255, 0, 0, 0.8); /* Ombre rouge */
				}
			}
			.product-clicked {
				animation: shadowWhiteToRed 0.4s forwards; /* Animation de l'ombre en 0.4s */
				transition: transform 0.2s ease;
				border: 2px solid rgba(255, 0, 0, 0.5); /* Bord rouge léger */
			}

			.img {
				width: 100%;
				height: 150px; /* Ajustez la hauteur comme souhaité */
				background-size: cover; /* Assure que l'image couvre tout l'espace sans se déformer */
				background-position: center; /* Centre l'image pour un meilleur cadrage */
				border-radius: 8px; /* Facultatif : pour donner un effet arrondi */
				overflow: hidden; /* Empêche le débordement */
			}

			#page-preloader {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: rgba(255, 255, 255, 0.9); /* Fond opaque */
				display: flex;
				justify-content: center;
				align-items: center;
				z-index: 99999; /* Couvrir toute la page */
			}
			#page-preloader img {
				width: 150px; /* Ajustez la taille */
				height: auto;
				animation: zoom 0.8s infinite ease-in-out, vibrate 0.5s infinite linear;
			}

		</style>
		
</head>

	<body>
		<body class="pace-top">
		<div id="page-preloader">
			<img src="{{asset('hub/assets/img/logo.png')}}" alt="Chargement...">
		</div>

			<div id="app" class="app app-content-full-height app-without-sidebar app-without-header">
				@yield('content')
			</div>

			<!-- theme panel -->
			<div class="app-theme-panel">
				<div class="app-theme-panel-container">
					<a href="javascript:;" data-toggle="theme-panel-expand" class="app-theme-toggle-btn"><i
							class="bi bi-sliders"></i></a>
					<div class="app-theme-panel-content">
						<div class="small fw-bold text-inverse mb-1">Display Mode</div>
						<div class="card mb-3">

							<div class="card-body p-2">
								<div class="row gx-2">
									<div class="col-6">
										<a href="javascript:;" data-toggle="theme-mode-selector" data-theme-mode="dark"
											class="app-theme-mode-link active">
											<div class="img"><img src="hub/assets/img/mode/dark.jpg" class="object-fit-cover"
													height="76" width="76" alt="Dark Mode"></div>
											<div class="text">Dark</div>
										</a>
									</div>
									<div class="col-6">
										<a href="javascript:;" data-toggle="theme-mode-selector" data-theme-mode="light"
											class="app-theme-mode-link">
											<div class="img"><img src="hub/assets/img/mode/light.jpg" class="object-fit-cover"
													height="76" width="76" alt="Light Mode"></div>
											<div class="text">Light</div>
										</a>
									</div>
								</div>
							</div>

							<div class="card-arrow">
								<div class="card-arrow-top-left"></div>
								<div class="card-arrow-top-right"></div>
								<div class="card-arrow-bottom-left"></div>
								<div class="card-arrow-bottom-right"></div>
							</div>
						</div>
						<div class="small fw-bold text-inverse mb-1">Theme Color</div>
						<div class="card mb-3">
							<div class="card-body p-2">
								<div class="app-theme-list">
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-pink" data-theme-class="theme-pink"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Pink">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-red" data-theme-class="theme-red"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Red">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-warning" data-theme-class="theme-warning"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Orange">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-yellow" data-theme-class="theme-yellow"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Yellow">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-lime" data-theme-class="theme-lime"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Lime">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-green" data-theme-class="theme-green"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Green">&nbsp;</a></div>
									<div class="app-theme-list-item active"><a href="javascript:;"
											class="app-theme-list-link bg-teal" data-theme-class
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Default">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-info" data-theme-class="theme-info"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Cyan">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-primary" data-theme-class="theme-primary"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Blue">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-purple" data-theme-class="theme-purple"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Purple">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-indigo" data-theme-class="theme-indigo"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Indigo">&nbsp;</a></div>
									<div class="app-theme-list-item"><a href="javascript:;"
											class="app-theme-list-link bg-gray-100" data-theme-class="theme-gray-200"
											data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover"
											data-bs-container="body" data-bs-title="Gray">&nbsp;</a></div>
								</div>

							</div>

							<div class="card-arrow">
								<div class="card-arrow-top-left"></div>
								<div class="card-arrow-top-right"></div>
								<div class="card-arrow-bottom-left"></div>
								<div class="card-arrow-bottom-right"></div>
							</div>

						</div>
						<div class="small fw-bold text-inverse mb-1">Theme Cover</div>
						<div class="card">
							<div class="card-body p-2">
								<div class="app-theme-cover">
									<div class="app-theme-cover-item active">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-1.jpg);"
											data-theme-cover-class data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Default">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-2.jpg);"
											data-theme-cover-class="bg-cover-2" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 2">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-3.jpg);"
											data-theme-cover-class="bg-cover-3" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 3">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-4.jpg);"
											data-theme-cover-class="bg-cover-4" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 4">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-5.jpg);"
											data-theme-cover-class="bg-cover-5" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 5">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-6.jpg);"
											data-theme-cover-class="bg-cover-6" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 6">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-7.jpg);"
											data-theme-cover-class="bg-cover-7" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 7">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-8.jpg);"
											data-theme-cover-class="bg-cover-8" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 8">&nbsp;</a>
									</div>
									<div class="app-theme-cover-item">
										<a href="javascript:;" class="app-theme-cover-link"
											style="background-image: url(hub/assets/img/cover/cover-thumb-9.jpg);"
											data-theme-cover-class="bg-cover-9" data-toggle="theme-cover-selector"
											data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body"
											data-bs-title="Cover 9">&nbsp;</a>
									</div>
								</div>
							</div>
							<div class="card-arrow">
								<div class="card-arrow-top-left"></div>
								<div class="card-arrow-top-right"></div>
								<div class="card-arrow-bottom-left"></div>
								<div class="card-arrow-bottom-right"></div>
							</div>

						</div>
					</div>
				</div>
			</div>

			<a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
		</div>

		<script data-cfasync="false" src="{{asset('cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js')}}"></script>
		
		<script src="{{asset('hub/assets/js/vendor.min.js')}}" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script src="{{asset('hub/assets/js/app.min.js')}}" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script src="{{asset('hub/assets/plugins/jvectormap-next/jquery-jvectormap.min.js')}}" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script src="{{asset('hub/assets/plugins/jvectormap-content/world-mill.js')}}" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script src="{{asset('hub/assets/plugins/apexcharts/dist/apexcharts.min.js')}}" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script src="{{asset('hub/assets/js/demo/dashboard.demo.js')}}" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script src="{{asset('hub/assets/js/demo/pos-customer-order.demo.js')}}" type="c87805f60b464443bf38d728-text/javascript"></script>
		<script async src="https://www.googletagmanager.com/gtag/js?id=G-Y3Q0VGQKY3" type="9763b948d984f4be9ade72e9-text/javascript"></script>
		<script type="9763b948d984f4be9ade72e9-text/javascript">
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}
			gtag('js', new Date());

			gtag('config', 'G-Y3Q0VGQKY3');
		</script>
		<script src="{{asset('cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js')}}" data-cf-settings="9763b948d984f4be9ade72e9-|49" defer></script>
		<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"rayId":"8a3d40de7d4c8877","version":"2024.6.1","r":1,"serverTiming":{"name":{"cfL4":true}},"token":"4db8c6ef997743fda032d4f73cfeff63","b":1}' crossorigin="anonymous"></script>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
		<!-- DataTables JS -->
		<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
		<script>
			const image = document.querySelector("#page-preloader img");
			let scale = 1; // Échelle initiale
			let direction = 1; // Indique si on "zoome" ou "dézoome"

			function animateZoom() {
				// Augmenter ou réduire l'échelle
				scale += direction * 0.005;
				
				// Inverser la direction au seuil
				if (scale >= 1.2 || scale <= 1) {
					direction *= -1;
				}

				// Appliquer la transformation
				image.style.transform = `scale(${scale})`;

				// Répéter l'animation
				requestAnimationFrame(animateZoom);
			}
			// Lancer l'animation
			animateZoom();
			function animateVibrate() {
				const offsetX = (Math.random() - 0.05) * 4; // Déplacement horizontal
				const offsetY = (Math.random() - 0.05) * 4; // Déplacement vertical

				image.style.transform = `translate(${offsetX}px, ${offsetY}px)`;

				// Répéter l'animation
				setTimeout(animateVibrate, 1000);
			}

			// Lancer l'animation
			animateVibrate();


			window.addEventListener("load", function () {
				const preloader = document.getElementById("page-preloader");
				preloader.style.opacity = "0.5"; // Transition douce
				setTimeout(() => preloader.style.display = "none", 500); // Masque après l'animation
			});

		</script>
	</body>
</html>