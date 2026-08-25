<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes entreprises — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/app.min.css') }}" rel="stylesheet">
    <style>
        :root { --company-accent: #19c37d; }
        body { min-height: 100vh; background: #080b10; }
        .company-page { position: relative; min-height: 100vh; overflow: hidden; }
        .company-page::before { content: ''; position: fixed; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(25,195,125,.16), transparent 32%), radial-gradient(circle at 95% 90%, rgba(50,110,255,.12), transparent 30%); }
        .company-shell { position: relative; z-index: 1; max-width: 1120px; }
        .eyebrow { color: var(--company-accent); font-size: .75rem; letter-spacing: .18em; font-weight: 800; }
        .hero-copy { max-width: 610px; color: rgba(255,255,255,.58); }
        .company-card { height: 100%; color: inherit; background: rgba(20,24,31,.88); border: 1px solid rgba(255,255,255,.09); border-radius: 18px; transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease; }
        .company-card:hover { transform: translateY(-4px); border-color: rgba(25,195,125,.55); box-shadow: 0 18px 45px rgba(0,0,0,.28); }
        .company-card.active-company { border-color: var(--company-accent); box-shadow: 0 0 0 1px rgba(25,195,125,.15); }
        .company-name { color: #fff !important; font-weight: 750; line-height: 1.35; overflow-wrap: anywhere; word-break: break-word; white-space: normal; }
        .company-logo { width: 58px; height: 58px; flex: 0 0 58px; border-radius: 16px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg, #1fd18a, #117d5a); color: white; font-size: 1.2rem; font-weight: 800; }
        .company-logo img { width: 100%; height: 100%; object-fit: cover; }
        .role-pill { display: inline-flex; align-items: center; padding: .35rem .65rem; border-radius: 999px; background: rgba(255,255,255,.07); color: rgba(255,255,255,.65); font-size: .76rem; }
        .active-pill { background: rgba(25,195,125,.14); color: #62e6ad; }
        .open-company { border-radius: 11px; font-weight: 700; }
        .create-card { display: flex; min-height: 210px; align-items: center; justify-content: center; text-align: center; border: 1px dashed rgba(255,255,255,.22); background: rgba(255,255,255,.025); }
        .create-card:hover { border-color: var(--company-accent); background: rgba(25,195,125,.055); }
        .create-icon { width: 48px; height: 48px; margin: 0 auto 1rem; border-radius: 14px; display: grid; place-items: center; background: rgba(25,195,125,.13); color: var(--company-accent); font-size: 1.5rem; }
        .selection-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
    </style>
</head>
<body class="text-white">
<div class="company-page">
    <main class="container company-shell py-4 py-lg-5">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div class="d-flex align-items-center gap-3">
                <div class="company-logo" style="width:44px;height:44px;flex-basis:44px;border-radius:12px">POS</div>
                <div><div class="fw-bold">{{ config('app.name') }}</div><div class="small text-white-50">Espace professionnel</div></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block"><div class="small fw-bold">{{ auth()->user()->name }}</div><div class="small text-white-50">Compte connecté</div></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm">Déconnexion</button></form>
            </div>
        </header>

        <section class="mb-5">
            <div class="eyebrow text-uppercase mb-2">Vos espaces de travail</div>
            <h1 class="display-6 fw-bold mb-2">Quelle entreprise souhaitez-vous ouvrir ?</h1>
            <p class="hero-copy mb-0">Chaque entreprise possède ses propres produits, ventes, utilisateurs et permissions. Votre contexte sera adapté dès l’ouverture.</p>
            <div class="selection-actions mt-4">
                @if($returnUrl)
                    <a href="{{ $returnUrl }}" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left me-2"></i>Retour à l’application
                    </a>
                    <span class="small text-white-50">Aucun changement d’entreprise ne sera effectué.</span>
                @else
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light">
                            <i class="bi bi-box-arrow-left me-2"></i>Quitter sans choisir
                        </button>
                    </form>
                @endif
            </div>
        </section>

        @if(session('error'))<div class="alert alert-danger border-0">{{ session('error') }}</div>@endif
        @if(session('success'))<div class="alert alert-success border-0">{{ session('success') }}</div>@endif

        <div class="row g-4">
            @forelse($memberships as $membership)
                @php $isActive = (int) $activeCompanyId === (int) $membership->company_id; @endphp
                <div class="col-lg-4 col-md-6">
                    <form method="POST" action="{{ route('companies.switch', $membership->company_id) }}" class="h-100">@csrf
                        <div class="company-card {{ $isActive ? 'active-company' : '' }} p-4 d-flex flex-column">
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="company-logo">
                                    @if($membership->company->logo)<img src="{{ asset($membership->company->logo) }}" alt="">@else{{ mb_strtoupper(mb_substr($membership->company->name, 0, 2)) }}@endif
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <h3 class="h5 mb-1 company-name">{{ $membership->company->name }}</h3>
                                    <div class="small text-white-50 text-truncate">{{ $membership->company->email ?: 'Aucun e-mail renseigné' }}</div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="role-pill">Rôle : {{ $membership->role->name ?? 'Non attribué' }}</span>
                                @if($isActive)<span class="role-pill active-pill">Entreprise active</span>@endif
                            </div>
                            <button class="btn {{ $isActive ? 'btn-outline-theme' : 'btn-theme' }} open-company mt-auto" type="submit">
                                {{ $isActive ? 'Continuer dans cette entreprise' : 'Ouvrir cette entreprise' }}
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-warning border-0">Votre compte n’est rattaché à aucune entreprise active. Vous pouvez créer votre première entreprise maintenant.</div></div>
            @endforelse

            <div class="col-lg-4 col-md-6">
                <a href="{{ route('companies.create') }}" class="company-card create-card p-4 text-decoration-none">
                    <div><div class="create-icon">+</div><h3 class="h5 text-white">Créer une entreprise</h3><p class="text-white-50 small mb-0">Ajoutez un nouvel espace indépendant à votre compte.</p></div>
                </a>
            </div>
        </div>
    </main>
</div>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}"></script>
</body>
</html>
