<div class="card mb-4 shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="row g-0 flex-wrap">
        <div class="col-12 col-md-5">
            <img 
                src="{{ asset('storage/' . $adivinhacao->imagem) }}" 
                class="img-fluid rounded-4 shadow-sm w-100"
                alt="Imagem da adivinhação" 
                loading="lazy"
                style="aspect-ratio: 4/3; object-fit: cover; width: 100%; height: auto;"
                width="600"
                height="450"
            >
        </div>

        <div class="col-12 col-md-7 p-4 d-flex flex-column justify-content-between">
            <div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('adivinhacoes.view', $adivinhacao->uuid)}}" class="btn btn-warning">Editar</a>
                <br>
                @endif
                <h5 class="text-primary fw-bold mb-2">
                    {{ $adivinhacao->titulo.  (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired == true ? ' - EXPIRADA': '' ) }}
                </h5>

                @if(!empty($adivinhacao->expired_at_br))
                <p class="text-primary small mb-2">
                    Expira em <strong>{{ $adivinhacao->expired_at_br }}</strong>.
                </p>
                @else
                <p class="text-primary small mb-2">Esta adivinhação não expira.</p>
                @endif

                <button class="btn btn-outline-info btn-sm mb-3 rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalDescricao-{{ $adivinhacao->id }}">
                    ➕ Informações
                </button>

                @if(!empty($adivinhacao->dica) && ($adivinhacao->resolvida != 'S'))
                @if($adivinhacao->dica_paga == 'S')
                @if(!$adivinhacao->buyed== true)
                <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
                    <div>
                        <strong>Dica disponível:</strong> Esta dica custa <strong>R${{ number_format($adivinhacao->dica_valor, 2, ',', '.') }} (a dica só aparecerá para você)</strong>.
                    </div>
                    <a href="{{ route('dicas.index_buy', $adivinhacao->uuid) }}" class="btn btn-sm btn-primary">Comprar dica</a>
                </div>
                @else
                <div class="alert alert-info" role="alert">
                    <strong>Dica:</strong> {{ $adivinhacao->dica }}
                </div>
                @endif
                @else
                <div class="alert alert-info" role="alert">
                    <strong>Dica:</strong> {{ $adivinhacao->dica }}
                </div>
                @endif
                @endif

                <p class="mb-1 small"><strong>Código da adivinhação:</strong> {{ $adivinhacao->uuid }}</p>

                <div class="modal fade" id="modalDescricao-{{ $adivinhacao->id }}" tabindex="-1" aria-labelledby="modalLabel-{{ $adivinhacao->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 shadow-sm">
                            <div class="modal-header border-0">
                                <h5 class="modal-title" id="modalLabel-{{ $adivinhacao->id }}">{{ $adivinhacao->titulo.  (!empty($adivinhacao->expired_at_br) && $adivinhacao->expired == true ? ' - EXPIRADA': '' ) }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">{!! $adivinhacao->descricao !!}</div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>

                @auth
                @if($limitExceded)
                <div class="alert alert-warning small py-2 px-3 rounded-pill">⚠️ Você atingiu o limite de tentativas hoje!</div>
                @else
                <div class="mb-2">
                    <input type="text" id="resposta-{{ $adivinhacao->id }}" class="form-control border-primary fw-semibold rounded-3" name="resposta" placeholder="💬 Digite sua resposta">
                </div>
                <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
                @if($adivinhacao->resolvida != 'S')
                <button class="btn btn-success w-100 rounded-pill py-2" id="btn-resposta-{{ $adivinhacao->id }}" @if($adivinhacao->expired == true && !empty($adivinhacao->expired_at_br)) disabled @endif>
                    Enviar resposta
                </button>
                @endif
                @endif
                @else
                <div class="alert alert-warning small rounded-3 mt-2">
                    Você precisa <a href="{{ route('login') }}" class="text-decoration-underline fw-semibold">entrar</a> para responder. É <span class="text-success fw-semibold">grátis</span>!
                </div>
                @endauth
            </div>

            @php $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL); @endphp
            @if($isLink)
            <div class="mt-3 text-end">
                <a href="{{ $adivinhacao->premio }}" class="btn btn-outline-primary btn-sm rounded-pill" target="_blank">🎁 Ver prêmio</a>
            </div>
            @endif
        </div>
    </div>
</div>

@if(env('ENABLE_ADS_TERRA', false))
@include('layouts.ads.ads_terra_banner')
@endif
