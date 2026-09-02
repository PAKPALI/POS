@props(['variant' => 'neutral'])
<span {{ $attributes->class(["saas-badge", "is-{$variant}"]) }}>{{ $slot }}</span>
