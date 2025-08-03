{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    @include('layouts.base_header')
    @include('partials.adivinhacao', ['adivinhacao' => $adivinhacao])
</div>

@if($respostas->isNotEmpty())

<div class="container py-5">
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
                            <td class="text-white text-center">{{ $resposta->created_at_br }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-white">Acabou! Você viu tudo...</td>
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
