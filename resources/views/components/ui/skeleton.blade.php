@props(['lines' => 3])
<div {{ $attributes->class('saas-skeleton') }} aria-busy="true" aria-label="Chargement">@for($line = 0; $line < $lines; $line++)<span></span>@endfor</div>
