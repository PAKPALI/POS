@props(['title', 'description' => null, 'eyebrow' => null, 'icon' => null])
<header {{ $attributes->class('saas-ui-page-header') }}>
    <div class="saas-ui-page-header-copy">
        @if($eyebrow)<span class="saas-ui-eyebrow">@if($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif{{ $eyebrow }}</span>@endif
        <h1>{{ $title }}</h1>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="saas-ui-page-header-actions">{{ $actions }}</div>@endisset
</header>
