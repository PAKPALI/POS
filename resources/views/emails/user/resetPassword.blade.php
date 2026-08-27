<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin-bottom:8px;">{{ config('app.name') }}</h2>
        <h1>Mot de passe oublié</h1>
    </div>
    <div class="content text-center" style="padding:24px 12px;">
        <h2>Bonjour {{ $user->name }},</h2>
        <p>Une demande de réinitialisation du mot de passe a été effectuée pour votre compte.</p>
        <p style="margin:28px 0;"><a href="{{ $resetUrl }}" class="btn" style="color:#ffffff !important;text-decoration:none !important;">Choisir un nouveau mot de passe</a></p>
        <div class="info">
            <p style="margin:0;">Ce lien est valable pendant {{ $expiresInMinutes }} minutes et ne peut être utilisé que pour votre compte.</p>
        </div>
        <p style="font-size:13px;color:#6b7280;word-break:break-all;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
        <p style="color:#b42318;">Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet e-mail. Votre mot de passe ne sera pas modifié.</p>
    </div>
    @include('emails.design.emailFooter')
</div>
</body>
</html>
