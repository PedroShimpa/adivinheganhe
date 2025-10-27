<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Adivinhação</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">
    <div style="background-color: #fff; border-radius: 8px; padding: 20px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2 style="color: #2c3e50;">🤔 Uma nova adivinhação chegou!</h2>
        <p style="font-size: 16px; color: #333;">
            Uma nova adivinhação foi publicada: <strong>{{ $titulo }}</strong>
        </p>
        <p>
            <a href="{{ $url }}" style="display: inline-block; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
                Tentar agora!
            </a>
        </p>
        <p style="color: #888; font-size: 12px;">Obrigado por jogar com a gente!</p>
        @if(isset($this))
        {!! $buildTrackingPixel() !!}
        @endif
    </div>
</body>
</html>
