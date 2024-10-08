<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
    <style>
        /* Styles spécifiques au client de messagerie */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
        }
        p {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            font-weight: 600;
            color: #ffffff;
            text-align: center;
            vertical-align: middle;
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            line-height: 1.6;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease-in-out;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .header {
            background-color: black;
            color: #ffffff;
            text-align: center;
            padding: 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            background-color: #f0f0f0;
            margin-top: 20px;
        }
        .text-center{
            text-align: center;
        }
    </style>
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
        <div class="footer">
            <p>Cet e-mail a été envoyé par <strong class="text-primary">[APP-NAME]</strong> <strong></strong>.</p>
            Copyright © 2024 <strong class="text-primary">[APP-NAME]</strong>. Tous droits réservés. Créé par: <strong class="text-primary">PAKPALI Essolissam Didier</strong>
        </div>
    </div>
</body>
</html>
