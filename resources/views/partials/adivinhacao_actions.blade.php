<p class="mb-1 small text-dark"><strong>Código:</strong> {{ $adivinhacao->uuid }}</p>

@auth
<div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
    <button type="button" class="btn btn-gradient-primary btn-sm rounded-pill verRespostas" adivinhacao_id="{{ $adivinhacao->id}}" data-bs-toggle="modal" data-bs-target="#modalSuasRespostas">
        📜 Seus Palpites
    </button>
    <button class="btn btn-gradient-info btn-sm mb-3 rounded-pill abrirModalInformacoes" data-bs-toggle="modal" data-bs-target="#modalInformacoes"
            titulo="{{ $adivinhacao->titulo. (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired ? ' - EXPIRADA' : '') }}"
            descricao="{{ $adivinhacao->descricao}}">
        ➕ Informações
    </button>
</div>

@if($adivinhacao->limitExceded)
    <div class="alert alert-warning small py-2 px-3 rounded-pill">⚠️ Você atingiu o limite de palpites dessa adivinhação para hoje!</div>
@else
    @if($adivinhacao->resolvida != 'S')
    @if($adivinhacao->only_members == 1)
        @if(auth()->user()->isVip())
            <div class="alert alert-success small py-2 px-3 rounded-pill mb-3">
                Palpites restantes: <span id="palpites_adivinhacao_{{ $adivinhacao->id}}">{{ $adivinhacao->palpites_restantes }}</span>
            </div>

            {{-- Campo de palpite moderno --}}
            <div class="input-group mb-3 shadow-sm rounded-pill" style="overflow: hidden; border: 2px solid #0d6efd;">
                <input type="text"
                    id="resposta-{{ $adivinhacao->id }}"
                    name="resposta"
                    class="form-control form-control-lg border-0 fw-bold ps-3"
                    placeholder="💬 Digite seu palpite aqui..."
                    style="font-size: 1.25rem; height: 60px;"
                >
                <button class="btn btn-gradient-primary d-flex align-items-center justify-content-center fw-bold sendResposta"
                        id="btn-resposta-{{ $adivinhacao->id }}"
                        style="min-width: 140px; font-size: 1.1rem;"
                        @if($adivinhacao->expired && !empty($adivinhacao->expired_at_br)) disabled @endif>
                    <i class="bi bi-send me-2"></i> Enviar
                </button>
            </div>
            <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
        @else
            <div class="alert alert-warning small py-2 px-3 rounded-pill mb-3">
                <i class="bi bi-star-fill text-warning"></i> Apenas membros VIP podem responder a esta adivinhação.
                <a href="{{ route('seja_membro') }}" class="btn btn-sm btn-primary ms-2">Seja VIP</a>
            </div>
        @endif

        @else

   <div class="alert alert-success small py-2 px-3 rounded-pill mb-3">
                Palpites restantes: <span id="palpites_adivinhacao_{{ $adivinhacao->id}}">{{ $adivinhacao->palpites_restantes }}</span>
            </div>

            {{-- Campo de palpite moderno --}}
            <div class="input-group mb-3 shadow-sm rounded-pill" style="overflow: hidden; border: 2px solid #0d6efd;">
                <input type="text"
                    id="resposta-{{ $adivinhacao->id }}"
                    name="resposta"
                    class="form-control form-control-lg border-0 fw-bold ps-3"
                    placeholder="💬 Digite seu palpite aqui..."
                    style="font-size: 1.25rem; height: 60px;"
                >
                <button class="btn btn-gradient-primary d-flex align-items-center justify-content-center fw-bold sendResposta"
                        id="btn-resposta-{{ $adivinhacao->id }}"
                        style="min-width: 140px; font-size: 1.1rem;"
                        @if($adivinhacao->expired && !empty($adivinhacao->expired_at_br)) disabled @endif>
                    <i class="bi bi-send me-2"></i> Enviar
                </button>
            </div>
            <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">


        @endif
    @else
        <div class="alert alert-warning small rounded-3 mt-2">
            Esta adivinhação já foi resolvida
        </div>
    @endif
@endif
@else
<div class="alert alert-warning small rounded-3 mt-2">
    Você precisa <a href="{{ route('login') }}" class="text-decoration-underline fw-semibold">entrar</a> para responder. É <span class="text-primary fw-semibold">grátis</span>!
</div>
@endauth
