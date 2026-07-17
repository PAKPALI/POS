@extends('ecommerce.public.layout')
@section('title', 'Commande confirmee - '.($company->name ?? 'Boutique'))
@section('content')
    <div style="text-align:center;padding:60px 20px;max-width:500px;margin:0 auto;">
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#16a34a);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:#fff;font-size:2.2rem;box-shadow:0 8px 30px rgba(34,197,94,.3);">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2 style="font-weight:800;font-size:1.5rem;color:var(--dark);">Commander confirmee !</h2>
        <p style="color:var(--muted);margin-bottom:20px;">Merci pour votre commande. Nous vous contacterons bientot.</p>
        @if($code)
            <div style="background:var(--bg);border:2px dashed var(--border);border-radius:var(--radius);padding:20px 28px;display:inline-block;">
                <div style="font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Code commande</div>
                <div style="font-size:1.5rem;font-weight:800;color:var(--acc);letter-spacing:2px;font-family:monospace;">{{ $code }}</div>
            </div>
        @endif
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:28px;">
            <a href="{{ url('/shop') }}" class="btn-primary-custom"><i class="bi bi-house"></i> Accueil</a>
            <a href="{{ url('/shop/products') }}" class="btn-outline-custom"><i class="bi bi-grid"></i> Continuer</a>
        </div>
    </div>
@endsection
