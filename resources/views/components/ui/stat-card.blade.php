@props(['label', 'value', 'hint' => null, 'icon' => null])
<article {{ $attributes->class('saas-metric') }}>
    <div><span>{{ $label }}</span><strong>{{ $value }}</strong>@if($hint)<small>{{ $hint }}</small>@endif</div>
    @if($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif
</article>
