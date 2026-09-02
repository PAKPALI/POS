@props(['id', 'label' => null, 'hint' => null, 'error' => null, 'type' => 'text'])
<div {{ $attributes->only('class')->class('saas-form-group') }}>
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    <input id="{{ $id }}" type="{{ $type }}" {{ $attributes->except('class') }} @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
    @if($hint)<small>{{ $hint }}</small>@endif
    @if($error)<small id="{{ $id }}-error" class="saas-field-error" role="alert">{{ $error }}</small>@endif
</div>
