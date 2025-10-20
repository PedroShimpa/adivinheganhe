@extends('layouts.app')

@section('title', 'Chamado #' . $suporte->id)

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card glass">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">Chamado #{{ $suporte->id }}</h1>
                    <a href="{{ route('suporte.user.index') }}" class="btn btn-light btn-sm">Voltar</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Categoria:</strong> {{ $suporte->categoria->descricao ?? 'N/A' }}</p>
                            <p><strong>Status:</strong>
                                @if($suporte->status === 'A')
                                    <span class="badge bg-warning">Aguardando</span>
                                @elseif($suporte->status === 'EA')
                                    <span class="badge bg-info">Em Atendimento</span>
                                @elseif($suporte->status === 'F')
                                    <span class="badge bg-success">Finalizado</span>
                                @endif
                            </p>
                            <p><strong>Data:</strong> {{ $suporte->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Descrição:</strong>
                            <p>{{ $suporte->descricao }}</p>
                            @if(!empty($suporte->admin_response))
                                <strong>Resposta do Suporte:</strong>
                                <p>{{ $suporte->admin_response }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h5>Chat</h5>
                    <div id="chat-messages" class="border rounded p-3 mb-3" style="height: 300px; overflow-y: auto;">
                        @foreach($suporte->chatMessages as $message)
                            <div class="mb-2">
                                <strong>{{ $message->user->name }}:</strong> {{ $message->message }}
                                <small class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        @endforeach
                    </div>

                    @if($suporte->status !== 'F')
                        <form id="chat-form">
                            @csrf
                            <div class="input-group">
                                <input type="text" id="message" class="form-control" placeholder="Digite sua mensagem..." required>
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const chatMessages = $('#chat-messages');
    const chatForm = $('#chat-form');
    const messageInput = $('#message');

    function scrollToBottom() {
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    scrollToBottom();

    chatForm.on('submit', function(e) {
        e.preventDefault();
        const message = messageInput.val().trim();
        if (!message) return;

        $.ajax({
            url: '{{ route("api.suporte.chat.store", $suporte) }}',
            method: 'POST',
            data: {
                message: message,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                messageInput.val('');
                loadMessages();
            },
            error: function() {
                alert('Erro ao enviar mensagem.');
            }
        });
    });

    function loadMessages() {
        $.ajax({
            url: '{{ route("api.suporte.chat.messages", $suporte) }}',
            method: 'GET',
            success: function(messages) {
                chatMessages.empty();
                messages.forEach(function(msg) {
                    chatMessages.append(`
                        <div class="mb-2">
                            <strong>${msg.user_name}:</strong> ${msg.message}
                            <small class="text-muted">${msg.created_at}</small>
                        </div>
                    `);
                });
                scrollToBottom();
            }
        });
    }

    // Load messages every 5 seconds
    setInterval(loadMessages, 5000);
});
</script>
@endsection
