<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>{{ $title }}</title>@include('emails.design.emailStyle')</head>
<body><div class="container">
    <div class="header"><h2>{{ config('app.name') }}</h2><p style="color:#ff9f43">Programme partenaires</p></div>
    <div class="info">
        <h3>{{ $title }}</h3>
        <p>{{ $intro }}</p>
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:18px 0;border-collapse:collapse">
            @foreach($details as $label => $value)
                <tr><td style="padding:8px 0;color:#6b7280;border-bottom:1px solid #e5e7eb;width:42%">{{ $label }}</td><td style="padding:8px 0;color:#111827;font-weight:700;border-bottom:1px solid #e5e7eb">{{ $value }}</td></tr>
            @endforeach
        </table>
    </div>
    <p class="text-center"><a class="btn" href="{{ $actionUrl }}" style="color:#fff!important;-webkit-text-fill-color:#fff"><span style="color:#fff!important">Ouvrir la fiche partenaire</span></a></p>
    @include('emails.design.emailFooter', ['company' => null])
</div></body>
</html>
