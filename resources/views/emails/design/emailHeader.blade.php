@php
    $emailHeaderTitle = $title ?? config('mail.brand_name');
    $emailHeaderSubtitle = $subtitle ?? null;
@endphp
<div class="header">
    <h2 style="margin-bottom:{{ $emailHeaderSubtitle ? '8px' : '0' }};">{{ $emailHeaderTitle }}</h2>
    @if ($emailHeaderSubtitle)
        <p style="margin:0;color:#ff9f43;">{{ $emailHeaderSubtitle }}</p>
    @endif
</div>
