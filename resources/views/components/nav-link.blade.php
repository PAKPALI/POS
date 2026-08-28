@props(['active'=>false])
<a {{ $attributes->class([$active?'border-indigo-400 text-gray-900':'border-transparent text-gray-500','inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium']) }}>{{ $slot }}</a>
