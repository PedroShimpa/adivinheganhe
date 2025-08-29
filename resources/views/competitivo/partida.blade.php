@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container-fluid px-2 py-4">

    {{-- Cabeçalho com jogadores em destaque --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center justify-content-center text-center">

                @php
                $jogadores = $partida->jogadores;
                $left = $jogadores[0] ?? null;
                $right = $jogadores[1] ?? null;
                @endphp

                {{-- Jogador da esquerda --}}
                <div class="col-5 d-flex flex-column align-items-center">
                    <img src="{{ $left?->user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($left?->user->username ?? 'Jogador').'&background=random' }}"
                        class="rounded-circle border border-3 border-primary shadow-lg mb-3"
                        width="100" height="100" style="object-fit: cover;">
                    <h5 class="fw-bold mb-1">{{ $left?->user->username ?? 'Jogador 1' }}</h5>
                    <span class="text-primary fw-bold">Rating {{ $left?->user->rank->elo ?? 0 }}</span>
                </div>

                {{-- VS no meio --}}
                <div class="col-2">
                    <h3 class="fw-bold text-danger">⚔️</h3>
                    <h5 class="fw-bold">VS</h5>
                </div>

                {{-- Jogador da direita --}}
                <div class="col-5 d-flex flex-column align-items-center">
                    <img src="{{ $right?->user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($right?->user->username ?? 'Jogador').'&background=random' }}"
                        class="rounded-circle border border-3 border-danger shadow-lg mb-3"
                        width="100" height="100" style="object-fit: cover;">
                    <h5 class="fw-bold mb-1">{{ $right?->user->username ?? 'Jogador 2' }}</h5>
                    <span class="text-danger fw-bold">Rating {{ $right?->user->rank->elo ?? 0 }}</span>
                </div>

            </div>
        </div>
    </div>

    {{-- Área da Partida --}}
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div class="card-header text-center bg-gradient bg-primary text-white p-4">
            <h3 class="fw-bold mb-2">⚔️ Partida Competitiva</h3>
            <div class="d-flex justify-content-center gap-4">
                <p class="mb-0">📍 Round: <span id="roundAtual" class="fw-bold">{{ $partida->round_atual }}</span></p>
                <p class="mb-0">⏳ Tempo: <span id="tempoRestante" class="fw-bold">100</span>s</p>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- Pergunta --}}
            <div id="perguntaContainer" class="mb-4 text-center">
                <h4 id="perguntaTexto" class="fw-semibold mb-3"></h4>
                <div id="perguntaFileContainer" class="mb-3"></div>
            </div>

            {{-- Resposta --}}
            <div class="input-group mb-3">
                <input type="text" id="respostaInput"
                    class="form-control form-control-lg"
                    placeholder="Digite sua resposta...">
                <button id="enviarRespostaBtn"
                    class="btn btn-success btn-lg px-4 resposta-btn">
                    Enviar
                </button>
            </div>

            <small class="text-muted d-block text-center">Responda antes do tempo acabar ⏱️</small>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
    $(document).ready(function() {
        let partidaUuid = "{{ $partida->uuid }}";
        let roundAtual = "{{$partida->round_atual}}";
        let tempoMax = Math.max(100 - (roundAtual - 1) * 10, 10);
        let tempoInterval;
        let perguntaAtualId = null;

        function carregarPergunta() {
            $.get(`/competitivo/partida/${partidaUuid}/pergunta`, function(pergunta) {
                perguntaAtualId = pergunta.id;

                // Atualiza o texto da pergunta
                $('#perguntaTexto').text(pergunta.pergunta);

                // Exibe arquivo se houver
                if (pergunta.arquivo) {
                    $('#perguntaFileContainer').html(
                        `<img src="${pergunta.arquivo}" alt="Pergunta" class="img-fluid rounded" />`
                    );
                } else {
                    $('#perguntaFileContainer').html('');
                }

                // Limpa input e habilita botão
                $('#respostaInput').val('');
                $('.resposta-btn').prop('disabled', false).text('Enviar Resposta');

                // Toca som da nova pergunta
                new Audio("{{ asset('sounds/new_question.wav')}}").play();

                // Inicia contador baseado no round_started_at
                if (pergunta.round_started_at) {
                    const startedAt = new Date(pergunta.round_started_at); // assume UTC
                    const now = new Date();
                    let segundos = Math.max(100 - (roundAtual - 1) * 10, 10); // default

                    // Calcula segundos restantes
                    const diff = Math.floor((segundos * 1000 - (now - startedAt)) / 1000);
                    iniciarContador(diff > 0 ? diff : 0);
                }
            });
        }

        function iniciarContador(segundos) {
            clearInterval(tempoInterval);
            $('#tempoRestante').text(segundos);
            tempoInterval = setInterval(() => {
                segundos--;
                $('#tempoRestante').text(segundos);
                if (segundos <= 0) {
                    clearInterval(tempoInterval);
                    $('.resposta-btn').prop('disabled', true).text('Tempo esgotado');
                }
            }, 1000);
        }

        function enviarResposta(perguntaId, resposta) {
            $('.resposta-btn').prop('disabled', true).text('Resposta enviada');

            $.post(`/competitivo/partida/${partidaUuid}/${perguntaId}/responder`, {
                resposta: resposta,
                _token: '{{ csrf_token() }}'
            }, function() {
                $('.resposta-btn').prop('disabled', true).text('Resposta enviada');

                if (!$('#aguardeOponente').length) {
                    $('#perguntaContainer').append(
                        `<p id="aguardeOponente" class="text-center text-warning fw-bold mt-3">
                    ⏳ Aguarde o oponente responder para prosseguir...
                </p>`
                    );
                }
            });
        }
        $('#enviarRespostaBtn').click(function() {
            let resposta = $('#respostaInput').val().trim();
            if (!resposta) return alert('Digite uma resposta antes de enviar!');
            enviarResposta(perguntaAtualId, resposta);
        });

        carregarPergunta();

        window.Echo.private('competitivo.partida.' + partidaUuid)
            .listen('.nova.pergunta', e => {

                const toastContainer = document.getElementById('toastContainer') || (() => {
                    const container = document.createElement('div');
                    container.id = 'toastContainer';
                    container.style.position = 'fixed';
                    container.style.top = '1rem';
                    container.style.right = '1rem';
                    container.style.zIndex = 1080;
                    document.body.appendChild(container);
                    return container;
                })();

                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-bg-success border-0';
                toastEl.role = 'alert';
                toastEl.ariaLive = 'assertive';
                toastEl.ariaAtomic = 'true';

                toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    Todos responderam corretamente! Próxima pergunta...
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

                toastContainer.appendChild(toastEl);

                const bsToast = new bootstrap.Toast(toastEl, {
                    delay: 3000,
                    autohide: true
                });
                bsToast.show();

                roundAtual++;
                $('#roundAtual').text(roundAtual);
                tempoMax = Math.max(100 - (roundAtual - 1) * 10, 10);

                carregarPergunta();

            })
            .listen('.partida.finalizada', e => {
                window.location.href = `/competitivo/partida/finalizada/${partidaUuid}`;

            });

        const respostaInput = document.getElementById('respostaInput');
        if (respostaInput) {
            respostaInput.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') {
                    e.preventDefault();
                    alert('Colar não é permitido!');
                }
            });
            respostaInput.addEventListener('paste', function(e) {
                e.preventDefault();
                alert('Colar não é permitido!');
            });
            respostaInput.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                alert('Colar não é permitido!');
            });
        }
    });

</script>
@endpush