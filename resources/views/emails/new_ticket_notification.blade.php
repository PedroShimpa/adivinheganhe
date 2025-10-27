<p>Olá administrador,</p>

<p>Um novo chamado foi aberto com as seguintes informações:</p>

<ul>
    <li><strong>Nome:</strong> {{ $nome }}</li>
    @if($email)
        <li><strong>Email:</strong> {{ $email }}</li>
    @endif
    <li><strong>Categoria:</strong> {{ $categoria }}</li>
    <li><strong>Descrição:</strong><br>{{ $descricao }}</li>
</ul>

<p>Acesse o painel administrativo para verificar e responder ao chamado.</p>
{!! $buildTrackingPixel() !!}
