@extends('layouts.app')

@section('title', 'Chamado #' . $suporte->id)

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h1 class="h3 mb-0">Chamado #{{ $suporte->id }}</h1>
                    <a href="{{ route('suporte.user.index') }}" class="btn btn-light btn-sm">Voltar</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Categoria:</strong> {{ $suporte->categoria->descricao ?? 'N/A' }}</p>
                            <p><strong>Status:</strong>
                                @if($suporte->status === 'A')
                                    <span class="badge bg-warning">Aguardando</span>
                                @elseif($suporte->status === 'EA')
                                    <span class="badge bg-info">Em Atendimento</span>
                                @elseif($suporte->status === 'F')
                                    <span class="badge bg-success">Finalizado</span>
                                @endif
                            </p>
                            <p><strong>Data:</strong> {{ $suporte->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Descrição:</strong>
                            <p>{{ $suporte->descricao }}</p>
                            @if(!empty($suporte->admin_response))
                                <strong>Resposta do Suporte:</strong>
                                <p>{{ $suporte->admin_response }}</p>
                            @endif
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>


@endsection
