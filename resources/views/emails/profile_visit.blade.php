<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $username }} visitou seu perfil!</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 22px;
        }
        .email-body {
            padding: 25px 30px;
            text-align: center;
        }
        .email-body p {
            font-size: 16px;
            line-height: 1.5;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 28px;
            font-size: 16px;
            color: #fff;
            background: #4f46e5;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #3730a3;
        }
        .email-footer {
            text-align: center;
            font-size: 13px;
            color: #777;
            padding: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>👀 {{ $username }} visitou seu perfil!</h1>
        </div>
        <div class="email-body">
            <p>Você recebeu uma nova visita no seu perfil. Clique no botão abaixo para ver quem foi.</p>

            <a href="{{ route('profile.view', $username) }}" class="button">Ver Perfil</a>
        </div>
        <div class="email-footer">
            <p>Este é um e-mail automático enviado por <strong>{{ config('app.name') }}</strong>.
            Não responda diretamente a esta mensagem.</p>
            {!! $buildTrackingPixel() !!}
        </div>
    </div>
</body>
</html>
