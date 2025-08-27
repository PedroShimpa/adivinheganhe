<div class="card  mb-4 p-3 animate__animated animate__fadeIn shadow-5 rounded-4 overflow-hidden">
    <div class="row g-0 flex-wrap">
        <div class="col-12 col-md-5">
            <img
                src="{{ str_starts_with($adivinhacao->imagem, 'http') ? $adivinhacao->imagem :  asset('/storage/' . $adivinhacao->imagem) }}"
                class="img-fluid rounded-4 w-100"
                alt="Imagem da adivinhação"
                loading="lazy"
                style="aspect-ratio: 4/3; object-fit: contain; width: 100%; height: auto;"
                width="600"
                height="450"
                fetchpriority=high>
        </div>

        <div class="col-12 col-md-7 p-4 d-flex flex-column justify-content-between">
            <div>
                @if(Auth::check() && auth()->user()->isAdmin())
                <a href="{{ route('adivinhacoes.view', $adivinhacao->uuid)}}" class="btn btn-warning mb-3">Editar</a>
                @endif

                <h5 class=" fw-bold mb-2">
                    {{ $adivinhacao->titulo. (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired ? ' - EXPIRADA' : '') }}
                </h5>

                @if(!empty($adivinhacao->formato_resposta))
                <p class="text-info small mb-2">
                    Formato da resposta: <strong>{{ $adivinhacao->formato_resposta }}</strong>
                </p>
                @endif
                @if(!empty($adivinhacao->expired_at_br))
                <p class="text-info small mb-2">
                    Expira em <strong>{{ $adivinhacao->expired_at_br }}</strong>
                </p>
                @else
                <p class="text-info small mb-2">Esta adivinhação não expira.</p>
                @endif

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
                    <button type="button" class="btn btn-primary btn-sm rounded-pill verRespostas" adivinhacao_id="{{ $adivinhacao->id}}" data-bs-toggle="modal" data-bs-target="#modalSuasRespostas">
                        📜 Seus Palpites
                    </button>
                    <button class="btn btn-info btn-sm mb-3 rounded-pill abrirModalInformacoes" data-bs-toggle="modal" data-bs-target="#modalInformacoes" titulo="{{ $adivinhacao->titulo. (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired ? ' - EXPIRADA' : '') }}"
                        descricao="{{ $adivinhacao->descricao}}">
                        ➕ Informações
                    </button>
                </div>
                @if($adivinhacao->limitExceded)
                <div class="alert alert-warning small py-2 px-3 rounded-pill">⚠️ Você atingiu o limite de palpites dessa adivinhação para hoje!</div>
                @else
                @if($adivinhacao->resolvida != 'S')
                <div class="alert alert-success small py-2 px-3 rounded-pill">Palpites restantes: <span id="palpites_adivinhacao_{{ $adivinhacao->id}}">{{ $adivinhacao->palpites_restantes }}</span></div>
                <div class="mb-2">
                    <input type="text" id="resposta-{{ $adivinhacao->id }}" class="form-control border-primary fw-semibold rounded-3" name="resposta" placeholder="💬 Digite seu palpite">
                </div>
                <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
                <button class="btn btn-primary w-100 rounded-pill py-2 sendResposta" id="btn-resposta-{{ $adivinhacao->id }}" @if($adivinhacao->expired && !empty($adivinhacao->expired_at_br)) disabled @endif>
                    Enviar palpite
                </button>
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

                <div class="mt-3">
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <span class="fw-semibold small">Compartilhar:</span>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($adivinhacao->titulo . ' ' . route('adivinhacoes.index', $adivinhacao->uuid)) }}" target="_blank" class="btn btn-sm btn-success rounded-pill">
                            WhatsApp
                        </a>
                    </div>
                    <button class="btn btn-secondary btn-sm rounded-pill verComentarios"
                        data-id="{{ $adivinhacao->id }}"
                        data-route="{{ route('adivinhacoes.comments', $adivinhacao->uuid) }}">
                        💬 Comentários
                    </button>

                    <div id="comentarios-{{ $adivinhacao->id }}" class="comentarios-box d-none mt-3 p-3 rounded-4 bg-light shadow-sm animate__animated">
                        <div class="comentarios-list small mb-3 text-dark">
                            <p class="text-muted">Carregando comentários...</p>
                        </div>

                        @auth
                        <div class="input-group">
                            <input type="text" id="comentario-input-{{ $adivinhacao->id }}" class="form-control rounded-start-pill" placeholder="💬 Escreva um comentário...">
                            <button class="btn btn-primary rounded-end-pill sendComment"
                                data-id="{{ $adivinhacao->id }}"
                                data-route="{{ route('adivinhacoes.comment', $adivinhacao->uuid) }}">
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

            @php $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL); @endphp
            @if($isLink)
            <div class="mt-3 text-end">
                <a href="{{ $adivinhacao->premio }}" class="btn btn-primary btn-sm rounded-pill" target="_blank">🎁 Ver prêmio</a>
            </div>
            @endif
        </div>
    </div>
</div>