<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de votre demande de retrait</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin-bottom:8px;">{{ config('app.name') }}</h2>
        <p style="margin:0;color:#ff9f43;">Sécurité du compte partenaire</p>
    </div>

    <div class="content text-center" style="padding:24px 12px;">
        <h2>Bonjour {{ $name }},</h2>
        <p>Une demande de retrait a été initiée depuis votre espace partenaire.</p>
        <p style="margin:24px 0 8px;font-size:13px;color:#6b7280;">Votre code de confirmation</p>
        <div class="info" style="margin:0 auto 22px;padding:18px 12px;background:#f1f1f1;border:1px solid #e5e7eb;border-radius:8px;">
            <p style="margin:0;font-size:32px;line-height:1.2;font-weight:800;letter-spacing:8px;color:#111827;">{{ $code }}</p>
        </div>
        <p>Renseignez ce code dans votre espace partenaire pour confirmer la demande. Aucun versement ne sera lancé avant cette validation.</p>
        <p>Ce code expire dans <strong>{{ $expiry }}</strong> et ne peut être utilisé qu’une seule fois.</p>
        <p style="margin-bottom:0;color:#b42318;">Ne communiquez jamais ce code. Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail et contactez le support.</p>
    </div>

    @include('emails.design.emailFooter', ['company' => null])
</div>
</body>
</html>
