{{-- resources/views/home.blade.php --}}
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

  <h5 class="mb-3">🎉 Ultimos prêmios enviados</h5>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark small">
        <tr>
          <th>Título</th>
          <th>Resposta</th>
          <th>Ações</th>
          <th>Usuário</th>
          <th>Enviado?</th>
        </tr>
      </thead>
      <tbody>
        @foreach($premios as $premio)
        <tr>
          <td>{{ $premio->titulo }}</td>
          <td>{{ $premio->resposta }}</td>
          <td>
            @php $isLink = filter_var($premio->premio, FILTER_VALIDATE_URL); @endphp
            @if($isLink)
            <a href="{{ $premio->premio }}" target="_blank" class="btn btn-sm btn-outline-primary">Ver prêmio</a>
            @endif
            <a href="{{ route('adivinhacoes.index',$premio->uuid) }}" target="_blank" class="btn btn-sm btn-outline-success">Ver Adivinhação</a>
          </td>
          <td>{{ $premio->username }}</td>
          <td>
            @if($premio->premio_enviado === 'S')
            <span class="badge bg-success">Sim</span>
            @else
            <span class="badge bg-warning text-dark">Não</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
@push('scripts')
@include('partials.essentials_scripts_to_reply')
@endpush