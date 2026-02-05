{{-- Botão de edição para admins --}}
@if(Auth::check() && auth()->user()->isAdmin())
    <a href="{{ route('adivinhacoes.view', $adivinhacao->uuid)}}" class="btn btn-warning mb-3">Editar</a>
@endif

{{-- Título --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="fw-bold mb-0">
        {{ $adivinhacao->titulo. (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired ? ' - EXPIRADA' : '') }}
    </h5>
    @if($adivinhacao->only_members == 1)
        <span class="badge bg-warning text-dark">
            <i class="bi bi-star-fill"></i> VIP
        </span>
    @endif
</div>

{{-- Formato da resposta --}}
@if(!empty($adivinhacao->formato_resposta))
    <p class="text-info small mb-2">
        Formato da resposta: <strong>{{ $adivinhacao->formato_resposta }}</strong>
    </p>
@endif

{{-- Dificuldade --}}
@if(!empty($adivinhacao->dificuldade))
    <p class="text-warning small mb-2">
        Dificuldade: <strong>{{ $adivinhacao->dificuldade }}</strong>
    </p>
@endif

{{-- Respostas até agora --}}
<p class="text-primary small mb-2">
    Respostas até agora: <strong>{{ $adivinhacao->respostas->count() }}</strong>
</p>

{{-- Data de expiração --}}
@if(!empty($adivinhacao->expired_at_br))
    <p class="text-info small mb-2">
        Expira em <strong>{{ $adivinhacao->expired_at_br }}</strong>
    </p>
@else
    <p class="text-info small mb-2">Esta adivinhação não expira.</p>
@endif
