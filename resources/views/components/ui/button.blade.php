@props(['variant' => 'primary', 'size' => null, 'type' => 'button', 'href' => null, 'loadingText' => null])
@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['saas-btn', "saas-btn-{$variant}", $size ? "saas-btn-{$size}" : null]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @if($loadingText) data-loading-text="{{ $loadingText }}" @endif {{ $attributes->class(['saas-btn', "saas-btn-{$variant}", $size ? "saas-btn-{$size}" : null]) }}>{{ $slot }}</button>
@endif
