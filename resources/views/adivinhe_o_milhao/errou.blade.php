@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <h1 class="fw-bold display-5">❌ Você errou!</h1>
                    <p class="lead mt-3">Dessa vez não deu, mas não desista — volte mais forte! 💪</p>
                </div>

                <div class="card-body text-center p-5">

                    @isset($pergunta)
                    <h5 class="fw-bold mb-2">Pergunta</h5>
                    <p class="fs-5 mb-4">
                        {{ is_array($pergunta) ? ($pergunta['descricao'] ?? '') : ($pergunta->descricao ?? '') }}
                    </p>
                    @endisset

                    @isset($resposta_correta)
                    <div class="alert alert-success rounded-pill fw-semibold mx-auto" style="max-width: 640px;">
                        ✅ Resposta correta: <span class="ms-1">{{ $resposta_correta }}</span>
                    </div>
                    @endisset

                    <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                        <a href="{{ route('home') }}"
                            class="btn btn-lg btn-outline-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🏠 Voltar para a Página Inicial
                        </a>

                        @auth
                        <a href="{{ route('jogo.iniciar') }}"
                            class="btn btn-lg btn-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🔁 Tentar Novamente
                        </a>
                        @else
                        <div class="w-100 text-muted">Você precisa estar logado para jogar.</div>
                        <a href="{{ route('login') }}"
                            class="btn btn-lg btn-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🔑 Entrar
                        </a>
                        @endauth
                    </div>

                </div>

                <div class="card-footer text-center p-4 bg-light">
                    <small class="text-muted">📅 Lembrete: há 1 tentativa gratuita por dia.</small>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection