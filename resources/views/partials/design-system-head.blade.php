@php
    $appearanceUser = auth()->user();
    $appearanceMode = in_array($appearanceUser?->appearance_mode, ['light', 'dark', 'system'], true)
        ? $appearanceUser->appearance_mode
        : 'dark';
    $accentColor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $appearanceUser?->accent_color)
        ? strtoupper($appearanceUser->accent_color)
        : '#3B82F6';
@endphp
<script>
    (() => {
        const mode = @json($appearanceMode);
        const accent = @json($accentColor);
        const resolved = mode === 'system'
            ? (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark')
            : mode;
        const root = document.documentElement;
        root.dataset.dsThemePreference = mode;
        root.dataset.dsTheme = resolved;
        root.dataset.bsTheme = resolved;
        root.style.setProperty('--ds-accent', accent);
    })();
</script>
<link href="{{ asset('hub/assets/css/design-system.css') }}?v=20260902-4" rel="stylesheet">
<link href="{{ asset('hub/assets/css/password-toggle.css') }}?v=20260902-1" rel="stylesheet">
<link href="{{ asset('hub/assets/css/datatable-loading.css') }}?v=20260901-2" rel="stylesheet">
