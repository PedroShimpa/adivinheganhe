@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container mb-5 mt-2">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <h1 class="fw-bold display-5">❌ Você errou!</h1>
                    <p class="lead mt-3">Dessa vez não deu, mas não desista — volte mais forte! 💪</p>
                </div>

                <div class="card-body text-center p-5">

                    <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                        <a href="{{ route('home') }}"
                            class="btn btn-lg btn-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🏠 Voltar para a Página Inicial
                        </a>
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