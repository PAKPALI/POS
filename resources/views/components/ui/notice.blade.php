@props(['variant' => 'info', 'icon' => null])
@php($icons = ['info' => 'bi-info-circle', 'success' => 'bi-check-circle', 'warning' => 'bi-exclamation-triangle', 'danger' => 'bi-x-octagon'])
<div {{ $attributes->class(['saas-ui-notice', "is-{$variant}"]) }} role="{{ $variant === 'danger' ? 'alert' : 'status' }}">
    <i class="bi {{ $icon ?: ($icons[$variant] ?? $icons['info']) }}" aria-hidden="true"></i><div>{{ $slot }}</div>
</div>
