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

                    @php
                        $vencedor = $partida->jogadores->where('vencedor', 1)->first();
                    @endphp
                    @if($vencedor)
                        <h2 class="fw-bold mt-3">🏆 Vencedor: {{ $vencedor->user->username }}</h2>
                    @endif
                </div>

                <div class="card-body p-5">
                    <h4 class="fw-bold mt-4 mb-3">👥 Jogadores</h4>
                    <ul class="list-group list-group-flush mb-4">
                        @foreach($partida->jogadores as $jogador)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $jogador->user->username }}
                            @if($jogador->vencedor)
                                <span class="badge bg-success rounded-pill">Vencedor</span>
                            @else
                                <span class="badge bg-danger rounded-pill">Perdedor</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3">📊 Estatísticas</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">Rodadas jogadas: <strong>{{ $partida->round_atual }}</strong></li>
                    </ul>

                    <div class="mt-5">
                        @php
                        $respostasPorPergunta = $partida->respostas->groupBy('pergunta_id');
                        @endphp

                        @foreach($respostasPorPergunta as $perguntaId => $respostas)
                        <div class="card mb-3 shadow-sm rounded-4">
                            <div class="card-header bg-primary text-white">
                                {{ $respostas->first()->pergunta->pergunta ?? 'Pergunta' }} - Resposta: {{ $respostas->first()->pergunta->resposta }}
                            </div>
                            <div class="card-body">
                                @foreach($respostas as $resposta)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded 
                                    {{ $resposta->correta ? 'bg-success text-white' : 'bg-light text-dark' }}">

                                    <div class="d-flex align-items-center">
                                        <img src="{{ $resposta->user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($resposta->user->username).'&background=random' }}"
                                            width="40" height="40" class="rounded-circle me-2" style="object-fit: cover;">
                                        <span class="fw-bold">{{ $resposta->user->username }}</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold">{{ $resposta->resposta }}</span>
                                        @if($resposta->correta)
                                            <i class="bi bi-check-circle text-white"></i>
                                        @else
                                            <i class="bi bi-x-circle text-danger"></i>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="text-center mt-4">
                            <a href="{{ route('competitivo.index') }}"
                                class="btn btn-lg btn-primary px-5 py-3 rounded-pill shadow fw-bold">
                                🔙 Voltar para o Modo Competitivo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('tocar_fim')) {
            let audio = new Audio("{{ asset('sounds/fim_comp.wav') }}");
            audio.volume = 0.3;
            audio.play();
        }
    });
</script>
@endpush
