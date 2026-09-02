@php
    $navigationCompany = $activeCompany ?? null;
    $navigationLogo = $navigationCompany?->logo ? asset($navigationCompany->logo) : asset('hub/assets/img/logo.png');
    $navigationName = $navigationCompany?->name ?? config('app.name', 'Application');
@endphp
<div class="navigation-loader" id="navigationLoader" aria-hidden="true" aria-live="polite">
    <div class="navigation-loader-card" role="status" aria-label="Chargement de {{ $navigationName }}">
        <span class="navigation-loader-mark"><img src="{{ $navigationLogo }}" alt="" width="48" height="48" onerror="this.onerror=null;this.src='{{ asset('hub/assets/img/logo.png') }}';"></span>
        <span class="navigation-loader-spinner" aria-hidden="true"></span>
        <span class="navigation-loader-label">Chargement…</span>
    </div>
</div>
