<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
        @include('emails.design.emailStyle')
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company->name ?? config('app.name') }}</h1>
            <h4 style="color:red;">(Alerte de stock)</h4>
        </div>
        <div class="content text-center">
            <p>
                <h3>
                    {{$text}}<br>
                    {{$text2}}
                </h3>
            </p>
            <p style="color:red;"><strong> NB: Veuillez faire une mise à jour du stock afin d'éviter une rupture totale ! </strong></p>
            <!-- <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p> -->
            <!-- <a href="#" class="btn">Contacter le support</a> -->
        </div>
    @include('emails.design.emailFooter')
    </div>
</body>
</html>
