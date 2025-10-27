<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo aos VIPs!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #FFD700;
            margin: 0;
            font-size: 28px;
        }
        .welcome-message {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .benefits {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .benefits h2 {
            color: #FFD700;
            margin-top: 0;
        }
        .benefits ul {
            list-style-type: none;
            padding: 0;
        }
        .benefits li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        .benefits li:before {
            content: "✓";
            color: #FFD700;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
        .cta-button {
            display: inline-block;
            background-color: #FFD700;
            color: #333;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Bem-vindo aos VIPs! 🎉</h1>
        </div>

        <div class="welcome-message">
            <p>Olá <strong>{{ $usuario->name }}</strong>,</p>
            <p>Parabéns! Você agora faz parte do nosso exclusivo clube VIP. Estamos muito felizes em recebê-lo nesta comunidade especial!</p>
        </div>

        <div class="benefits">
            <h2>Seus Novos Privilégios VIP</h2>
            <ul>
                <li>Acesso antecipado a adivinhações exclusivas</li>
                <li>7 tentativas diárias (em vez de 3)</li>
                <li>Participação em eventos especiais</li>
                <li>Suporte prioritário</li>
                <li>Badge VIP em seu perfil</li>
                <li>Conteúdo premium e dicas avançadas</li>
            </ul>
        </div>

        <p>Agora você pode explorar todas as adivinhações VIP e aproveitar ao máximo sua experiência no Adivinhe & Ganhe!</p>

        <div style="text-align: center;">
            <a href="{{ url('/') }}" class="cta-button">Explorar Adivinhações VIP</a>
        </div>

        <div class="footer">
            <p>Se tiver alguma dúvida, não hesite em entrar em contato conosco.</p>
            <p>Atenciosamente,<br>Equipe Adivinhe & Ganhe</p>
            {!! $trackingPixel !!}
        </div>
    </div>
</body>
</html>
