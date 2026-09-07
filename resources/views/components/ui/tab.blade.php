@props(['active' => false, 'href' => null, 'type' => 'button'])
@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['saas-ui-tab', $active ? 'is-active' : null]) }} @if($active) aria-current="page" @endif>{{ $slot }}</a>
@else
    <button type="{{ $type }}" role="tab" aria-selected="{{ $active ? 'true' : 'false' }}" {{ $attributes->class(['saas-ui-tab', $active ? 'is-active' : null]) }}>{{ $slot }}</button>
@endif
