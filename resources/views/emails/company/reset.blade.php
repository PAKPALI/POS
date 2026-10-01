<!doctype html>
<html lang="fr"><head><meta charset="UTF-8"><title>Réinitialisation de votre entreprise</title>@include('emails.design.emailStyle')</head>
<body><div class="container">
@include('emails.design.emailHeader', ['subtitle' => 'Sécurité de votre entreprise'])
<div class="content">
    <h2>{{ $code ? 'Confirmez votre demande' : 'Réinitialisation effectuée' }}</h2>
    <p>Entreprise : <strong>{{ $reset->company_name }}</strong></p>
    @if($code)
        <p>Vous avez demandé la suppression des données ci-dessous. Pour autoriser cette opération, saisissez ce code dans l’application :</p>
        <p style="font-size:32px;font-weight:800;letter-spacing:6px;text-align:center;">{{ $code }}</p>
        <p>Ce code est personnel, valable 10 minutes et utilisable une seule fois. Ne le communiquez à personne.</p>
    @else
        <p>La réinitialisation sélective a été effectuée le {{ $reset->completed_at->format('d/m/Y à H:i') }} UTC.</p>
    @endif
    <ul>@foreach($reset->summary as $item)<li><strong>{{ $item['label'] }}</strong> — {{ $item['count'] }} élément(s). {{ $item['impact'] }}</li>@endforeach</ul>
    <p>Votre compte propriétaire, les autres entreprises, l’abonnement, les quotas restants et la configuration de votre entreprise sont conservés.</p>
    <p>Les références des achats de quotas restent conservées par la plateforme pour sécuriser les paiements.</p>
    <p>Si vous n’êtes pas à l’origine de cette demande, changez votre mot de passe et contactez contact@maxanou.com.</p>
</div>
@include('emails.design.emailFooter', ['company' => null])
</div></body></html>
