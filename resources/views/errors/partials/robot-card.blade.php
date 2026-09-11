@php
    $isPlatformError = auth('platform')->check() && request()->routeIs('platform.*');
    $isPartnerError = auth('partner')->check() && request()->routeIs('partner.*');
    $homeUrl = $isPlatformError
        ? route('platform.dashboard')
        : ($isPartnerError ? route('partner.dashboard') : (auth()->check() ? route('companies.select') : url('/')));
    $homeLabel = $isPlatformError ? 'Vue générale' : ($isPartnerError ? 'Espace partenaire' : (auth()->check() ? 'Mes entreprises' : 'Accueil'));
    $homeIcon = $isPlatformError ? 'bi-grid-1x2-fill' : ($isPartnerError ? 'bi-people-fill' : (auth()->check() ? 'bi-buildings' : 'bi-house-door-fill'));
@endphp

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <section class="robot-error-card card overflow-hidden" aria-labelledby="robot-error-title">
            <div class="card-body p-4 p-md-5">
                <span class="robot-error-signal {{ $tone ?? '' }}"><i class="bi {{ $signalIcon ?? 'bi-exclamation-circle' }}" aria-hidden="true"></i>{{ $eyebrow ?? 'Information' }}</span>
                <img src="{{ asset('hub/assets/img/errors/access-denied-robot.png') }}"
                     class="robot-error-illustration {{ $robotContext ?? '' }}"
                     alt="Petit robot assistant signalant {{ strtolower($title ?? 'une situation inhabituelle') }}">
                <span class="robot-error-code">ERREUR {{ $status ?? 500 }}</span>
                <h1 id="robot-error-title" class="h3 mt-2 mb-3">{{ $title ?? 'Une erreur est survenue' }}</h1>
                <p class="text-secondary mb-4">{{ $description ?? 'Notre assistant technique a été averti. Vous pouvez revenir à une page sûre ou réessayer dans un instant.' }}</p>

                <div class="robot-error-actions d-flex flex-wrap gap-2" aria-label="Actions disponibles">
                    <a href="{{ $homeUrl }}" class="robot-error-button robot-error-button-primary"><i class="bi {{ $homeIcon }} me-2" aria-hidden="true"></i>{{ $homeLabel }}</a>
                    @if(($showBack ?? true) === true)
                        <button type="button" class="robot-error-button robot-error-button-secondary" data-error-back data-error-fallback="{{ $homeUrl }}"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Revenir en arrière</button>
                    @endif
                </div>

                @if(!empty($helpText))
                    <p class="small text-secondary mt-4 mb-0">{{ $helpText }}</p>
                @endif
            </div>
        </section>
    </div>
</div>
