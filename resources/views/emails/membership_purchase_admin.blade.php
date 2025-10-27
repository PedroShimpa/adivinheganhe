<p><strong>{{ $usuario->name }}</strong> ({{ $usuario->email }}) adquiriu o membership VIP!</p>

<p>O usuário agora tem acesso a benefícios exclusivos.</p>
@if($unsubscribeUrl !== '#')
    <p style="font-size: 12px; color: #666;">
        If you no longer wish to receive these emails, <a href="{{ $unsubscribeUrl }}">unsubscribe here</a>.
    </p>
@endif

