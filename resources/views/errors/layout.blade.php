@php
    $isPlatformError = auth('platform')->check() && request()->routeIs('platform.*');
    $isPartnerError = auth('partner')->check() && request()->routeIs('partner.*');
    $errorLayout = $isPlatformError
        ? 'layouts.platform'
        : ($isPartnerError ? 'layouts.partner' : (auth()->check() ? 'layouts.saas' : 'layouts.public-auth'));
@endphp
@extends($errorLayout)

@push('styles')
    <link href="{{ asset('hub/assets/css/error-pages.css') }}?v=20260910-1" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid py-4 py-md-5">
        @yield('error-content')
    </div>
@endsection

@push('scripts')
<script>
(() => {
    document.querySelectorAll('[data-error-back]').forEach((button) => {
        button.addEventListener('click', () => {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.assign(button.dataset.errorFallback || '/');
        });
    });
})();
</script>
@endpush
