@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container mb-5 mt-2">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center text-white p-5"
                    style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                    <h1 class="fw-bold display-5">🏁 Partida Finalizada</h1>
                    <p class="lead mt-3">Confira os resultados da sua partida competitiva!</p>
                </div>

                <div class="card-body p-5">
                    <h3 class="fw-bold mb-3">🎯 Resultado</h3>

                    <div class="text-center mb-4">
                        @if($partida->jogadores->where('user_id', auth()->id)->value('vencedor') == auth()->id())
                            <h2 class="text-success fw-bold">🎉 Você venceu!</h2>
                        @else
                            <h2 class="text-danger fw-bold">😞 Você perdeu!</h2>
                        @endif
                    </div>

                    <h4 class="fw-bold mt-4 mb-3">👥 Jogadores</h4>
                    <ul class="list-group list-group-flush mb-4">
                        @foreach($partida->jogadores as $jogador)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $jogador->user->name }}
                                @if($partida->vencedor )
                                    <span class="badge bg-success rounded-pill">Vencedor</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Perdedor</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3">📊 Estatísticas</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">Tempo de partida: <strong>{{ gmdate("i:s", $partida->duracao) }}</strong></li>
                        <li class="list-group-item">Rodadas jogadas: <strong>{{ $partida->rodada_atual }}</strong></li>
                        <li class="list-group-item">Rating ganho/perdido: <strong>{{ $partida->elo_diff }}</strong></li>
                    </ul>

                    <div class="text-center mt-4">
                        <a href="{{ route('competitivo.index') }}" 
                           class="btn btn-lg btn-primary px-5 py-3 rounded-pill shadow fw-bold">
                            🔙 Voltar para o Modo Competitivo
                        </a>
                        <a href="{{ route('competitivo.buscar_partida') }}" 
                           class="btn btn-lg btn-success px-5 py-3 rounded-pill shadow fw-bold ms-2">
                            ⚡ Jogar Novamente
                        </a>
                    </div>
                </div>

                <div class="card-footer text-center p-4 bg-light">
                    <small class="text-muted">🏆 Continue evoluindo seu rating e se torne o melhor jogador!</small>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
