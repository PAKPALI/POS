@php($isPlatformError = auth('platform')->check() && request()->routeIs('platform.*'))
@extends($isPlatformError ? 'layouts.platform' : 'layouts.saas')
@section('title', 'Accès refusé')
@section('page-title', 'Accès refusé')

@push('styles')
<link href="{{ asset('hub/assets/css/error-pages.css') }}?v=20260902-4" rel="stylesheet">
@endpush

@section('content')
@php($permissionMembership = $currentMembership ?? null)
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="permission-denied-card card border-0 shadow-lg overflow-hidden">
                <div class="card-body p-4 p-md-5 text-center">
                    <img src="{{ asset('hub/assets/img/errors/access-denied-robot.png') }}"
                         class="permission-illustration mb-3"
                         alt="Petit robot gardien indiquant poliment que l’accès est réservé">
                    <div class="text-warning fw-bold text-uppercase small mb-2">Accès refusé · Erreur 403</div>
                    <h1 class="h3 mb-3">Cette fonctionnalité n’est pas disponible pour votre rôle</h1>
                    <p class="text-secondary mb-4">
                        {{ $exception->getMessage() ?: "Votre rôle ne vous donne pas accès à cette fonctionnalité dans l’entreprise sélectionnée." }}
                    </p>

                    <div class="permission-actions d-flex flex-wrap justify-content-center gap-2" aria-label="Actions disponibles">
                        @if($isPlatformError)
                            <a href="{{ route('platform.dashboard') }}" class="permission-button permission-button-primary"><i class="bi bi-grid-1x2-fill me-2"></i>Vue générale</a>
                        @elseif($permissionMembership?->hasPermission('dashboard.view'))
                            <a href="{{ route('dashboard') }}" class="permission-button permission-button-primary"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
                        @endif
                        @if($permissionMembership?->hasPermission('clients.manage'))
                            <a href="{{ route('client.index') }}" class="permission-button permission-button-secondary">Clients</a>
                        @endif
                        @if($permissionMembership?->hasPermission('sales.manage'))
                            <a href="{{ route('sale.index') }}" class="permission-button permission-button-secondary">Point de vente</a>
                        @endif
                        @if($permissionMembership?->hasPermission('inventory.manage'))
                            <a href="{{ route('inventory.index') }}" class="permission-button permission-button-secondary">Inventaire</a>
                        @endif
                        @unless($isPlatformError)<a href="{{ $permissionMembership ? route('profil') : route('companies.select') }}" class="permission-button permission-button-secondary"><i class="bi bi-person me-2"></i>{{ $permissionMembership ? 'Mon profil' : 'Mes entreprises' }}</a>@endunless
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
