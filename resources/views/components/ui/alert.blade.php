@props(['variant' => 'info', 'role' => null])
<div {{ $attributes->class(["saas-alert", "saas-alert-{$variant}"]) }} role="{{ $role ?: ($variant === 'danger' ? 'alert' : 'status') }}">{{ $slot }}</div>
