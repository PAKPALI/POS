@props(['active'=>false])
<a {{ $attributes->class([$active?'bg-indigo-50 border-indigo-400 text-indigo-700':'border-transparent text-gray-600','block pl-3 pr-4 py-2 border-l-4 text-base font-medium']) }}>{{ $slot }}</a>
