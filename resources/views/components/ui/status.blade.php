@props(['variant' => 'neutral'])
<span {{ $attributes->class(["saas-status-badge", "is-{$variant}"]) }}>{{ $slot }}</span>
