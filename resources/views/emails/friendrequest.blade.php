<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Adivinhe e Ganhe - Pedido de amizade</title>
</head>

<body>
    <h2>Olá, {{ $toUser }} 👋</h2>
    <p>Você recebeu um pedido de amizade de <strong>{{ $fromUser }}</strong>.</p>
    <p>
        <a href="{{ $friendRequestRoute }}" style="display:inline-block;padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:6px;">
            Ver pedidos de amizade
        </a>
    </p>
</body>

</html>