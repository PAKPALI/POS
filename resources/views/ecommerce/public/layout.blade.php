<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $company->name ?? 'Boutique')</title>
    <script>
        (function () {
            var savedTheme = localStorage.getItem('ecommerce_theme');
            var preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', savedTheme || preferredTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --acc: #2563eb;
            --acc-h: #1d4ed8;
            --dark: #0f172a;
            --darker: #020617;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow-lg: 0 10px 40px rgba(0,0,0,.08);
            --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        [data-theme="dark"] {
            color-scheme: dark;
            --dark: #f8fafc;
            --darker: #ffffff;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --border: #273449;
            --bg: #0b1120;
            --card-bg: #111827;
            --shadow: 0 1px 3px rgba(0,0,0,.28);
            --shadow-lg: 0 14px 42px rgba(0,0,0,.38);
        }
        * { box-sizing: border-box; }
        body {
            font-family: var(--font);
            background-color: var(--bg);
            background-image:
                radial-gradient(circle at 8% 8%, rgba(37,99,235,.11), transparent 24rem),
                radial-gradient(circle at 92% 28%, rgba(99,102,241,.08), transparent 28rem),
                linear-gradient(180deg, rgba(255,255,255,.72), transparent 34rem),
                linear-gradient(rgba(15,23,42,.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15,23,42,.07) 1px, transparent 1px),
                linear-gradient(135deg, #f8fbff 0%, #f1f5ff 48%, #eef6ff 100%);
            background-size: auto, auto, auto, 24px 24px, 24px 24px, auto;
            background-position: center, center, center, center, center, center;
            background-attachment: fixed;
            color: var(--text);
            margin: 0;
            -webkit-font-smoothing: antialiased;
            transition: background-color .35s ease, color .35s ease;
        }
        [data-theme="dark"] body {
            background-image:
                radial-gradient(circle at 8% 8%, rgba(37,99,235,.18), transparent 27rem),
                radial-gradient(circle at 92% 24%, rgba(79,70,229,.14), transparent 30rem),
                linear-gradient(180deg, rgba(15,23,42,.42), transparent 36rem),
                linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px),
                linear-gradient(135deg, #07101f 0%, #0b1120 48%, #10172a 100%);
            background-size: auto, auto, auto, 24px 24px, 24px 24px, auto;
        }
        a { color: var(--acc); text-decoration: none; }
        a:hover { color: var(--acc-h); }

        .shop-nav {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(226,232,240,.6);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: box-shadow .3s;
        }
        .shop-nav.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
        [data-theme="dark"] .shop-nav { background:rgba(15,23,42,.88); border-bottom-color:rgba(51,65,85,.75); }
        [data-theme="dark"] .shop-nav.scrolled { box-shadow:0 8px 28px rgba(0,0,0,.28); }
        [data-theme="dark"] .hero-modern,
        [data-theme="dark"] .sidebar-card .card-header,
        [data-theme="dark"] .footer-modern { background:#020617; }
        [data-theme="dark"] .card { background-color:var(--card-bg); color:var(--text); }
        .shop-nav .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 68px;
        }
        .shop-nav .nav-brand {
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .shop-nav .nav-brand img { max-height: 42px; border-radius: 8px; }
        .shop-nav .nav-brand i { font-size: 1.6rem; color: var(--acc); }
        .shop-nav .nav-links { display: flex; align-items: center; gap: 4px; }
        .shop-nav .nav-links a {
            padding: 8px 16px;
            border-radius: 8px;
            color: var(--muted);
            font-weight: 500;
            font-size: .9rem;
            transition: all .2s;
            text-decoration: none;
        }
        .shop-nav .nav-links a:hover { color: var(--text); background: #f1f5f9; }
        .shop-nav .nav-links a.active { color: var(--acc); background: #eff6ff; }
        .cart-wrap {
            position: relative;
            color: var(--text);
            font-size: 1.3rem;
            text-decoration: none;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: background .2s;
        }
        .cart-wrap:hover { background: #f1f5f9; }
        .theme-toggle {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            background: var(--card-bg);
            cursor: pointer;
            transition: transform .25s, background .25s, border-color .25s;
        }
        .theme-toggle:hover { transform:translateY(-2px) rotate(8deg); border-color:var(--acc); color:var(--acc); }
        [data-theme="dark"] .cart-wrap:hover,
        [data-theme="dark"] .shop-nav .nav-links a:hover { background:#1e293b; }
        .cart-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: var(--acc);
            color: #fff;
            font-size: .65rem;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            box-shadow: 0 2px 6px rgba(37,99,235,.3);
        }
        .cart-badge.bump { animation: cartBump .35s ease; }
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: var(--text);
            padding: 8px;
            cursor: pointer;
        }

        .page-wrap { min-height: 65vh; }
        .main-container { padding-top: 24px; padding-bottom: 48px; }

        .sidebar-card {
            border: none;
            border-radius: var(--radius);
            background: var(--card-bg);
            box-shadow: var(--shadow);
            overflow: hidden;
            position: sticky;
            top: 92px;
            margin-bottom: 20px;
        }
        .sidebar-card .card-header {
            background: var(--dark);
            color: #fff;
            border: none;
            padding: 14px 20px;
            font-weight: 700;
            font-size: .85rem;
            letter-spacing: .3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cat-list .list-group-item {
            border: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 11px 20px;
            color: var(--muted);
            font-weight: 500;
            font-size: .88rem;
            transition: all .2s;
        }
        .cat-list .list-group-item:last-child { border-bottom: none; }
        .cat-list .list-group-item:hover { background: #f8fafc; color: var(--acc); padding-left: 26px; }
        .cat-list .list-group-item.active { background: #eff6ff; color: var(--acc); font-weight: 600; border-left: 3px solid var(--acc); border-radius: 0; }
        .cat-list .list-group-item .count {
            background: #f1f5f9;
            padding: 1px 9px;
            border-radius: 20px;
            font-size: .72rem;
            color: var(--muted);
            font-weight: 600;
        }

        .product-card {
            border: none;
            border-radius: var(--radius);
            background: var(--card-bg);
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
            isolation: isolate;
            transition: all .3s cubic-bezier(.4,0,.2,1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .product-card::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            padding: 1.5px;
            border-radius: inherit;
            background: conic-gradient(
                from var(--glow-angle, 0deg),
                transparent 0 68%,
                rgba(96,165,250,.2) 74%,
                #60a5fa 82%,
                #2563eb 88%,
                transparent 96%
            );
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            animation: productGlowSpin 4.8s linear infinite;
        }
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 45px rgba(37,99,235,.18), var(--shadow-lg);
        }
        .product-card:hover::before {
            padding: 2px;
            background: conic-gradient(
                from var(--glow-angle, 0deg),
                transparent 0 55%,
                #93c5fd 69%,
                #3b82f6 80%,
                #2563eb 88%,
                transparent 100%
            );
        }
        @property --glow-angle {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false;
        }
        @keyframes productGlowSpin { to { --glow-angle: 360deg; } }
        .product-card .img-wrap {
            aspect-ratio: 1/1;
            overflow: hidden;
            background: #f1f5f9;
            position: relative;
            flex-shrink: 0;
        }
        .product-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s cubic-bezier(.4,0,.2,1);
        }
        .product-card:hover .img-wrap img { transform: scale(1.1); }
        .product-card .img-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #cbd5e1;
            font-size: 3rem;
        }
        .product-card .badge {
            position: absolute;
            top: 12px;
            left: 12px;
            font-size: .68rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
            letter-spacing: .3px;
            backdrop-filter: blur(4px);
        }
        .product-card .card-body {
            padding: 14px 16px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-card .cat-label {
            font-size: .7rem;
            font-weight: 600;
            color: var(--acc);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 3px;
        }
        .product-card .product-name {
            font-size: .95rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2px;
            line-height: 1.3;
            flex: 1;
        }
        .product-card .product-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--acc);
            margin-bottom: 10px;
        }
        .product-card .product-price .currency { font-size: .7rem; font-weight: 600; color: var(--muted); }
        .product-card .qty-input {
            width: 52px;
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: .3rem;
            text-align: center;
            font-weight: 600;
            font-size: .82rem;
            outline: none;
            transition: border-color .2s;
        }
        .product-card .qty-input:focus { border-color: var(--acc); }
        .product-card .add-to-cart-btn {
            border: 2px solid var(--border);
            border-radius: 8px;
            background: transparent;
            color: var(--text);
            font-weight: 600;
            font-size: .78rem;
            padding: .4rem .6rem;
            transition: all .25s;
            cursor: pointer;
            white-space: nowrap;
        }
        .product-card .add-to-cart-btn:hover { background: var(--acc); border-color: var(--acc); color: #fff; }
        .product-card .add-to-cart-btn.added {
            background: #059669;
            border-color: #059669;
            color: #fff;
        }
        .product-card .add-to-cart-btn:disabled { opacity: .4; cursor: not-allowed; }
        [data-theme="dark"] .cat-list .list-group-item { background:var(--card-bg); border-bottom-color:var(--border); }
        [data-theme="dark"] .cat-list .list-group-item:hover { background:#172033; }
        [data-theme="dark"] .cat-list .list-group-item.active,
        [data-theme="dark"] .btn-outline-custom:hover { background:#172554; }
        [data-theme="dark"] .cat-list .count,
        [data-theme="dark"] .product-card .img-wrap,
        [data-theme="dark"] .product-card .add-to-cart-btn:hover:disabled { background:#1e293b; }
        [data-theme="dark"] .form-control { background:#0f172a; color:var(--text); border-color:var(--border)!important; }
        [data-theme="dark"] .form-control::placeholder { color:#64748b; }

        .btn-primary-custom {
            background: var(--acc);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .6rem 1.6rem;
            border-radius: 8px;
            transition: all .25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary-custom:hover { background: var(--acc-h); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(37,99,235,.35); }
        .btn-outline-custom {
            border: 2px solid var(--border);
            background: transparent;
            color: var(--text);
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .25s;
        }
        .btn-outline-custom:hover { border-color: var(--acc); color: var(--acc); background: #eff6ff; }

        .hero-modern {
            background: linear-gradient(145deg, var(--dark) 0%, #1e293b 100%);
            border-radius: var(--radius-lg);
            padding: 48px 40px;
            margin-bottom: 36px;
            position: relative;
            overflow: hidden;
        }
        .hero-modern::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 20%, rgba(37,99,235,.15) 0%, transparent 60%),
                        radial-gradient(ellipse at 20% 80%, rgba(37,99,235,.08) 0%, transparent 50%);
            pointer-events: none;
        }
        .hero-modern::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: .5;
            pointer-events: none;
        }
        .hero-modern .hero-content { position: relative; z-index: 1; }
        .hero-modern h1 {
            color: #fff;
            font-weight: 900;
            font-size: 2rem;
            margin-bottom: 10px;
            letter-spacing: -.5px;
        }
        .hero-modern p {
            color: rgba(255,255,255,.65);
            font-size: 1rem;
            max-width: 560px;
            line-height: 1.7;
            margin-bottom: 22px;
        }
        .hero-modern .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .hero-modern .hero-actions a {
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: .9rem;
            transition: all .25s;
            text-decoration: none;
        }
        .hero-modern .hero-actions .hero-btn-primary {
            background: var(--acc);
            color: #fff;
        }
        .hero-modern .hero-actions .hero-btn-primary:hover { background: var(--acc-h); box-shadow: 0 4px 20px rgba(37,99,235,.4); }
        .hero-modern .hero-actions .hero-btn-outline {
            background: rgba(255,255,255,.08);
            color: #e2e8f0;
            border: 1px solid rgba(255,255,255,.12);
        }
        .hero-modern .hero-actions .hero-btn-outline:hover { background: rgba(255,255,255,.14); }
        .hero-modern .hero-stats {
            display: flex;
            gap: 36px;
            margin-top: 28px;
            position: relative;
            z-index: 1;
        }
        .hero-modern .hero-stats .stat-item {}
        .hero-modern .hero-stats .stat-item h3 {
            color: #fff;
            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 1px;
            letter-spacing: -.5px;
        }
        .hero-modern .hero-stats .stat-item span {
            color: rgba(255,255,255,.5);
            font-size: .78rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .section-title {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--dark);
            margin-bottom: 2px;
        }
        .section-subtitle {
            color: var(--muted);
            font-size: .88rem;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; color: #cbd5e1; }
        .empty-state h5 { font-weight: 700; color: var(--text); }

        .footer-modern {
            background: var(--dark);
            color: rgba(255,255,255,.55);
            padding: 48px 0 0;
            margin-top: 60px;
        }
        .footer-modern h5, .footer-modern h6 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 14px;
            font-size: .95rem;
        }
        .footer-modern p { font-size: .85rem; line-height: 1.6; }
        .footer-modern a {
            color: rgba(255,255,255,.55);
            text-decoration: none;
            display: block;
            padding: 3px 0;
            font-size: .85rem;
            transition: color .2s;
        }
        .footer-modern a:hover { color: var(--acc); }
        .footer-modern .divider { border-color: rgba(255,255,255,.08); margin: 32px 0 0; }
        .footer-modern .bottom {
            padding: 20px 0;
            text-align: center;
            font-size: .78rem;
            color: rgba(255,255,255,.35);
        }

        .toast-modern {
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 500;
            font-size: .85rem;
            box-shadow: 0 8px 30px rgba(0,0,0,.15);
        }
        .page-enter { animation: fadeUp .45s both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .pulse-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }
        @keyframes cartBump { 50% { transform: scale(1.28); } }

        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e8edf3 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        @media (prefers-reduced-motion: reduce) {
            .product-card::before { animation: none; --glow-angle: 220deg; }
        }
        @media (max-width: 991px) {
            .shop-nav .nav-links { display: none; }
            .shop-nav .nav-links.open {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255,255,255,.98);
                backdrop-filter: blur(16px);
                padding: 12px 24px;
                border-bottom: 1px solid var(--border);
                box-shadow: 0 20px 40px rgba(0,0,0,.08);
                gap: 2px;
            }
            [data-theme="dark"] .shop-nav .nav-links.open { background:rgba(15,23,42,.98); }
            .mobile-menu-btn { display: block; }
            .sidebar-card { position: static; }
            .hero-modern { padding: 32px 24px; }
            .hero-modern h1 { font-size: 1.5rem; }
        }
        @media (max-width: 575px) {
            .hero-modern { padding: 24px 20px; border-radius: var(--radius); }
            .hero-modern h1 { font-size: 1.3rem; }
            .hero-modern .hero-stats { gap: 20px; }
            .hero-modern .hero-stats .stat-item h3 { font-size: 1.2rem; }
            .main-container { padding-top:16px; padding-left:10px; padding-right:10px; }
            .product-grid-row { --bs-gutter-x:10px; --bs-gutter-y:10px; }
            .product-card { border-radius:10px; }
            .product-card:hover { transform:translateY(-3px); }
            .product-card .img-wrap { aspect-ratio:1/1; }
            .product-card .card-body { padding:10px; }
            .product-card .badge { top:7px; left:7px; padding:3px 6px; font-size:.58rem; }
            .product-card .cat-label { font-size:.58rem; letter-spacing:.4px; }
            .product-card .product-name { font-size:.78rem; min-height:2.1em; }
            .product-card .product-price { font-size:.88rem; margin-bottom:7px; }
            .product-card .product-price .currency { font-size:.58rem; }
            .product-card .qty-input { width:38px; padding:.25rem .1rem; font-size:.72rem; border-width:1px; }
            .product-card .add-to-cart-btn { padding:.32rem .25rem; font-size:.68rem; border-width:1px; }
            .product-card .add-to-cart-btn i { display:none; }
            .product-card .btn-outline-custom { padding:.32rem!important; font-size:.66rem!important; border-width:1px; }
            .theme-toggle { width:36px; height:36px; }
        }
        @media (max-width: 370px) {
            .product-card .add-to-cart-btn { font-size:.61rem; }
            .product-card .card-body { padding:8px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="shop-nav" id="shopNav">
        <div class="container nav-inner">
            <a class="nav-brand" href="{{ url('/shop') }}">
                @if($company->logo)
                    <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}">
                @else
                    <i class="bi bi-shop"></i> {{ $company->name }}
                @endif
            </a>
            <button class="mobile-menu-btn" onclick="document.getElementById('navLinks').classList.toggle('open')">
                <i class="bi bi-list"></i>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="{{ url('/shop') }}" class="{{ request()->is('shop') && !request()->is('shop/') ? 'active' : '' }}">Accueil</a>
                <a href="{{ url('/shop/products') }}" class="{{ request()->is('shop/products*') ? 'active' : '' }}">Produits</a>
                {{-- <a href="{{ url('/shop/checkout') }}" class="{{ request()->is('shop/checkout*') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i>
                    <span class="cart-badge" id="cartCount" style="position:static;display:none;margin-left:4px;">0</span>
                    Panier
                </a> --}}
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="theme-toggle" id="themeToggle" aria-label="Activer le mode nuit" title="Changer le thème">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <a class="cart-wrap" href="{{ url('/shop/checkout') }}">
                    <i class="bi bi-bag"></i>
                    <span class="cart-badge" id="cartCountDesktop">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="page-wrap">
        <div class="container main-container">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="sidebar-card card">
                        <div class="card-header"><i class="bi bi-grid"></i> Categories</div>
                        <div class="list-group list-group-flush cat-list">
                            <a href="{{ url('/shop') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->is('shop') && !request()->is('shop/category/*') ? 'active' : '' }}">
                                Tous les produits
                                <span class="count">{{ \App\Models\Product::where('status',1)->where('type',1)->where('qte','>',0)->count() }}</span>
                            </a>
                            @foreach($categories as $cat)
                                @php $catCount = $cat->products()->where('status',1)->where('type',1)->where('qte','>',0)->count(); @endphp
                                <a href="{{ url('/shop/category/'.$cat->id) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->is('shop/category/'.$cat->id) ? 'active' : '' }}">
                                    {{ $cat->name }}
                                    <span class="count">{{ $catCount }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 page-enter">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-modern">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5>{{ $company->name }}</h5>
                    @if($company->description)<p>{{ Str::limit($company->description, 160) }}</p>@endif
                </div>
                <div class="col-md-3">
                    <h6>Liens</h6>
                    <a href="{{ url('/shop') }}">Accueil</a>
                    <a href="{{ url('/shop/products') }}">Produits</a>
                    <a href="{{ url('/shop/checkout') }}">Panier</a>
                </div>
                <div class="col-md-3">
                    <h6>Contact</h6>
                    <a href="tel:{{ $company->number1 }}"><i class="bi bi-telephone me-2"></i>{{ $company->number1 }}</a>
                    @if($company->email)<a href="mailto:{{ $company->email }}"><i class="bi bi-envelope me-2"></i>{{ $company->email }}</a>@endif
                </div>
                <div class="col-md-2">
                    <h6>Adresse</h6>
                    <p style="margin:0;">{{ $company->adress ?? '-' }}</p>
                </div>
            </div>
            <hr class="divider">
            <div class="bottom">&copy; {{ date('Y') }} {{ $company->name }}. Tous droits reserves.</div>
        </div>
    </footer>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index:9999;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var CART_KEY = 'ecommerce_cart';
        var DEFAULT_PRODUCT_IMAGE = @json(asset('icons/product-placeholder.svg'));
        function getCart() {
            try {
                var stored = JSON.parse(localStorage.getItem(CART_KEY));
                if (!Array.isArray(stored)) return [];
                return stored.map(function(item) {
                    return {
                        product_id: parseInt(item.product_id, 10),
                        name: String(item.name || 'Produit'),
                        price: Number(item.price) || 0,
                        quantity: Math.max(1, parseInt(item.quantity, 10) || 1),
                        image: item.image ? String(item.image) : DEFAULT_PRODUCT_IMAGE
                    };
                }).filter(function(item) { return Number.isFinite(item.product_id); });
            } catch(e) { return []; }
        }
        function saveCart(c) { localStorage.setItem(CART_KEY, JSON.stringify(c)); updateCartCount(); }
        function updateCartCount() {
            var cart = getCart(), count = cart.length;
            $('.cart-badge').each(function() {
                count > 0 ? $(this).text(count).css('display','flex') : $(this).hide();
            });
        }
        function addToCart(id, name, price, qty, image) {
            id = parseInt(id, 10);
            if (!qty || qty < 1) qty = 1;
            var cart = getCart(), existing = cart.find(function(i){return parseInt(i.product_id, 10) === id;});
            if (existing) {
                existing.quantity += qty;
                existing.name = name;
                existing.price = parseFloat(price);
                if (image) existing.image = image;
            } else {
                cart.push({product_id:id, name:name, price:parseFloat(price), quantity:qty, image:image || ''});
            }
            saveCart(cart);
            $('.cart-badge').removeClass('bump');
            requestAnimationFrame(function(){ $('.cart-badge').addClass('bump'); });
            showToast(name + ' ajouté au panier');
        }
        function showToast(msg) {
            var id = 't-'+Date.now();
            var colors = ['#065f46','#1d4ed8','#7c3aed','#db2777'];
            var bg = colors[Math.floor(Math.random()*colors.length)];
            var el = $('<div id="'+id+'" class="toast-modern toast" style="background:'+bg+'"><div class="toast-body"><i class="bi bi-check-circle-fill me-2"></i>'+msg+'</div></div>');
            $('#toastContainer').append(el);
            new bootstrap.Toast(el[0],{delay:2200}).show();
            setTimeout(function(){el.remove();},2500);
        }
        function updateThemeButton() {
            var dark = document.documentElement.getAttribute('data-theme') === 'dark';
            $('#themeToggle')
                .attr('aria-label', dark ? 'Activer le mode jour' : 'Activer le mode nuit')
                .find('i').attr('class', dark ? 'bi bi-sun-fill' : 'bi bi-moon-stars');
        }
        $(function(){
            updateCartCount();
            updateThemeButton();
            $('#themeToggle').on('click', function() {
                var current = document.documentElement.getAttribute('data-theme');
                var next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('ecommerce_theme', next);
                updateThemeButton();
            });
            $(document).on('click', '.add-to-cart-btn', function(){
                var btn = $(this);
                addToCart(parseInt(btn.attr('data-id')), btn.attr('data-name'), parseFloat(btn.attr('data-price')), parseInt(btn.closest('.product-card').find('.qty-input').val())||1, btn.attr('data-image') || '');
                var original = btn.html();
                btn.addClass('added').html('<i class="bi bi-check2"></i> Ajouté');
                setTimeout(function(){ btn.removeClass('added').html(original); }, 1100);
            });
            $(window).on('scroll', function() {
                $('#shopNav').toggleClass('scrolled', $(this).scrollTop() > 20);
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.shop-nav').length) {
                    $('#navLinks').removeClass('open');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
