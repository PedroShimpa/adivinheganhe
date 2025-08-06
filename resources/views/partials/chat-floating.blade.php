<div id="chatFloating" class="glass shadow-lg rounded-4 position-fixed d-flex flex-column"
    style="bottom: 1.5rem; right: 1.5rem; width: 320px; max-height: 450px; font-family: 'Orbitron', sans-serif; z-index: 1050;">

    <div id="chatHeader" class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
        style="user-select: none; cursor: pointer;">
        <h6 class="m-0 text-glow">Chat</h6>
        <button id="chatToggleBtn" class="btn btn-glow btn-sm btn-primary position-relative" style="line-height: 1;">
            <i class="bi bi-chat-dots"></i>
            <span id="chatNotification" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none">
                <span class="visually-hidden">Nova mensagem</span>
            </span>
        </button>

    </div>

    <div id="chatBody" class="flex-grow-1 overflow-auto px-3 pb-3 d-none"
        style="background: rgba(0, 0, 0, 0.25); border-radius: 0 0 1rem 1rem;">
        <div id="chatMessages" class="mb-3" style="max-height: 320px; overflow-y: auto;">
        </div>

        @auth
        <form id="chatForm" class="d-flex gap-2 px-3 pb-3" style="background: rgba(0, 0, 0, 0.25); border-radius: 0 0 1rem 1rem;">
            <input type="text" id="chatInput" class="form-control form-control-sm rounded-pill"
                placeholder="Digite sua mensagem..." autocomplete="off" />
            <button type="submit" class="btn btn-glow btn-primary btn-sm rounded-pill px-3 sendChat">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        @else
        <div class="alert alert-warning small rounded-3 mt-2 px-3">
            Você precisa estar logado para enviar mensagens
        </div>
        @endauth

    </div>
</div>

<style>
    #chatFloating {
        bottom: 1.5rem;
        right: 1.5rem;
        width: 320px;
        max-height: 450px;
        font-family: 'Orbitron', sans-serif;
        z-index: 1050;
        display: flex;
        flex-direction: column;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(8px);
        transition: width 0.3s ease, height 0.3s ease;
    }

    @media (max-width: 576px) {
        #chatFloating {
            width: calc(100vw - 2rem);
            max-height: 80vh;
            bottom: 1rem;
            right: 1rem;
            border-radius: 1rem;
            font-size: 0.9rem;
        }

        #chatBody {
            max-height: calc(80vh - 70px);
            padding: 0.5rem 1rem 1rem 1rem !important;
        }

        #chatInput {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .sendChat {
            padding: 0.4rem 1.2rem;
            font-size: 1.1rem;
        }

        #chatToggleBtn {
            font-size: 1.3rem;
            padding: 0.4rem 0.7rem;
        }
    }

    #chatHeader {
        user-select: none;
        cursor: pointer;
        padding: 0.8rem 1.2rem !important;
    }

    #chatMessages .message {
        padding: 0.4rem 0.75rem;
        max-width: 80%;
        font-size: 0.9rem;
    }

    #chatMessages .message.bot {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        align-self: flex-start;
    }
</style>
<script>
    $(function() {
        const $chatBody = $('#chatBody');
        const $chatToggleBtn = $('#chatToggleBtn');
        const $chatMessages = $('#chatMessages');
        const $chatForm = $('#chatForm');
        const $chatInput = $('#chatInput');
        const $sendBtn = $chatForm.find('button[type="submit"]');

        $chatToggleBtn.on('click', () => {
            $chatBody.toggleClass('d-none');
            const isVisible = !$chatBody.hasClass('d-none');
            $chatToggleBtn.html(isVisible ?
                '<i class="bi bi-x-lg"></i>' :
                '<i class="bi bi-chat-dots"></i><span id="chatNotification" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none"><span class="visually-hidden">Nova mensagem</span></span>'
            );

            if (isVisible) {
                $('#chatNotification').addClass('d-none');
            }
        });


        @auth
        async function enviarMensagem(texto) {
            if (!texto.trim()) return;

            $chatInput.prop('disabled', true);
            $sendBtn.prop('disabled', true);

            try {
                const res = await fetch('{{ route("chat.enviar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: texto
                    })
                });

                if (!res.ok) throw new Error('Falha no envio');

            } catch (err) {} finally {
                $chatInput.prop('disabled', false);
                $sendBtn.prop('disabled', false);
            }
        }

        $chatForm.on('submit', async function(e) {
            e.preventDefault();
            const texto = $chatInput.val().trim();
            if (!texto) return;
            await enviarMensagem(texto);
            $chatInput.val('').focus();
        });
        @endauth

        async function carregarMensagens() {
            const res = await fetch('{{ route("chat.buscar") }}');
            const mensagens = await res.json();

            if (Array.isArray(mensagens)) {
                mensagens.forEach(msg => {
                    adicionarMensagem(`${msg.user}: ${msg.message}`, 'message');

                    ;
                });

                $chatMessages.scrollTop($chatMessages[0].scrollHeight);
            }
        }

        carregarMensagens();

    });
</script>