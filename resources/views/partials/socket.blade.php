<script src="{{ asset('vendor/pusher/pusher.min.js') }}"></script>
<script src="{{ asset('vendor/laravel-echo/echo.min.js') }}"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    window.Echo = new Echo({
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
            $(`#count-respostas-${e.adivinhacaoId}`).html(e.contagem);
        })
        .listen('.alerta.global', e => {
            Swal.fire(e.titulo, e.msg, e.tipo)
        });

    window.Echo.channel('comments')
        .listen('.novoComentario', e => {
            const comment = adicionarComentario(e);
            const $box = e.isPost
                ? $(`#comentarios-post-${e.adivinhacaoId}`)
                : $(`#comentarios-${e.adivinhacaoId}`);

            $box.find('.comentarios-list').append(comment);
        });

    @auth
    window.Echo.private(`user.{{ Auth::id() }}`)
        .listen('.resposta.sucesso', e => {
            Swal.fire(e.title ?? 'Parabéns!', e.mensagem, 'success').then(() => {
                const id = e.adivinhacaoId;
                $(`#resposta-${id}`).prop('disabled', true);
                $(`#btn-resposta-${id}`).prop('disabled', true);
            });
        })
        .listen('.notificacao.recebida', e => {
            const toast = $(`
                <div class="toast align-items-center text-white bg-dark border-0 show"
                     role="alert" aria-live="assertive" aria-atomic="true"
                     style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; min-width: 250px;">
                    <div class="d-flex">
                        <div class="toast-body">${e.message || 'Nova notificação!'}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);

            $('body').append(toast);
            setTimeout(() => toast.remove(), 15000);

            const $count = $('#notificationCount');
            let current = parseInt($count.text()) || 0;
            $count.text(current + 1);

            new Audio("{{ asset('sounds/notification-sound.mp3')}}").play();
        });

    window.Echo.private('chat.' + {{ auth()->user()->id }})
        .listen('.mensagem.recebida_enviada', e => {
            const toast = $(`
                <div class="toast align-items-center text-white bg-dark border-0 show"
                     role="alert" aria-live="assertive" aria-atomic="true"
                     style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; min-width: 250px;">
                    <div class="d-flex">
                        <div class="toast-body">${e.senderName} te enviou uma mensagem!</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);

            $('body').append(toast);
            setTimeout(() => toast.remove(), 15000);

            new Audio("{{ asset('sounds/notification-sound.mp3')}}").play();

            let $badge = $('#mensagem-recebida-' + e.senderId);
            let count = parseInt($badge.text() || 0);
            $badge.text(count + 1);
            $badge.removeClass('d-none');
        });
    @endauth

    function adicionarComentario(comentario) {
        const foto = comentario.user_photo
            ? comentario.user_photo
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(comentario.usuario)}&background=random`;

        const vipBadge = comentario.is_vip ? '<span class="badge bg-warning text-dark ms-1"><i class="bi bi-star-fill"></i> VIP</span>' : '';

        return `
        <div class="d-flex align-items-start gap-2 mb-3 p-3 rounded-3 bg-white shadow-sm">
            <img src="${foto}"
                 alt="${comentario.usuario}"
                 class="rounded-circle shadow-sm"
                 width="48" height="48"
                 style="object-fit: cover;">
            <div>
                <div class="fw-bold abrir-perfil" data-username="${comentario.usuario}">${comentario.usuario}${vipBadge}</div>
                <div class="text-muted">${comentario.body}</div>
            </div>
        </div>`;
    }
</script>
