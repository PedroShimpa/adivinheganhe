@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-2 text-center">Escolha a região</h2>
    <p class="text-center mb-5">Cada região tem adivinhações exclusivas, o frete só é pago dentro da região e alguns prêmios só podem ser enviados nela!</p>

    <div class="row justify-content-center">
        @foreach ($regioes as $regiao)
            <div class="col-md-4 col-lg-3 mb-4">
                <a href="{{ route('adivinhacoes.buscar_por_regiao', $regiao->slug_url) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 rounded-4 text-center py-4 px-2 hover-shadow" style="transition: all 0.3s ease;">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold">{{ $regiao->nome }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
