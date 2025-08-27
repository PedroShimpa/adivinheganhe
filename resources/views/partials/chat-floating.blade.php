<div id="chatFloating" class="glass shadow-lg rounded-4 position-fixed d-flex flex-column"
    style="bottom: 1.5rem; right: 1.5rem; width: 320px; max-height: 450px; font-family: 'Orbitron', sans-serif; z-index: 1050;">

    <div id="chatHeader" class="d-flex justify-content-between align-items-center p-3">
        <h6 class="m-0">Chat</h6>
        <button id="chatToggleBtn" class="btn btn-glow btn-sm btn-primary position-relative" style="line-height: 1;">
            <i class="bi bi-chat-dots"></i>
            <span id="chatNotification" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none">
                <span class="visually-hidden">Nova mensagem</span>
            </span>
        </button>
    </div>

    <div id="chatBody" class="flex-grow-1 d-flex flex-column px-3 pb-3"
        style="background: rgba(0, 0, 0, 0.25); border-radius: 0 0 1rem 1rem; overflow: hidden;">
        <div id="chatMessages" class="flex-grow-1 overflow-auto mb-3 d-flex flex-column" style="gap: 0.25rem;"></div>

        @auth
        <form id="chatForm" class="d-flex gap-2 pb-3" style="background: rgba(0, 0, 0, 0.25); border-radius: 0 0 1rem 1rem;">
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
        display: flex;
        flex-direction: column;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(8px);
        overflow-x: none;

        transition: width 0.3s ease, height 0.3s ease;
    }

    #chatHeader {
        user-select: none;
        cursor: pointer;
        padding: 0.8rem 1.2rem;
    }

    #chatMessages {
        display: flex;
        flex-direction: column;
        overflow-x: none;

        overflow-y: auto;
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

    @media (max-width: 576px) {
        #chatFloating {
            width: calc(100vw - 2rem);
            max-height: 80vh;
            bottom: 1rem;
            right: 1rem;
            overflow-x: none;

            border-radius: 1rem;
            font-size: 0.9rem;
        }

        #chatBody {
            max-height: calc(80vh - 70px);
            overflow-x: none;
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
</style>

<script>
    $(function() {
        const $chatBody = $('#chatBody');
        const $chatToggleBtn = $('#chatToggleBtn');
        const $chatMessages = $('#chatMessages');
        const $chatForm = $('#chatForm');
        const $chatInput = $('#chatInput');
        const $sendBtn = $chatForm.find('button[type="submit"]');

        const savedState = localStorage.getItem('chatState');
        if (savedState === 'open') {
            $chatBody.removeClass('d-none');
            $chatToggleBtn.html('<i class="bi bi-x-lg"></i>');
        } else {
            $chatBody.addClass('d-none');
            $chatToggleBtn.html('<i class="bi bi-chat-dots"></i><span id="chatNotification" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none"><span class="visually-hidden">Nova mensagem</span></span>');
        }

        $chatToggleBtn.on('click', () => {
            $chatBody.toggleClass('d-none');
            const isVisible = !$chatBody.hasClass('d-none');
            $chatToggleBtn.html(isVisible ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-chat-dots"></i><span id="chatNotification" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none"><span class="visually-hidden">Nova mensagem</span></span>');
            if (isVisible) {
                $('#chatNotification').addClass('d-none');
                localStorage.setItem('chatState', 'open');
            } else {
                localStorage.setItem('chatState', 'closed');
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
                    const div = $('<div>').addClass('message').text(`${msg.usuario}: ${msg.mensagem}`);
                    $chatMessages.append(div);
                });
                $chatMessages.scrollTop($chatMessages[0].scrollHeight);
            }
        }
        carregarMensagens();
    });
</script>