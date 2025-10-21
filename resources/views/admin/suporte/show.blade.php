@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">
    <div class="mb-4">
        <h1 class="h3">Chamado #{{ $suporte->id }}</h1>
        <a href="{{ route('suporte.admin.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5>Detalhes do Chamado</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $suporte->nome ?? $suporte->user->name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $suporte->email ?? $suporte->user->email ?? 'N/A' }}</p>
                    <p><strong>Categoria:</strong> {{ $suporte->categoria->descricao ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        @if($suporte->status === 'A')
                            <span class="badge bg-warning">Aguardando</span>
                        @elseif($suporte->status === 'EA')
                            <span class="badge bg-info">Em Atendimento</span>
                        @elseif($suporte->status === 'F')
                            <span class="badge bg-success">Finalizado</span>
                        @else
                            <span class="badge bg-secondary">Desconhecido</span>
                        @endif
                    </p>
                    <p><strong>Data:</strong> {{ $suporte->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="mt-3">
                <strong>Descrição:</strong>
                <p>{{ $suporte->descricao }}</p>
            </div>
            @if(!empty($suporte->admin_response))
            <div class="mt-3">
                <strong>Resposta do Suporte:</strong>
                <p>{{ $suporte->admin_response }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5>Atualizar Chamado</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('suporte.admin.update', $suporte) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="A" {{ $suporte->status === 'A' ? 'selected' : '' }}>Aguardando</option>
                        <option value="EA" {{ $suporte->status === 'EA' ? 'selected' : '' }}>Em Atendimento</option>
                        <option value="F" {{ $suporte->status === 'F' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="admin_response" class="form-label">Resposta do Suporte</label>
                    <textarea name="admin_response" id="admin_response" class="form-control" rows="5">{{ $suporte->admin_response }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Atualizar</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5>Enviar Mensagem para o Cliente</h5>
        </div>
        <div class="card-body">
            <div id="chat-messages" class="border rounded p-3 mb-3" style="height: 300px; overflow-y: auto;">
                @foreach($suporte->chatMessages as $message)
                <div class="mb-2">
                    <strong>{{ $message->user->name }}:</strong> {{ $message->message }}
                    <small class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                </div>
                @endforeach
            </div>
            <form id="admin-chat-form">
                @csrf
                <div class="input-group mb-2">
                    <input type="text" id="admin-chat-message" class="form-control" placeholder="Digite sua mensagem..." required>
                    <button type="submit" class="btn btn-success">Enviar</button>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="send-push" checked>
                    <label class="form-check-label" for="send-push">
                        Enviar notificação push
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="send-email" checked>
                    <label class="form-check-label" for="send-email">
                        Enviar email
                    </label>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('admin-chat-form');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('admin-chat-message');

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        const sendPush = document.getElementById('send-push').checked;
        const sendEmail = document.getElementById('send-email').checked;

        if (!message) return;

        fetch('/api/suporte/{{ $suporte->id }}/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                send_push: sendPush,
                send_email: sendEmail
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add message to chat
                const messageDiv = document.createElement('div');
                messageDiv.className = 'mb-2';
                messageDiv.innerHTML = `<strong>{{ auth()->user()->name }}:</strong> ${message} <small class="text-muted">${new Date().toLocaleString('pt-BR')}</small>`;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                messageInput.value = '';

                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success mt-3';
                alert.textContent = 'Mensagem enviada com sucesso!';
                chatForm.appendChild(alert);
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert('Erro ao enviar mensagem');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao enviar mensagem');
        });
    });
});
</script>
@endpush
