<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de l’adresse partenaire</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    @include('emails.design.emailHeader', ['subtitle' => 'Activation du compte partenaire'])

    <div class="content text-center" style="padding:24px 12px;">
        <h2>Bonjour {{ $partner->name }},</h2>
        <p>Merci pour votre inscription. Confirmez votre adresse e-mail pour activer votre espace partenaire Maxanou.</p>

        <p style="margin:28px 0 8px;">
            <a href="{{ $verificationUrl }}" class="btn" style="color:#ffffff !important;text-decoration:none !important;">
                Confirmer mon adresse
            </a>
        </p>

        <div class="info" style="text-align:left;">
            <p style="margin:0;">Ce lien est valable pendant <strong>{{ $expiresInMinutes }} minutes</strong> et ne peut être utilisé qu’une seule fois.</p>
        </div>

        <p style="font-size:12px;color:#6b7280;word-break:break-all;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
            <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
        </p>
        <p style="margin-bottom:0;color:#b42318;">Si vous n’êtes pas à l’origine de cette inscription, ignorez simplement cet e-mail.</p>
    </div>

    @include('emails.design.emailFooter', ['company' => null])
</div>
</body>
</html>
