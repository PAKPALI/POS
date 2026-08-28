<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>{{ $title }}</title>@include('emails.design.emailStyle')</head>
<body><div class="container">
    <div class="header"><h2>{{ config('app.name') }}</h2><p style="color:#ff9f43">Administration SaaS</p></div>
    <div class="info"><h3>{{ $title }}</h3><p>{{ $intro }}</p>
        @if($code)<p style="font-size:32px;font-weight:800;letter-spacing:8px;color:#111827">{{ $code }}</p>@endif
        <p>Cette autorisation expire dans {{ $expiry }}. Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail et contactez le responsable de la plateforme.</p>
    </div>
    @if($actionUrl)<p class="text-center"><a class="btn" href="{{ $actionUrl }}" style="color:#fff!important;-webkit-text-fill-color:#fff"><span style="color:#fff!important">{{ $actionLabel }}</span></a></p>@endif
    @include('emails.design.emailFooter', ['company' => null])
</div></body></html>
