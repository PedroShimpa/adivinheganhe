@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">
    <div class="mb-4">
        <h1 class="h3">Chamado #{{ $suporte->id }}</h1>
        <a href="{{ route('suporte.admin.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5>Detalhes do Chamado</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $suporte->nome ?? $suporte->user->name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $suporte->email ?? $suporte->user->email ?? 'N/A' }}</p>
                    <p><strong>Categoria:</strong> {{ $suporte->categoria->descricao ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        @if($suporte->status === 'A')
                            <span class="badge bg-warning">Aguardando</span>
                        @elseif($suporte->status === 'EA')
                            <span class="badge bg-info">Em Atendimento</span>
                        @elseif($suporte->status === 'F')
                            <span class="badge bg-success">Finalizado</span>
                        @else
                            <span class="badge bg-secondary">Desconhecido</span>
                        @endif
                    </p>
                    <p><strong>Data:</strong> {{ $suporte->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="mt-3">
                <strong>Descrição:</strong>
                <p>{{ $suporte->descricao }}</p>
            </div>
            @if(!empty($suporte->attachments))
            <div class="mt-3">
                <strong>Anexos:</strong>
                <div class="row">
                    @foreach(json_decode($suporte->attachments) as $attachment)
                    <div class="col-md-3 mb-2">
                        <img src="{{ $attachment }}" class="img-fluid rounded attachment-preview" alt="Anexo" style="max-height: 150px; cursor: pointer;" data-src="{{ $attachment }}">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @if(!empty($suporte->admin_response))
            <div class="mt-3">
                <strong>Resposta do Suporte:</strong>
                <p>{{ $suporte->admin_response }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5>Atualizar Chamado</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('suporte.admin.update', $suporte) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="A" {{ $suporte->status === 'A' ? 'selected' : '' }}>Aguardando</option>
                        <option value="EA" {{ $suporte->status === 'EA' ? 'selected' : '' }}>Em Atendimento</option>
                        <option value="F" {{ $suporte->status === 'F' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="admin_response" class="form-label">Resposta do Suporte</label>
                    <textarea name="admin_response" id="admin_response" class="form-control" rows="5">{{ $suporte->admin_response }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Atualizar</button>
            </form>
        </div>
    </div>
</div>

<!-- Attachment Modal -->
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalLabel">Visualizar Anexo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="attachmentModalImage" src="" class="img-fluid" alt="Anexo">
            </div>
            <div class="modal-footer">
                <a id="attachmentDownloadLink" href="" download class="btn btn-primary">Download</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Document ready, initializing attachment modal...');

    // Attachment modal functionality using jQuery
    $('.attachment-preview').on('click', function(e) {
        e.preventDefault();
        console.log('Attachment clicked');

        const src = $(this).data('src');
        console.log('Image source:', src);

        if (src) {
            $('#attachmentModalImage').attr('src', src);
            $('#attachmentDownloadLink').attr('href', src);
            $('#attachmentModal').modal('show');
            console.log('Modal should be shown');
        } else {
            console.error('No src found for attachment');
        }
    });

    // Test if modal elements exist
    console.log('Modal elements check:');
    console.log('Modal exists:', $('#attachmentModal').length > 0);
    console.log('Image exists:', $('#attachmentModalImage').length > 0);
    console.log('Download link exists:', $('#attachmentDownloadLink').length > 0);
    console.log('Attachment previews found:', $('.attachment-preview').length);
  
});
</script>
@endpush
