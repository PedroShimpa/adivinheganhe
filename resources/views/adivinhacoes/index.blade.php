{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">

    @if(Auth::check())
    <div class="mb-4 p-3 bg-light rounded shadow-sm text-center">
        <h5 class="mb-2 text-primary fw-semibold">
            Indique e ganhe <strong>5 tentativas por novo Adivinhador registrado no seu link!</strong>
        </h5>
        <div class="input-group mb-3 mx-auto " style="max-width: 100%;">
            <input type="text" id="linkIndicacao" class="form-control text-truncate" style="max-width: 400px;"
                value="{{ route('register', ['ib' => auth()->user()->uuid]) }}" readonly>
            <button class="btn btn-outline-primary" id="btnCopiarLink">Copiar link</button>
        </div>
        <p class="mb-1"><strong id="tentativas-restantes">Restam {{ $trys }}</strong> tentativas.
            <a href="{{ route('tentativas.comprar') }}" class="btn btn-sm btn-primary ms-2">Comprar mais</a>
        </p>
        <p class="small">Você tem 10 tentativas (não acumulativas) gratuitas todos os dias!</p>
    </div>
    @endif

    <div class="card mb-4 shadow-sm">
        <div class="row g-0 flex-wrap">
            <div class="col-12 col-md-5 d-flex align-items-center justify-content-center bg-light p-2">
                <img src="{{ asset('storage/' . $adivinhacao->imagem) }}" class="img-fluid w-100 rounded" alt="Imagem da adivinhação">
            </div>
            <div class="col-12 col-md-7 p-3 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="text-primary fw-bold mb-2">{{ $adivinhacao->titulo.  ($adivinhacao->expired ?: ' - EXPIRADA') }}</h5>
                    @if(!empty($adivinhacao->expired_at_br))
                    <p class="text-primary">Esta adivinhação expira em {{$adivinhacao->expired_at_br}}, mas não se preocupe, caso ela expire, incluiremos outra com o mesmo prêmio e outra imagem!</p>
                    @endif

                    <button class="btn btn-outline-info btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalDescricao-{{ $adivinhacao->id }}">
                        ➕ Informações
                    </button>

                    <div class="modal fade" id="modalDescricao-{{ $adivinhacao->id }}" tabindex="-1" aria-labelledby="modalLabel-{{ $adivinhacao->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel-{{ $adivinhacao->id }}">{{ $adivinhacao->titulo.  ($adivinhacao->expired ?: ' - EXPIRADA')}}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">{!! $adivinhacao->descricao !!}</div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mt-2">
                        👥 <span id="count-respostas-{{ $adivinhacao->id}}"> {{ $adivinhacao->count_respostas ?: 'Ninguém tentou adivinhar ainda!' }}</span> respostas até agora
                    </p>

                    @auth
                    @if($limitExceded)
                    <div class="alert alert-warning p-2 small">Você atingiu o limite de tentativas hoje!</div>
                    @else
                    <div class="mb-2">
                        <input type="text" id="resposta-{{ $adivinhacao->id }}" class="form-control border-primary fs-6 fw-semibold" name="resposta" placeholder="💬 Digite sua resposta">
                    </div>
                    <input type="hidden" name="adivinhacao_id" value="{{ $adivinhacao->id }}">
                    <button class="btn btn-success w-100" id="btn-resposta-{{ $adivinhacao->id }}" disabled="{{ $adivinhacao->expired == true ? true : false}}">Enviar resposta</button>
                    @endif
                    @else
                    <div class="alert alert-warning small">
                        Você precisa <a href="{{ route('login') }}">entrar</a> para responder. É <span class="text-success">grátis</span>!
                    </div>
                    @endauth
                </div>

                @php $isLink = filter_var($adivinhacao->premio, FILTER_VALIDATE_URL); @endphp
                @if($isLink)
                <div class="mt-3 text-end">
                    <a href="{{ $adivinhacao->premio }}" class="btn btn-outline-primary btn-sm" target="_blank">🎁 Ver prêmio</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($adivinhacao->resolvida == 'S')

<div class="container py-5">
    <div class="text-center mb-4">
        <p class="text-muted">Confira abaixo quem respondeu e quando</p>
        <hr class="w-25 mx-auto">
    </div>

    <div class="card shadow rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-primary ">
                        <tr>
                            <th class="text-center">Código</th>
                            <th>Usuário</th>
                            <th>Resposta</th>
                            <th class="text-center">Hora</th>
                        </tr>
                    </thead>
                    <tbody id="respostas-container">
                        @forelse($respostas as $resposta)
                        <tr>
                            <td class="fw-semibold text-center">{{ $resposta->uuid }}</td>
                            <td class="fw-semibold">{{ $resposta->username }}</td>
                            <td>{{ $resposta->resposta }}</td>
                            <td class="text-muted text-center">{{ $resposta->created_at_br }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Acabou! Você viu tudo...</td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
            <div class="mt-3">
                {{ $respostas->links() }}
            </div>
        </div>

    </div>
</div>
@endif


@endsection
@push('scripts')
@include('partials.essentials_scripts_to_reply')

@endpush