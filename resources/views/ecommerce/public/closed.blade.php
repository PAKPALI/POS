<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boutique fermee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .closed-card {
            text-align: center;
            padding: 52px 40px;
            background: rgba(255,255,255,.04);
            border-radius: 16px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.08);
            max-width: 420px;
            width: 100%;
        }
        .closed-card .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        .closed-card h1 { color: #fff; font-weight: 800; font-size: 1.4rem; margin-bottom: 10px; }
        .closed-card p { color: rgba(255,255,255,.5); font-size: .92rem; line-height: 1.6; margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="closed-card">
        <div class="icon">🛒</div>
        <h1>Boutique momentanement fermee</h1>
        <p>Notre boutique en ligne est en cours de maintenance. Nous revenons tres bientot !</p>
    </div>
</body>
</html>
