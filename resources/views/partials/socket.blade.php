<script src="{{ asset('vendor/pusher/pusher.min.js') }}"></script>
<script src="{{ asset('vendor/laravel-echo/echo.min.js') }}"></script>

<script>
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const EchoCtor = window.Echo;

    window.Echo = new EchoCtor({
        broadcaster: 'pusher',
        key: '{{ env("REVERB_APP_KEY") }}',
        wsHost: '{{ env("VITE_REVERB_HOST", "adivinheganhe.com.br") }}',
        wsPort: '{{ env("VITE_REVERB_PORT", 443) }}',
        forceTLS: false,
        disableStats: true,
        authEndpoint: '/broadcasting/auth-mixed',
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        }
    });

    window.Echo.channel('adivinhacoes')
        .listen('.resposta.aprovada', e => {
            $('input[name="resposta"], .btn-primary').prop('disabled', true);
            Swal.fire('Adivinhação encerrada', e.mensagem, 'info');

            const id = e.adivinhacaoId;
            $(`#resposta-${id}`).prop('disabled', true);
            $(`#btn-resposta-${id}`).prop('disabled', true);
        })
        .listen('.resposta.contagem', e => {
            const id = e.adivinhacaoId;
            $(`#count-respostas-${id}`).html(e.contagem);
        })
        .listen('.alerta.global', e => {
            Swal.fire(e.titulo, e.msg, e.tipo)
        });

    window.Echo.channel('comments')
        .listen('.novoComentario', e => {

              comment = adicionarComentario(e);
              $box = $(`#comentarios-${e.adivinhacaoId}`);
              $list = $box.find('.comentarios-list');
              $list.append(comment)
        })


    @auth
    window.Echo.private(`user.{{ Auth::id() }}`)
        .listen('.resposta.sucesso', e => {
            Swal.fire(e.title ?? 'Parabéns!', e.mensagem, 'success').then(() => {
                const id = e.adivinhacaoId;
                $(`#resposta-${id}`).prop('disabled', true);
                $(`#btn-resposta-${id}`).prop('disabled', true);
            });
        });
    @endauth

    function adicionarMensagem(texto, tipo = 'message') {
        const $msg = $('<div class="message"></div>');
        if (tipo === 'user') {
            $msg.addClass('user');
        }
        $msg.text(texto);

        const $chatMessages = $('#chatMessages');
        $chatMessages.append($msg);

        const mensagens = $chatMessages.children('.message');
        if (mensagens.length > 200) {
            mensagens.slice(0, mensagens.length - 200).remove();
        }

        $chatMessages.scrollTop($chatMessages[0].scrollHeight);
    }

    
    function adicionarComentario(comentario) {
      return `
        <div class="mb-2 p-2 rounded-3 bg-white shadow-sm">
            <strong>${comentario.usuario}:</strong> ${comentario.body}
        </div>`;
    }

    window.Echo.channel('chat')
        .listen('.MensagemEnviada', e => {
            var tipo = 'message'
            @auth
            if("{{ auth()->user()->username }}" == e.usuario) {
                tipo = 'user'
            }
            @endauth

            adicionarMensagem(`${e.usuario}: ${e.mensagem}`, tipo);

            if ($('#chatBody').hasClass('d-none')) {
                $('#chatNotification').removeClass('d-none');
            }
        });

   
</script>