@extends('layouts.app', ['enable_adsense' => true])
@push('head-content')
    <meta name="description" content="{{ Str::limit(strip_tags($adivinhacao->descricao), 150) }}">

    <meta property="og:title" content="{{ $adivinhacao->titulo }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($adivinhacao->descricao), 150) }}">
    <meta property="og:image" content="{{ str_starts_with($adivinhacao->imagem, 'http') ? $adivinhacao->imagem : asset('/storage/' . $adivinhacao->imagem) }}">
    <meta property="og:url" content="{{ route('adivinhacoes.index', $adivinhacao->uuid) }}">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $adivinhacao->titulo }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($adivinhacao->descricao), 150) }}">
    <meta name="twitter:image" content="{{ str_starts_with($adivinhacao->imagem, 'http') ? $adivinhacao->imagem : asset('/storage/' . $adivinhacao->imagem) }}">
@endpush

@section('content')
<div class="container mb-5 mt-2">
    @include('layouts.base_header')
    @include('partials.adivinhacao', ['adivinhacao' => $adivinhacao])
</div>

@if($respostas->isNotEmpty())

<div class="container mb-5 mt-2">
    <div class="text-center mb-4">
        <p class="text-white">Confira abaixo quem respondeu e quando</p>
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
                            <th>Palpite</th>
                            <th class="text-center">Hora</th>
                        </tr>
                    </thead>
                    <tbody id="respostas-container ">
                        @forelse($respostas as $resposta)
                        <tr>
                            <td class="fw-semibold text-center">{{ $resposta->uuid }}</td>
                            <td class="fw-semibold">{{ $resposta->username }}</td>
                            <td>{{ $resposta->resposta }}</td>
                            <td class=" text-center">{{ $resposta->created_at_br }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center ">Acabou! Você viu tudo...</td>
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
