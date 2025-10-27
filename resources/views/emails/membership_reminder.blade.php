<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vamos finalizar a compra do seu VIP?</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #6a1b9a;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #00f7ff;
            color: #333;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎮 Adivinhe e Ganhe</h1>
        <h2>Vamos finalizar a compra do seu VIP?</h2>
    </div>

    <div class="content">
        <p>Olá!</p>

        <p>Notamos que você visitou nossa página de membership VIP, mas ainda não finalizou a compra.</p>

        <p>Como membro VIP, você terá acesso a:</p>
        <ul>
            <li>✅ Adivinhações exclusivas</li>
            <li>✅ Sem anúncios</li>
            <li>✅ Suporte prioritário</li>
            <li>✅ Benefícios especiais</li>
        </ul>

        <p>Não perca essa oportunidade! Clique no botão abaixo para se tornar um membro VIP:</p>

        <div style="text-align: center;">
            <a href="https://adivinheganhe.com.br/seja-membro" class="button">Seja Membro VIP Agora</a>
        </div>

        <p>Se você tiver alguma dúvida, estamos aqui para ajudar!</p>

        <p>Atenciosamente,<br>
        Equipe Adivinhe e Ganhe</p>
    </div>

    <div class="footer">
        <p>Este é um email automático. Por favor, não responda diretamente a este email.</p>
        <p>© {{ date('Y') }} Adivinhe e Ganhe. Todos os direitos reservados.</p>
        {!! $trackingPixel !!}
    </div>
</body>
</html>
