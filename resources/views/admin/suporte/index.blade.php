@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">
    <div class="mb-4">
        <h1 class="h3">Gerenciamento de Suporte</h1>
        <p class="text-white">Chamados de suporte abertos</p>
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
