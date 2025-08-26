@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container ">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-6">🎁 Premiações</h2>
        <p class="text-white">Confira os prêmios conquistados pelos jogadores</p>
    </div>

    <div class="row g-4">
        @foreach($premios as $premio)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-4">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-trophy fa-2x text-warning me-3"></i>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $premio->titulo }}</h5>
                                <small class="text-muted">por {{ $premio->username }}</small>
                            </div>
                        </div>

                        {{-- Conteúdo do prêmio --}}
                        <div class="mb-3 flex-grow-1">
                            @if(filter_var($premio->premio, FILTER_VALIDATE_URL))
                                {{-- Se for uma URL de imagem --}}
                                @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $premio->premio))
                                    <a href="{{ $premio->premio }}" target="_blank">
                                        <img src="{{ $premio->premio }}" alt="Prêmio" class="img-fluid rounded shadow-sm">
                                    </a>
                                {{-- Se for apenas um link comum --}}
                                @else
                                    <a href="{{ $premio->premio }}" target="_blank" class="btn btn-outline-primary w-100">
                                        🔗 Ver prêmio
                                    </a>
                                @endif
                            @else
                                {{-- Se for apenas texto --}}
                                <p class="text-muted fst-italic">
                                    {{ $premio->premio }}
                                </p>
                            @endif
                        </div>

                        {{-- Resposta usada para ganhar --}}
                        <div class="border-top pt-2">
                            <small class="text-muted">
                                Resposta correta: <span class="fw-semibold text-success">{{ $premio->resposta }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
