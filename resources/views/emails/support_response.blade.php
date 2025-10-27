<p>Olá,</p>

<p>Seu chamado de suporte foi atualizado com as seguintes informações:</p>

<ul>
    <li><strong>Categoria:</strong> {{ $suporte->categoria->descricao ?? 'Desconhecida' }}</li>
    <li><strong>Descrição:</strong><br>{{ $suporte->descricao }}</li>
    <li><strong>Status:</strong> 
        @if($suporte->status === 'A')
            Aguardando
        @elseif($suporte->status === 'EA')
            Em Atendimento
        @elseif($suporte->status === 'F')
            Finalizado
        @else
            Desconhecido
        @endif
    </li>
    @if(!empty($suporte->admin_response))
        <li><strong>Resposta do Suporte:</strong><br>{{ $suporte->admin_response }}</li>
    @endif
</ul>

<p>Obrigado por usar nosso sistema de suporte.</p>

