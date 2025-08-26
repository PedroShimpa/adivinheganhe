@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container ">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);">
                    <h1 class="fw-bold display-5">🏆 Você ganhou!</h1>
                    <p class="lead mt-3">Parabéns, novo milionário! 💰🎉</p>
                </div>

                <div class="card-body text-center p-5">
                    <div class="my-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                            alt="Milionário" width="140" class="mb-4">
                    </div>

                    <h2 class="fw-bold text-success mb-4">🎊 R$ 1.000.000,00 🎊</h2>

                    <p class="fs-5 mb-4">
                        Em breve nossa equipe entrará em contato para realizar a
                        <strong>auditoria e o pagamento do prêmio</strong>.
                        Mais uma vez, parabéns pela sua conquista!
                    </p>

                    <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                        <a href="{{ route('home') }}"
                            class="btn btn-lg btn-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🏠 Página Inicial
                        </a>

                        <a href="{{ route('suporte.index') }}"
                            class="btn btn-lg btn-success px-5 py-3 rounded-pill shadow fw-bold">
                            📞 Fale com a equipe
                        </a>
                    </div>
                </div>

                <div class="card-footer text-center p-4 bg-light">
                    <small class="text-muted">🌟 Aproveite este momento único, você é oficialmente um milionário! 🌟</small>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection