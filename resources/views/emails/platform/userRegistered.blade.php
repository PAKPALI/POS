<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau compte utilisateur</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin-bottom:8px;">{{ config('mail.brand_name') }}</h2>
        <p style="margin:0;color:#ff9f43;">Administration SaaS</p>
    </div>

    <div class="content" style="padding:24px 12px;">
        <h2 class="text-center">Nouveau compte utilisateur</h2>
        <p>Un nouveau compte vient d’être créé sur la plateforme.</p>

        <table role="presentation">
            <tr><th colspan="2">Informations du compte</th></tr>
            <tr><td><strong>Nom</strong></td><td>{{ $user->name }}</td></tr>
            <tr><td><strong>E-mail</strong></td><td>{{ $user->email }}</td></tr>
            <tr><td><strong>Téléphone</strong></td><td>{{ $user->phone ?: '—' }}</td></tr>
            <tr><td><strong>Pays</strong></td><td>{{ $user->country_code ?: '—' }}</td></tr>
            <tr><td><strong>Rôle</strong></td><td>{{ $roleName }}</td></tr>
            <tr><td><strong>Créé le</strong></td><td>{{ $registeredAt?->format('d/m/Y à H:i') ?? '—' }}</td></tr>
        </table>

        <table role="presentation">
            <tr><th colspan="2">Entreprise rattachée</th></tr>
            <tr><td><strong>Nom</strong></td><td>{{ $company->name }}</td></tr>
            <tr><td><strong>E-mail</strong></td><td>{{ $company->email ?: '—' }}</td></tr>
            <tr><td><strong>Pays</strong></td><td>{{ $company->country_code ?: '—' }}</td></tr>
        </table>

        <p style="margin:24px 0 0;color:#6b7280;font-size:13px;">Cette notification est informative. Aucune action n’est requise si le compte est attendu.</p>
    </div>

    @include('emails.design.emailFooter', ['company' => null])
</div>
</body>
</html>
