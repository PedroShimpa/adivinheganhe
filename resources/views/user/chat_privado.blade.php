@extends('layouts.app')

@section('content')
<div class="container py-3" style="max-width: 700px;">
    <div class="card shadow-sm rounded-3">
        <div class="card-header d-flex align-items-center justify-content-between bg-primary text-white">
            <h5 class="m-0">Chat com {{ $user->username }}</h5>
        </div>

        <div id="chatMessages" class="card-body overflow-auto" style="height:400px; background:#f1f3f5;">
            {{-- Mensagens carregadas aqui --}}
        </div>

        <form id="chatForm" class="card-footer p-3 bg-light d-flex gap-2">
            <input type="hidden" name="receiver_id" value="{{ $user->id }}">
            <input type="text" name="message" class="form-control rounded-pill px-3" placeholder="Digite sua mensagem..." autocomplete="off">
            <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" type="submit" style="width:45px; height:45px;">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const userId = {{ $user->id }};
    const chatMessages = $('#chatMessages');

    // Função para adicionar mensagem ao chat
    function appendMessage(sender, text) {
        const isMe = sender == {{ auth()->user()->id }};
        const alignment = isMe ? 'justify-content-end' : 'justify-content-start';
        const bg = isMe ? 'bg-primary text-white' : 'bg-white text-dark';
        const messageHtml = `
            <div class="d-flex ${alignment} mb-2">
                <div class="p-2 ${bg} rounded-3" style="max-width: 70%; word-wrap: break-word;">
                    ${text}
                </div>
            </div>
        `;
        chatMessages.append(messageHtml);
        chatMessages.stop().animate({ scrollTop: chatMessages[0].scrollHeight }, 300);
    }

    // Carrega mensagens antigas
    $.get("{{ route('chat.buscar', $user->id) }}", function(data){
        data.forEach(msg => appendMessage(msg.user_id, msg.mensagem));
    });

    // Envia mensagem
    $('#chatForm').on('submit', function(e){
        e.preventDefault();
        const message = $(this).find('input[name="message"]').val();
        if(!message) return;

        $.post("{{ route('chat.enviar') }}", {
            message: message,
            receiver_id: userId,
            _token: "{{ csrf_token() }}"
        }, function(response){
            appendMessage({{ auth()->user()->id }}, message);
            $('#chatForm')[0].reset();
            $('#chatForm input[name="message"]').focus();
        });
    });

    // Escuta o evento em tempo real
    Echo.private('chat.' + {{ auth()->user()->id }})
        .listen('.mensagem.recebida_enviada', (e) => {
            if(e.senderId == userId){
                appendMessage(e.senderId, e.message);
            }
        });
</script>
@endpush
