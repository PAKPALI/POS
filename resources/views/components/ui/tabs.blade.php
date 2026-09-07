@props(['label' => 'Navigation par onglets'])
<nav {{ $attributes->class('saas-ui-tablist') }} role="tablist" aria-label="{{ $label }}">{{ $slot }}</nav>
