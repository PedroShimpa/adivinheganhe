@extends('layouts.app', ['enable_adsense' => true])

@section('content')

<div class="container mb-5 mt-2">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <h1 class="fw-bold display-5">⚔️ Modo Competitivo ⚔️</h1>
                    <p class="lead mt-3">Enfrente outros jogadores, responda rápido e prove que você é o melhor!</p>
                </div>


                <div class="card-body ">
                    <div class="d-flex flex-column align-items-center justify-content-center mt-5 mb-5">
                        {{-- Card do rating --}}
                        <div class="card shadow-lg rounded-4 p-4 mb-4 text-center" style="max-width: 400px;">
                            <h4 class="fw-bold mb-2">🏆 Seu rating atual</h4>
                            <div class="display-5 fw-bold text-primary">
                                {{ auth()->check() ? auth()->user()->getOrCreateRank()->elo : 0 }}
                            </div>
                        </div>

                        @auth
                        <button
                            class="btn btn-danger btn-lg px-5 py-3 rounded-pill shadow fw-bold buscar-partida">
                            ⚡ Buscar Partida
                        </button>
                        @else
                        <p class="text-muted mb-3 text-center">
                            Você precisa estar logado para jogar no modo competitivo.
                        </p>
                        <a href="{{ route('login') }}"
                            class="btn btn-primary btn-lg px-4 py-2 rounded-pill shadow fw-bold">
                            🔑 Clique aqui para entrar
                        </a>
                        @endauth
                    </div>


                    <h3 class="fw-bold mb-3">📜 Como funciona?</h3>
                    <p class="fs-5">
                        No modo competitivo, você será colocado contra outro jogador em tempo real.
                        Cada rodada tem <strong>um tempo limite para responder</strong> e a dificuldade das perguntas aumenta progressivamente.
                        Quem errar primeiro, perde!
                    </p>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">⏱️ Começamos com <strong>1:40 </strong> na primeira pergunta, e a cada rodada o tempo diminui em 10 segundos.</li>
                        <li class="list-group-item">💡 As perguntas vão de dificuldade <strong>1 até 10</strong>.</li>
                        <li class="list-group-item">📈 Seus resultados influenciam seu <strong>rating competitivo</strong>, aproximando você de jogadores com nível parecido.</li>
                        <li class="list-group-item">🔄 Partidas são rápidas e cada rodada exige atenção e agilidade!</li>
                        <li class="list-group-item">🎯 Responder fora do tempo ou errar elimina você da rodada.</li>

                    </ul>

                    <div class="text-center mt-5">
                        @auth
                        <button

                            class="btn btn-lg btn-danger px-5 py-3 rounded-pill shadow fw-bold buscar-partida">
                            ⚡ Buscar Partida
                        </button>
                        @else
                        <p class="text-muted mb-3">Você precisa estar logado para jogar no modo competitivo.</p>
                        <a href="{{ route('login') }}"
                            class="btn btn-lg btn-primary px-4 py-2 rounded-pill shadow fw-bold">
                            🔑 Clique aqui para entrar
                        </a>
                        @endauth
                    </div>
                </div>

                <div class="card-footer text-center p-4 bg-light">
                    <small class="text-muted">🔥 Mostre que você é o mais rápido e inteligente! Seu rating está em jogo! 🔥</small>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {

        @auth
        $('.buscar-partida').on('click', function(e) {
            let segundos = 0;
            let interval;
            e.preventDefault();

            Swal.fire({
                title: '🔎 Buscando partida...',
                html: `<p>Tempo de busca: <strong id="tempoBusca">0</strong> segundos</p>
                   <button id="cancelarBusca" class="swal2-confirm swal2-styled" style="background:#dc3545;">Parar Busca</button>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    const tempoEl = Swal.getHtmlContainer().querySelector('#tempoBusca');
                    const cancelarBtn = Swal.getHtmlContainer().querySelector('#cancelarBusca');

                    interval = setInterval(() => {
                        segundos++;
                        tempoEl.textContent = segundos;
                    }, 1000);

                    $(cancelarBtn).on('click', function() {
                        clearInterval(interval);
                        Swal.close();

                        $.post("{{ route('competitivo.cancelar_busca') }}", {
                                _token: '{{ csrf_token() }}'
                            }).done(() => console.log('Saiu da fila.'))
                            .fail(() => console.log('Erro ao sair da fila.'));

                    });

                       $.post("{{ route('competitivo.iniciar_busca') }}", {
                        _token: '{{ csrf_token() }}'
                    }).done(() => console.log('Busca iniciada no backend'))
                    .fail(() => console.log('Erro ao iniciar busca'));

                }
            });

        });
        window.Echo.channel('competitivo')
                .listen('.partida.encontrada', e => {
                    Swal.close();
                    if (e.user_id1 == "{{auth()->id()}}" || e.user_id2 == "{{auth()->id()}}")
                        window.location.href = "competitivo/partida/" + e.uuid;
            });

        @endauth

    });
</script>
@endpush