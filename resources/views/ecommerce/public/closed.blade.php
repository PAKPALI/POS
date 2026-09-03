@extends('layouts.public-auth')

@section('title', 'Accès à la boutique')

@push('styles')
<style>
    .storefront-denied { width: min(100%, 720px); margin: 0 auto; }
    .storefront-denied-card {
        position: relative; overflow: hidden; padding: clamp(2rem, 5vw, 4rem) clamp(1.25rem, 5vw, 3.5rem);
        text-align: center; border: 1px solid var(--ds-border, rgba(148, 163, 184, .2)); border-radius: 28px;
        background: var(--ds-surface, rgba(255, 255, 255, .8)); box-shadow: 0 24px 70px rgba(15, 23, 42, .16);
    }
    .storefront-denied-card::before { position: absolute; inset: 0 0 auto; height: 4px; content: ''; background: linear-gradient(90deg, var(--ds-accent, #3b82f6), #20bfa9); }
    .storefront-denied-illustration { display: block; width: min(100%, 250px); height: auto; margin: 0 auto 1.25rem; filter: drop-shadow(0 18px 25px rgba(15, 23, 42, .14)); }
    .storefront-denied-eyebrow { display: inline-flex; align-items: center; gap: .45rem; margin-bottom: .75rem; color: var(--ds-accent, #3b82f6); font-size: .75rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .storefront-denied-title { margin-bottom: .85rem; color: var(--ds-heading, #0f172a); font-size: clamp(1.55rem, 4vw, 2.15rem); font-weight: 800; letter-spacing: -.03em; }
    .storefront-denied-copy { max-width: 500px; margin: 0 auto; color: var(--ds-muted, #64748b); font-size: 1rem; line-height: 1.7; }
</style>
@endpush

@section('content')
<main class="storefront-denied" aria-labelledby="storefront-denied-title">
    <section class="storefront-denied-card">
        <img src="{{ asset('hub/assets/img/errors/access-denied-robot.png') }}" class="storefront-denied-illustration" alt="Illustration indiquant que l’accès à la boutique est momentanément indisponible">
        <div class="storefront-denied-eyebrow"><i class="bi bi-shield-lock" aria-hidden="true"></i>Accès momentanément indisponible</div>
        <h1 id="storefront-denied-title" class="storefront-denied-title">Cette boutique n’est pas accessible pour le moment</h1>
        <p class="storefront-denied-copy">La boutique en ligne est actuellement désactivée ou son abonnement ne permet pas encore l’accès e-commerce. Revenez plus tard ou contactez l’administrateur de l’entreprise.</p>
    </section>
</main>
@endsection
