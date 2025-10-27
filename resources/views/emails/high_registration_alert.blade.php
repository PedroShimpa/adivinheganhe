<!DOCTYPE html>
<html>
<head>
    <title>Alerta de Alto Número de Registros</title>
</head>
<body>
    <h1>Alerta: Alto Número de Registros de Usuários</h1>
    <p>O adivinhe e ganhe está com alto número de registro de usuários.</p>
    @if($unsubscribeUrl !== '#')
        <p style="font-size: 12px; color: #666;">
            If you no longer wish to receive these emails, <a href="{{ $unsubscribeUrl }}">unsubscribe here</a>.
        </p>
    @endif
    {!! $trackingPixel !!}
</body>
</html>
