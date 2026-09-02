@props(['name', 'role' => null, 'active' => false, 'logo' => null])
<article {{ $attributes->class(['saas-company-card', 'is-active' => $active]) }}>
    <div class="saas-company-card-head"><div class="saas-company-logo">@if($logo)<img src="{{ $logo }}" alt="">@else{{ mb_strtoupper(mb_substr($name, 0, 2)) }}@endif</div><div><h3>{{ $name }}</h3>@if($role)<p>{{ $role }}</p>@endif</div></div>
    {{ $slot }}
</article>
