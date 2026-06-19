<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Rapport hebdomadaire inventaire</title>
    @include('emails.design.emailStyle')
</head>

<body>
    <div class="container">

        <div class="header">
            <h2>{{ $company->name ?? config('app.name') }}</h2>
            <p style="color:#ffff;">- Rapport hebdomadaire des inventaires -</p>
        </div>

        <div class="info">
            <h3>Bonjour {{ $user->name }}</h3>
            <p>
                Voici le rapport des inventaires de la période :
                <strong>
                    {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}
                    -
                    {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                </strong>
            </p>

            <p>Le rapport complet est disponible en pièce jointe.</p>
        </div>

        @include('emails.design.emailFooter')

    </div>
</body>

</html>