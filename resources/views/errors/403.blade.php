@extends('layouts.layout')

@push('css-scripts')
<style>
    .permission-illustration {
        width: min(230px, 72vw);
        height: auto;
        filter: drop-shadow(0 18px 28px rgba(0, 0, 0, .32));
        animation: permissionGuardFloat 3.2s ease-in-out infinite;
    }
    @keyframes permissionGuardFloat {
        0%, 100% { transform: translateY(0) rotate(-1deg); }
        50% { transform: translateY(-10px) rotate(1deg); }
    }
    @media (prefers-reduced-motion: reduce) {
        .permission-illustration { animation: none; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card border-0 shadow-lg overflow-hidden">
                <div class="card-body p-4 p-md-5 text-center">
                    <img src="{{ asset('hub/assets/img/errors/access-denied-robot.png') }}"
                         class="permission-illustration mb-3"
                         alt="Petit robot gardien indiquant poliment que l’accès est réservé">
                    <div class="text-warning fw-bold text-uppercase small mb-2">Accès refusé · Erreur 403</div>
                    <h1 class="h3 mb-3">Cette fonctionnalité n’est pas disponible pour votre rôle</h1>
                    <p class="text-secondary mb-4">
                        {{ $exception->getMessage() ?: "Votre rôle ne vous donne pas accès à cette fonctionnalité dans l’entreprise sélectionnée." }}
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @if($currentMembership?->hasPermission('dashboard.view'))
                            <a href="{{ route('dashboard') }}" class="btn btn-theme"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
                        @endif
                        @if($currentMembership?->hasPermission('clients.manage'))
                            <a href="{{ route('client.index') }}" class="btn btn-outline-theme">Clients</a>
                        @endif
                        @if($currentMembership?->hasPermission('sales.manage'))
                            <a href="{{ route('sale.index') }}" class="btn btn-outline-theme">Point de vente</a>
                        @endif
                        @if($currentMembership?->hasPermission('inventory.manage'))
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-theme">Inventaire</a>
                        @endif
                        <a href="{{ route('profil') }}" class="btn btn-outline-secondary"><i class="bi bi-person me-2"></i>Mon profil</a>
                    </div>

                    <p class="small text-secondary mt-4 mb-0">
                        Si vous avez besoin de cet accès, contactez le propriétaire ou un administrateur de l’entreprise.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
