@props(['title' => null, 'description' => null])
<section {{ $attributes->class('saas-card') }}>
    @if($title || $description || isset($actions))<div class="saas-card-head"><div>@if($title)<h2>{{ $title }}</h2>@endif @if($description)<p class="saas-card-description">{{ $description }}</p>@endif</div>@isset($actions)<div class="saas-card-actions">{{ $actions }}</div>@endisset</div>@endif
    {{ $slot }}
</section>
