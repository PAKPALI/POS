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
            <h1>Bonjour {{$name}}</h1>
        </div>
        <div class="content text-center">
            <p><h3>{{$text}}</h3></p>
            <p style="color:red;"> NB: Le mot de passe reste confidentiel</p>
            <!-- <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p> -->
            <!-- <a href="#" class="btn">Contacter le support</a> -->
        </div>
        @include('emails.design.emailFooter')
    </div>
</body>
</html>
