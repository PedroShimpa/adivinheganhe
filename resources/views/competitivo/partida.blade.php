@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container-fluid px-2 py-4">

    {{-- Jogadores - Responsivo (em cima no celular, lateral no desktop) --}}
    <div class="row">
        <div class="col-12 col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 80px;">
                <div class="card-body p-3">
                    <h5 class="text-center fw-bold mb-3">👥 Jogadores</h5>
                    @foreach($partida->jogadores as $jogador)
                    <div class="d-flex justify-content-between py-1 px-2 bg-light rounded mb-2">
                        <span class="fw-semibold">{{ $jogador->user->username }}</span>
                        <span class="text-primary fw-bold">{{ $jogador->user->rank->elo ?? 0 }}</span>
                    </div>
                    @endforeach
                    <hr>
                    <p class="text-center fw-bold text-danger mb-0">⚔️ VS ⚔️</p>
                </div>
            </div>
        </div>

        {{-- Área da Partida --}}
        <div class="col-12 col-md-9">
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
                $('#perguntaTexto').text(pergunta.pergunta);

                if (pergunta.arquivo) {
                    $('#perguntaFileContainer').html(
                        `<img src="${pergunta.arquivo}" alt="Pergunta" class="img-fluid rounded" />`
                    );
                } else {
                    $('#perguntaFileContainer').html('');
                }

                $('#respostaInput').val('');
                $('.resposta-btn').prop('disabled', false).text('Enviar Resposta');

                new Audio("{{ asset('sounds/new_question.wav')}}").play();
                iniciarContador(tempoMax);
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
                roundAtual++;
                $('#roundAtual').text(roundAtual);
                tempoMax = Math.max(100 - (roundAtual - 1) * 10, 10);
                carregarPergunta();

            })
            .listen('.partida.finalizada', e => {
                window.location.href = `/competitivo/partida/finalizada/${partidaUuid}`;
                // tocar som partida finalizada
            });
    });
</script>
@endpush