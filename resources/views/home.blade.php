@extends('layouts.app')

@section('content')
<div class="container py-4">
  @include('layouts.base_header')
  @forelse($adivinhacoes as $adivinhacao)
  @include('partials.adivinhacao', ['adivinhacao' => $adivinhacao])
  @empty
  <div class="text-center">
    <h5 class="text-muted">Nenhuma adivinhação disponível no momento.</h5>
  </div>
  @endforelse

  @if($premios->isNotEmpty())
  <hr class="my-4">

  <h3 class="mb-3">🎉 Últimos prêmios enviados</h3>

  <div class="row g-3">
    @foreach($premios as $premio)
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-4 p-3 bg-light h-100">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
          <div class="flex-grow-1">
            <h6 class="fw-bold text-primary mb-1">{{ $premio->titulo }}</h6>
            <p class="mb-2"><strong>Resposta:</strong> <span class="text-dark">{{ $premio->resposta }}</span></p>
            <p class="mb-2 small text-muted">👤 <strong>Usuário:</strong> {{ $premio->username }}</p>
            <p class="mb-2 small">
              <strong>Vencedor notificado?</strong>
              @if($premio->vencedor_notificado === 'S')
              <span class="badge bg-success">Sim</span>
              @else
              <span class="badge bg-warning text-dark">Não</span>
              @endif
            </p>

            @if(!empty($premio->previsao_envio_premio))
            <p class="mb-2 small">
              <strong>Data prevista para envio:</strong>
              <span class="badge bg-success">{{ (new DateTime($premio->previsao_envio_premio))->format('d/m/Y')}}</span>
            </p>
            @endif
            <p class="mb-2 small">
              📦 <strong>Enviado?</strong>
              @if($premio->premio_enviado === 'S')
              <span class="badge bg-success">Sim</span>
              @else
              <span class="badge bg-warning text-dark">Não</span>
              @endif
            </p>
          </div>
          <div class="d-flex flex-column gap-2">
            @php $isLink = filter_var($premio->premio, FILTER_VALIDATE_URL); @endphp
            @if($isLink)
            <a href="{{ $premio->premio }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">🎁 Ver prêmio</a>
            @endif
            <a href="{{ route('adivinhacoes.index',$premio->uuid) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">🔍 Ver Adivinhação</a>
          </div>
        </div>
      </div>
    </div>
    @if(env('ENABLE_ADS_TERRA', false))
    <p>
      @include('layouts.ads.ads_terra_banner')
    </p>
    @endif
    @endforeach
  </div>
  @endif

  @if($adivinhacoesExpiradas->isNotEmpty())
  <hr class="my-4">

  <h3 class="mb-3">🎉 Adivinhações expiradas</h3><br>
  <small>Você poderá ver todas as respostas enviadas, não reveleramos a resposta da imagem pois ela pode ser usada novamente no futuro.</small>

  <div class="row g-3">
    @foreach($adivinhacoesExpiradas as $adivinhacao)
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-4 p-3 bg-light h-100">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
          <div class="flex-grow-1">
            <h6 class="fw-bold text-primary mb-1">{{ $adivinhacao->titulo }} - Expirada</h6>
          </div>
          <div class="d-flex flex-column gap-2">
            <a href="{{ route('adivinhacoes.index',$adivinhacao->uuid) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">🔍 Ver Adivinhação</a>
          </div>
        </div>
      </div>
    </div>
    @if(env('ENABLE_ADS_TERRA', false))
    <p>
      @include('layouts.ads.ads_terra_banner')
    </p>
    @endif
    @endforeach
  </div>
  @endif
</div>
@endsection
@push('scripts')
@include('partials.essentials_scripts_to_reply')
@endpush