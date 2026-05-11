<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Code Promo</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; }
            .container { width: 100%; padding: 5px; border: 1px solid #000; }
            h2 { margin-bottom: 20px; }
            .code { font-size: 24px; font-weight: bold; margin-top: 10px; }
            img { max-width: 300px; height: auto; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- <h2>Code Promo : {{ $codePromo->name }}</h2> -->
            <img src="{{ public_path('storage/'.$codePromo->qr_code) }}" alt="Code Barre">
            <p class="code">{{ $jokeCode }}-{{ $codePromo->code }}</p>
            <p>Réduction : {{ $codePromo->percents }}%</p>
        </div>
    </body>
</html>
