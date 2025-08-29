@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container mb-5 mt-2">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-6">🏆 Hall da Fama</h2>
        <p class="text-white">Os jogadores mais premiados do Adivinhe e Ganhe</p>
    </div>

    <div class="row g-4">
        @foreach($usuariosComMaisPremios as $index => $usuario)
            <div class="col-md-4 col-lg-3">
                <div class="card shadow-sm border-0 h-100 text-center position-relative rounded-4">
                    @if($index < 3)
                        <span class="position-absolute top-0 start-50 translate-middle badge 
                            {{ $index == 0 ? 'bg-warning text-dark' : ($index == 1 ? 'bg-secondary' : 'bg-primary') }} 
                            fs-6 px-3 py-2 rounded-pill shadow">
                            {{ $index + 1 }}º
                        </span>
                    @else
                        <span class="position-absolute top-0 start-50 translate-middle badge bg-light text-dark border fs-6 px-3 py-2 rounded-pill shadow">
                            {{ $index + 1 }}º
                        </span>
                    @endif

                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title mb-1">{{ $usuario->username }}</h5>
                        <p class="text-muted mb-3">Jogador</p>
                        <h6 class="fw-bold text-success">
                            🏅 {{ $usuario->count_premiacoes }} prêmios
                        </h6>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
