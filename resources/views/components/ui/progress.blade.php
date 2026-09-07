@props(['value' => 0, 'label' => null])
@php($percent = max(0, min(100, (float) $value)))
<div {{ $attributes->class('saas-ui-progress') }} role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}" @if($label) aria-label="{{ $label }}" @endif style="--saas-progress: {{ $percent }}%"><span></span></div>
