@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center bg-primary text-white p-4">
                    <h3 class="fw-bold">⚔️ Partida Competitiva</h3>
                    <p class="mb-0">Round atual: <span id="roundAtual">1</span></p>
                    <p class="mb-0">Tempo restante: <span id="tempoRestante">100</span> segundos</p>
                </div>

                <div class="card-body">
                    <div id="perguntaContainer" class="mb-4">
                        <h4 id="perguntaTexto"></h4>
                        <div id="perguntaFileContainer" class="mb-3"></div>
                    </div>

                    <div class="mb-3">
                        <input type="text" id="respostaInput" class="form-control" placeholder="Digite sua resposta...">
                    </div>

                    <button id="enviarRespostaBtn" class="btn btn-success resposta-btn">Enviar Resposta</button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js-scripts')
<script>
$(document).ready(function() {
    let partidaUuid = "{{ $partida->uuid }}";
    let roundAtual = {{ $partida->round_atual }};
    let tempoMax = Math.max(100 - (roundAtual - 1) * 10, 10);
    let tempoInterval;
    let perguntaAtualId = null;

    function carregarPergunta() {
        $.get(`/competitivo/partida/${partidaUuid}/pergunta`, function(pergunta) {
            perguntaAtualId = pergunta.id;
            $('#perguntaTexto').text(pergunta.pergunta);

            if (pergunta.file) {
                $('#perguntaFileContainer').html(
                    `<a href="${pergunta.file}" target="_blank" class="btn btn-outline-primary">Abrir Arquivo da Pergunta</a>`
                );
            } else {
                $('#perguntaFileContainer').html('');
            }

            $('#respostaInput').val('');
            $('.resposta-btn').prop('disabled', false).text('Enviar Resposta');

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
        });
});
</script>
@endpush
