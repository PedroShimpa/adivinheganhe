<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova mensagem do suporte</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { background-color: #f8f8f8; padding: 10px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nova Mensagem do Suporte</h1>
        </div>
        <div class="content">
            <p>Olá {{ $suporte->nome ?? $suporte->user->name ?? 'Cliente' }},</p>

            <p>Você recebeu uma nova mensagem do nosso suporte sobre o seu chamado #{{ $suporte->id }}:</p>

            <div style="background-color: white; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
                <p><strong>Mensagem:</strong></p>
                <p>{{ $message }}</p>
            </div>

            <p>Para responder ou ver o histórico completo, acesse o seu painel de chamados:</p>
            <p><a href="{{ route('suporte.user.show', $suporte) }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ver Chamado</a></p>

            <p>Atenciosamente,<br>
            Equipe de Suporte<br>
            Adivinhe e Ganhe</p>
        </div>
        <div class="footer">
            <p>Este é um email automático, por favor não responda diretamente.</p>
        </div>
    </div>
</body>
</html>
