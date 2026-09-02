@props(['title' => null, 'description' => null, 'icon' => 'bi-inbox'])
<div {{ $attributes->class('saas-empty-state') }}>
    @if($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif
    @if($title)<h2>{{ $title }}</h2>@endif
    @if($description)<p>{{ $description }}</p>@endif
    {{ $slot }}
</div>
