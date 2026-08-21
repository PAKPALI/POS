<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Invitation</title>@include('emails.design.emailStyle')</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ $invitation->company->name }}</h2>
        <p>Invitation à rejoindre l’entreprise</p>
    </div>
    <div class="content">
        <p>Bonjour,</p>
        <p><strong>{{ $invitation->inviter?->name }}</strong> vous invite à rejoindre <strong>{{ $invitation->company->name }}</strong> avec le rôle <strong>{{ $invitation->role?->name }}</strong>.</p>
        <p>Cette invitation expire le {{ $invitation->expires_at->format('d/m/Y à H:i') }}.</p>
        <p style="text-align:center;margin:28px 0"><a href="{{ $url }}" class="btn" style="color:#ffffff !important;text-decoration:none !important;">Consulter l’invitation</a></p>
        <p style="font-size:12px;color:#777">Ce lien est personnel, valable 48 heures et utilisable une seule fois. Ne le transférez pas. Si vous n’attendiez pas cette invitation, vous pouvez l’ignorer ou la refuser depuis le lien.</p>
    </div>
    @include('emails.design.emailFooter')
</div>
</body>
</html>
