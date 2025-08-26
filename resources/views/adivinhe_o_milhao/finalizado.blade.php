@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container ">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <h1 class="fw-bold display-5">⏰ Tempo Esgotado!</h1>
                    <p class="lead mt-3">Infelizmente o tempo acabou, mas não desanime! 🎮</p>
                </div>

                <div class="card-body text-center p-5">
                    <h3 class="fw-bold mb-4">Obrigado por jogar o <span class="text-primary">Adivinhe o Milhão</span> 💰</h3>
                    <p class="fs-5 mb-4">
                        Você poderá tentar novamente <strong>amanhã</strong>.
                        Lembre-se: cada dia traz uma nova chance de conquistar o prêmio de
                        <span class="fw-bold text-success">1 MILHÃO DE REAIS</span>!
                    </p>

                    <div class="my-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/1243/1243486.png"
                            alt="Tempo Esgotado" width="120" class="mb-3">
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="{{ route('home') }}"
                            class="btn btn-lg btn-outline-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🏠 Voltar para a Página Inicial
                        </a>
          
                    </div>
                </div>

                <div class="card-footer text-center p-4 bg-light">
                    <small class="text-muted">💡 Dica: Estude, prepare-se e volte amanhã para tentar de novo. Quem sabe você não leva o milhão? 😉</small>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection