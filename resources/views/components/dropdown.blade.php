@props(['align'=>'right','width'=>'48','contentClasses'=>'py-1 bg-white'])
@php($alignmentClasses=['left'=>'origin-top-left left-0','top'=>'origin-top','right'=>'origin-top-right right-0'][$align]??'origin-top-right right-0')
@php($widthClasses=['48'=>'w-48'][$width]??$width)
<div class="relative" x-data="{open:false}" @click.outside="open=false" @close.stop="open=false"><div @click="open=!open">{{ $trigger }}</div><div x-show="open" x-transition class="absolute z-50 mt-2 {{ $widthClasses }} rounded-md shadow-lg {{ $alignmentClasses }}" style="display:none"><div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">{{ $content }}</div></div></div>
