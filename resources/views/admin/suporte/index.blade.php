@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">
    <div class="mb-4">
        <h1 class="h3">Gerenciamento de Suporte</h1>
        <p class="text-white">Chamados de suporte abertos</p>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTicketModal">Criar Chamado para Cliente</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suportes as $suporte)
                    <tr>
                        <td>{{ $suporte->id }}</td>
                        <td>{{ $suporte->nome ?? $suporte->user->name ?? 'N/A' }}</td>
                        <td>{{ $suporte->email ?? $suporte->user->email ?? 'N/A' }}</td>
                        <td>{{ $suporte->categoria->descricao ?? 'N/A' }}</td>
                        <td>
                            @if($suporte->status === 'A')
                                <span class="badge bg-warning">Aguardando</span>
                            @elseif($suporte->status === 'EA')
                                <span class="badge bg-info">Em Atendimento</span>
                            @elseif($suporte->status === 'F')
                                <span class="badge bg-success">Finalizado</span>
                            @else
                                <span class="badge bg-secondary">Desconhecido</span>
                            @endif
                        </td>
                        <td>{{ $suporte->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('suporte.admin.show', $suporte) }}" class="btn btn-sm btn-primary">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $suportes->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const createTicketForm = document.getElementById('createTicketForm');

    createTicketForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admin/suporte/create-ticket', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Chamado criado com sucesso!');
                location.reload();
            } else {
                alert('Erro ao criar chamado: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao criar chamado');
        });
    });
});
</script>
@endpush

@push('extra_modais')
<!-- Modal for creating ticket -->
<div class="modal fade" id="createTicketModal" tabindex="-1" aria-labelledby="createTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createTicketForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createTicketModalLabel">Criar Chamado para Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Cliente (ID ou Nome)</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">Selecione um cliente...</option>
                                @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->id }} - {{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoria</label>
                            <select class="form-control" id="categoria_id" name="categoria_id" required>
                                <option value="">Selecione uma categoria...</option>
                                @foreach(\App\Models\SuporteCategorias::all() as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->descricao }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto</label>
                            <input type="text" class="form-control" id="assunto" name="assunto" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Anexos (opcional, até 2 imagens, max 2MB cada)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple accept="image/*" max="2">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Inicial</label>
                            <select class="form-control" id="status" name="status">
                                <option value="A">Aguardando</option>
                                <option value="EA">Em Atendimento</option>
                            </select>
                        </div>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Chamado</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endpush