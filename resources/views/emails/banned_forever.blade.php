<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Conta Banida - Plataforma</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #dc3545;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
            font-size: 15px;
        }
        .content h2 {
            margin-top: 0;
            color: #dc3545;
        }
        .footer {
            background: #f1f1f1;
            padding: 15px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Aviso de Banimento</h1>
        </div>
        <div class="content">
            <h2>Conta banida por atividade suspeita</h2>
            <p>
                Detectamos comportamentos irregulares em sua conta que indicam possível uso 
                de mecanismos automatizados, trapaças ou outros meios que violam as regras 
                da nossa plataforma.
            </p>
            <p>
                Por esse motivo, sua conta foi <strong>banida permanentemente</strong>. 
                Esta medida visa garantir a integridade, a justiça e a segurança de todos os usuários.
            </p>
            <p>
                Caso acredite que houve algum engano, você pode entrar em contato com nossa equipe 
                de suporte para solicitar uma revisão manual do caso. Entretanto, ressaltamos que 
                o banimento poderá ser mantido caso as evidências sejam confirmadas.
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Adivinhe e Ganhe - Todos os direitos reservados.
            {!! $buildTrackingPixel() !!}
        </div>
    </div>
</body>
</html>
