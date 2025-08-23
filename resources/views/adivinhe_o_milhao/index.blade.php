@extends('layouts.app', ['enable_adsense' => true])

@section('content')

<div class="container py-5">
    @if(Auth::check())
    <div class="mb-4 p-4 card text-center animate__animated animate__fadeInUp">
        <h5 class="mb-3 ">
            🎯 Indique e ganhe 1 tentativa extra por usuário registrado em seu link</strong>
        </h5>

        <div class="input-group mb-3 mx-auto" style="max-width: 500px;">
            <input type="text" id="linkIndicacao" class="form-control rounded-start" value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
            <button class="btn btn-primary text-white" id="btnCopiarLink">Copiar link</button>
        </div>
    </div>
    @endif

    <div class="alert alert-success mt-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 shadow-sm border-start border-4 border-success rounded-4 animate__animated animate__fadeInUp">
        <div class="d-flex align-items-center gap-3 text-center text-md-start">
            <i class="bi bi-whatsapp fs-4 text-success"></i>
            <span class="fw-semibold">
                Entre na nossa <strong>comunidade do WhatsApp!</strong>
            </span>
        </div>
        <a href="{{ env('WHATSAPP_COMUNITY_URL') }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-4">
            Participar agora
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                    <h1 class="fw-bold display-5">🎉 Conheça o jogo <span class="text-warning">Adivinhe o Milhão</span> 🎉</h1>
                    <p class="lead mt-3">Mostre seu conhecimento, prove sua agilidade e concorra a <span class="fw-bold text-warning">R$ 1.000.000,00</span>!</p>
                </div>

                <div class="card-body p-5">
                    <h3 class="fw-bold mb-3">📜 Como funciona?</h3>
                    <p class="fs-5">Você terá <strong>10 minutos</strong> para acertar <strong>100 perguntas aleatórias seguidas</strong>.
                        Se conseguir, você recebe um PIX de <span class="fw-bold text-success">1 MILHÃO DE REAIS</span> na sua conta!</p>

                    <h3 class="fw-bold mt-4 mb-3">⚖️ Regras</h3>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">🤖 <strong>Uso de bots</strong> será automaticamente identificado → o jogador será permanentemente <span class="text-danger">banido</span>.</li>
                        <li class="list-group-item">🎁 Você tem <strong>1 tentativa gratuita</strong> por dia.</li>
                        <li class="list-group-item">⏱️ Após clicar em <em>Jogar</em>, o tempo começa a contar. <strong>Não é possível pausar</strong> o jogo.</li>
                        <li class="list-group-item">🔄 Sempre que reiniciar, você começará da <strong>pergunta 1</strong>.</li>
                        <li class="list-group-item">🔄 Você pode pesquisar onde achar melhor: Google, ChatGPT e afins</strong>.</li>
                        <li class="list-group-item">🔄 Uso de trapaças que facilitem a digitação ou IA diretamente na pagina resultaram em banição <span class="text-danger">Permanente</span></strong>.</li>
                        <li class="list-group-item">🔄 Caso alguém consiga acertar as 100 respostas, iremos fazer um processo meticuloso de auditoria para validação.</li>
                    </ul>

                    <div class="text-center mt-5">
                        @auth
                        <a href="{{ route('adivinhe_o_milhao.iniciar') }}"
                            class="btn btn-lg btn-success px-5 py-3 rounded-pill shadow fw-bold">
                            🚀 Jogar Agora
                        </a>
                        @else
                        <p class="text-muted mb-3">Você precisa estar logado para jogar.</p>
                        <a href="{{ route('login') }}"
                            class="btn btn-lg btn-primary px-4 py-2 rounded-pill shadow fw-bold">
                            🔑 Clique aqui para entrar
                        </a>
                        @endauth
                    </div>
                </div>

                <div class="card-footer text-center p-4 bg-light">
                    <small class="text-muted">⚡ Será que você consegue acertar todas? O milhão te espera! ⚡</small>
                </div>
            </div>

        </div>
    </div>
</div>

@if($recordista)
<div class="container mt-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate__animated animate__fadeInUp">
        <div class="card-body text-center p-5"
            style="background: linear-gradient(135deg, #ff512f, #dd2476); color: white;">

            <h2 class="fw-bold mb-4">
                <i class="fas fa-crown text-warning"></i> Recordista Atual
            </h2>

            <div class="d-flex flex-column align-items-center gap-3">
                <div class="bg-white text-dark fw-bold px-4 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-user"></i> {{ $recordista->username }}
                </div>

                <div class="display-6 fw-bold text-warning">
                    <i class="fas fa-star"></i> {{ $recordista->respostas_corretas }} respostas corretas
                </div>
            </div>

            <p class="mt-4 mb-0 fst-italic">
                Será que você consegue superar esse recorde? 🚀
            </p>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    $('#btnCopiarLink').on('click', function() {
        const $btn = $(this);
        const $input = $('#linkIndicacao');
        $input[0].select();
        $input[0].setSelectionRange(0, 99999);

        navigator.clipboard.writeText($input.val()).then(() => {
            $btn.text('Copiado!').removeClass('btn-outline-primary').addClass('btn-primary');
            setTimeout(() => {
                $btn.text('Copiar link').removeClass('btn-primary').addClass('btn-primary');
            }, 2000);
        }).catch(() => {
            Swal.fire('Erro', 'Não foi possível copiar o link. Por favor, tente manualmente.', 'error');
        });
    });
</script>
@endpush