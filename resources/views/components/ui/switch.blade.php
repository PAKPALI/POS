@props(['id', 'label', 'hint' => null, 'checked' => false])
<label {{ $attributes->only('class')->class('saas-ui-switch') }} for="{{ $id }}">
    <span class="saas-ui-switch-copy"><strong>{{ $label }}</strong>@if($hint)<small>{{ $hint }}</small>@endif</span>
    <input id="{{ $id }}" type="checkbox" @checked($checked) {{ $attributes->except('class') }}>
    <span class="saas-ui-switch-control" aria-hidden="true"></span>
</label>
