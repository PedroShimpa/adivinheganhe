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

                    <hr>
                    <h5 class="mt-4">Histórico de Conversa</h5>
                    <div class="mb-4">
                        @foreach($suporte->replies as $reply)
                            <div class="card mb-2 {{ $reply->user_id == auth()->id() ? 'border-primary' : 'border-info' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">{{ $reply->user->name ?? 'Usuário' }} {{ $reply->user_id == auth()->id() ? '(Você)' : '' }}</span>
                                        <span class="text-muted" style="font-size:0.9em">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="mb-1">{{ $reply->mensagem }}</p>
                                    @if($reply->attachments && count($reply->attachments))
                                        <div class="mt-2">
                                            @foreach($reply->attachments as $att)
                                                @if(str_contains($att, '/suporte_attachments/') && (str_ends_with($att, '.webp') || str_ends_with($att, '.jpg') || str_ends_with($att, '.png') || str_ends_with($att, '.gif')))
                                                    <img src="{{ $att }}" alt="Anexo" style="max-width:120px;max-height:120px" class="me-2 mb-2 rounded shadow">
                                                @elseif(str_contains($att, '/suporte_attachments/') && (str_ends_with($att, '.mp4') || str_ends_with($att, '.mov') || str_ends_with($att, '.avi') || str_ends_with($att, '.webm')))
                                                    <video src="{{ $att }}" controls style="max-width:220px;max-height:120px" class="me-2 mb-2 rounded shadow"></video>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        @if($suporte->replies->isEmpty())
                            <p class="text-muted">Nenhuma resposta ainda.</p>
                        @endif
                    </div>

                    <hr>
                    <h5 class="mt-4">Enviar uma resposta</h5>
                    <form action="{{ route('suporte.reply', $suporte->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="mensagem" rows="3" class="form-control" placeholder="Digite sua resposta..."></textarea>
                            @error('mensagem') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,video/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF, MP4, MOV, AVI, WEBM, etc. (máx 2 arquivos)</small>
                            @error('attachments') <small class="text-danger">{{ $message }}</small> @enderror
                            @error('attachments.*') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <button type="submit" class="btn btn-success">Enviar resposta</button>
                    </form>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>


@endsection
