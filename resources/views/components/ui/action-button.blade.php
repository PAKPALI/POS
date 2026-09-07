@props(['label', 'variant' => 'primary', 'href' => null, 'type' => 'button', 'loadingText' => null])
@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['saas-ui-action-btn', "is-{$variant}"]) }} aria-label="{{ $label }}" title="{{ $label }}">{{ $slot }}</a>
@else
    <button type="{{ $type }}" @if($loadingText) data-loading-text="{{ $loadingText }}" @endif {{ $attributes->class(['saas-ui-action-btn', "is-{$variant}"]) }} aria-label="{{ $label }}" title="{{ $label }}">{{ $slot }}</button>
@endif
