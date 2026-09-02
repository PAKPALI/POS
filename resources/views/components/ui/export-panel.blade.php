@props(['title' => 'Exporter'])
<details {{ $attributes->class('saas-accordion') }}><summary><span>{{ $title }}</span></summary><div class="saas-accordion-body">{{ $slot }}</div></details>
