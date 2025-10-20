<div class="card mb-4 p-3 shadow-5 rounded-4 overflow-hidden">
    <div class="row g-0 flex-wrap">
        {{-- Coluna do arquivo --}}
        <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
            @php
                $file = $adivinhacao->imagem;
                $fileUrl = str_starts_with($file, 'http') ? $file : asset('/storage/' . $file);
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            @endphp

            @if(in_array($extension, ['jpg','jpeg','png','gif','webp','avif']))
                <img
                    src="{{ $fileUrl }}"
                    class="img-fluid rounded-4 w-100"
                    alt="Arquivo da adivinhação"
                    loading="lazy"
                    style="aspect-ratio: 4/3; object-fit: contain; width: 100%; height: auto;"
                    width="600"
                    height="450"
                    fetchpriority="high"
                >
            @elseif(in_array($extension, ['mp4','webm','ogg']))
                <video
                    class="rounded-4 w-100"
                    controls
                    preload="metadata"
                    style="aspect-ratio: 4/3; object-fit: contain; width: 100%; height: auto;"
                >
                    <source src="{{ $fileUrl }}" type="video/{{ $extension }}">
                    Seu navegador não suporta vídeo.
                </video>
            @elseif(in_array($extension, ['mp3','wav','ogg']))
                <audio
                    class="w-100"
                    controls
                    preload="metadata"
                >
                    <source src="{{ $fileUrl }}" type="audio/{{ $extension }}">
                    Seu navegador não suporta áudio.
                </audio>
            @elseif($extension === 'pdf')
                <iframe
                    src="{{ $fileUrl }}"
                    class="w-100 rounded-4"
                    style="aspect-ratio: 4/3; border:none;"
                    loading="lazy"
                ></iframe>
            @else
                <div class="alert alert-warning text-center w-100">
                    Arquivo não suportado
                    <br>
                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-primary mt-2">Download</a>
                </div>
            @endif
        </div>

        {{-- Coluna de conteúdo --}}
        <div class="col-12 col-md-7 p-4 d-flex flex-column justify-content-between">
            <div>
                {{-- Botão de edição para admins --}}
                @if(Auth::check() && auth()->user()->isAdmin())
                    <a href="{{ route('adivinhacoes.view', $adivinhacao->uuid)}}" class="btn btn-warning mb-3">Editar</a>
                @endif

                {{-- Título --}}
                <h5 class="fw-bold mb-2">
                    {{ $adivinhacao->titulo. (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired ? ' - EXPIRADA' : '') }}
                </h5>

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

                {{-- Data de expiração --}}
                @if(!empty($adivinhacao->expired_at_br))
                    <p class="text-info small mb-2">
                        Expira em <strong>{{ $adivinhacao->expired_at_br }}</strong>
                    </p>
                @else
                    <p class="text-info small mb-2">Esta adivinhação não expira.</p>
                @endif

                {{-- Dica --}}
                @if(!empty($adivinhacao->dica) && ($adivinhacao->resolvida != 'S'))
                    @if($adivinhacao->dica_paga == 'S')
                        @if(!$adivinhacao->buyed)
                            <div class="alert alert-warning d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>Dica disponível:</strong> Esta dica custa <strong>R${{ number_format($adivinhacao->dica_valor, 2, ',', '.') }}</strong>
                                </div>
                                <a href="{{ route('dicas.index_buy', $adivinhacao->uuid) }}" class="btn btn-sm btn-primary rounded-pill">Comprar dica</a>
                            </div>
                        @else
                            <div class="alert alert-info rounded-3">
                                <strong>Dica:</strong> {{ $adivinhacao->dica }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info rounded-3">
                            <strong>Dica:</strong> {{ $adivinhacao->dica }}
                        </div>
                    @endif
                @endif

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
                            @if(auth()->user()->isVip() || !isset($title) || $title !== 'Adivinhações VIP')
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
                                <input type="text" id="comentario-input-{{ $adivinhacao->id }}" class="form-control rounded-start-pill" placeholder="💬 Escreva um comentário...">
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
            </div>

            {{-- Botão de prêmio se for link --}}
            @php $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL); @endphp
            @if($isLink)
                <div class="mt-3 text-end">
                    <a href="{{ $adivinhacao->premio }}" class="btn btn-primary btn-sm rounded-pill" target="_blank">🎁 Ver prêmio</a>
                </div>
            @endif
        </div>
    </div>
</div>
