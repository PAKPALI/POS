@props(['variant' => 'primary', 'size' => null, 'type' => 'button'])
<button type="{{ $type }}" {{ $attributes->class(['saas-btn', "saas-btn-{$variant}", $size ? "saas-btn-{$size}" : null]) }}>{{ $slot }}</button>
