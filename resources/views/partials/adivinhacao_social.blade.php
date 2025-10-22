{{-- Compartilhar, likes e comentários --}}
<div class="mt-3">
    <div class="mt-2 d-flex gap-2 flex-wrap">
        <span class="fw-semibold small">Compartilhar:</span>
        <a href="https://api.whatsapp.com/send?text={{ urlencode($adivinhacao->titulo . ' ' . route('adivinhacoes.index', $adivinhacao->uuid)) }}" target="_blank" class="btn btn-sm btn-success rounded-pill">
            WhatsApp
        </a>
    </div>

    <div class="d-flex align-items-center gap-2 mt-1 mb-2">
        @php
            $userLiked = auth()->check() && $adivinhacao->likes->isNotEmpty();
            $likesCount = $adivinhacao->likes_count;
        @endphp

        @auth
            <button class="btn btn-sm rounded-pill btn-like {{ $userLiked ? 'btn-danger' : 'btn-outline-primary' }}" data-id="{{ $adivinhacao->uuid }}">
                <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                <span class="likes-count">{{ $likesCount }}</span>
            </button>
        @else
            <div class="btn btn-sm btn-outline-secondary rounded-pill disabled">
                <i class="bi bi-hand-thumbs-up"></i>
                <span class="likes-count">{{ $likesCount }}</span>
            </div>
        @endauth
    </div>

    <button class="btn btn-secondary btn-sm rounded-pill verComentarios" data-id="{{ $adivinhacao->id }}" data-route="{{ route('adivinhacoes.comments', $adivinhacao->uuid) }}">
        💬 Comentários
    </button>

    <div id="comentarios-{{ $adivinhacao->id }}" class="comentarios-box d-none mt-3 p-3 rounded-4 bg-light shadow-sm">
        <div class="comentarios-list small mb-3 text-dark"></div>
        <div class="text-center">
            <button class="btn btn-outline-primary btn-sm rounded-pill load-more-comments d-none" data-id="{{ $adivinhacao->id }}" data-route="{{ route('adivinhacoes.comments', $adivinhacao->uuid) }}" data-offset="5">
                Ver Mais Comentários
            </button>
        </div>

        @auth
            <div class="input-group">
                <input type="text" id="comentario-input-{{ $adivinhacao->id }}" class="form-control rounded-start-pill" placeholder="💬 Escreva um comentário..." maxlength="250">
                <button class="btn btn-primary rounded-end-pill sendComment" data-id="{{ $adivinhacao->id }}" data-route="{{ route('adivinhacoes.comment', $adivinhacao->uuid) }}">
                    Enviar
                </button>
            </div>
        @else
            <div class="alert alert-warning small rounded-3 mt-2">
                Você precisa <a href="{{ route('login') }}" class="fw-semibold text-decoration-underline">entrar</a> para comentar.
            </div>
        @endauth
    </div>
</div>
