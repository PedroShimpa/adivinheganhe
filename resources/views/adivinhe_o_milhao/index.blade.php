@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container py-5">
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
@endsection
