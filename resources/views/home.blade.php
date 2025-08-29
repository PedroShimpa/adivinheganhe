@extends('layouts.app', ['enable_adsense' => true])

@section('content')
    <div class="container ">
        @if (!empty($regiao))
            <div class="mb-4 mt-4">
                <h1 class="text-white text-center">{{ $regiao->nome }}</h1>
            </div>
        @endif
        @include('layouts.base_header')

        @forelse($adivinhacoes as $adivinhacao)
            @include('partials.adivinhacao', ['adivinhacao' => $adivinhacao])
        @empty
            <div class="text-center animate__animated animate__fadeIn">
                <h5 class="text-white">Nenhuma adivinhação disponível no momento.</h5>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    @include('partials.adivinhacoes_scripts')
@endpush
