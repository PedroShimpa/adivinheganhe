@extends('layouts.app')

@section('title', 'Meus Chamados')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card glass">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">Meus Chamados</h1>
                </div>
                <div class="card-body">
                    @if($suportes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
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
                                            <td>{{ $suporte->categoria->descricao ?? 'N/A' }}</td>
                                            <td>
                                                @if($suporte->status === 'A')
                                                    <span class="badge bg-warning">Aguardando</span>
                                                @elseif($suporte->status === 'EA')
                                                    <span class="badge bg-info">Em Atendimento</span>
                                                @elseif($suporte->status === 'F')
                                                    <span class="badge bg-success">Finalizado</span>
                                                @endif
                                            </td>
                                            <td>{{ $suporte->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('suporte.user.show', $suporte) }}" class="btn btn-sm btn-primary">Ver</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $suportes->links() }}
                    @else
                        <p class="text-center">Você ainda não possui chamados abertos.</p>
                        <div class="text-center">
                            <a href="{{ route('suporte.index') }}" class="btn btn-success">Abrir Novo Chamado</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
