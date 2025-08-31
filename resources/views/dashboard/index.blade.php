@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">

    <div class="mb-4">
        <h1 class="h3">Dashboard</h1>
        <p class="text-white">Visão geral do sistema</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Usuários</h5>
                    <p class="card-text display-6">{{ $countUsers }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Jogadores na Fila Competitivo</h5>
                    <p class="card-text display-6">{{ $jogadoresNaFilaAgoraCompetitivo }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Posts</h5>
                    <p class="card-text display-6">{{ $countPosts }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Adivinhações</h5>
                    <p class="card-text display-6">{{ $countAdivinhacoes }}</p>
                    <small>Ativas: {{ $countAdivinhacoesAtivas }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-danger shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Respostas Clássico</h5>
                    <p class="card-text display-6">{{ $countRespostasClassico }}</p>
                    <small>Hoje: {{ $countRespostasClassicoToday }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Jogos Adivinhe o Milhão</h5>
                    <p class="card-text display-6">{{ $countJogosAdivinheOmilhao }}</p>
                    <small>Hoje: {{ $countJogosAdivinheOmilhaoToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Partidas Competitivo</h5>
                    <p class="card-text display-6">{{ $countPartidasCompetitivo }}</p>
                    <small>Hoje: {{ $countPartidasCompetitivoToday }}</small>
                </div>
            </div>
        </div>

    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Premiações</h5>
        </div>
        <div class="card-body">
            <table id="premiacoesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Título</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($premiacoes as $premiacao)
                    <tr>
                        <td>{{ $premiacao->id }}</td>
                        <td>{{ $premiacao->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $premiacao->username }}</td>
                        <td>{{ $premiacao->titulo }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#removePremiacao-{{ $premiacao->id }}">
                                Remover
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="removePremiacao-{{ $premiacao->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('premiacoes.delete', ['premiacao' => $premiacao->id]) }}" method="POST">
                                    @method('DELETE')
                                    @csrf
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Excluir Adivinhação</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            Tem certeza que deseja excluir a premiação?
                                        </p>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Comentarios em Adivinhações</h5>
        </div>
        <div class="card-body">
            <table id="comentariosTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Uusuario</th>
                        <th>Adivinhação</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comentarios as $comentario)
                    <tr>
                        <td>{{ $comentario->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $comentario->username }}</td>
                        <td>{{ $comentario->titulo }}</td>
                        <td>{{ $comentario->body }}</td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Adivinhações Ativas</h5>
        </div>
        <div class="card-body">
            <table id="premiacoesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Data de criação</th>
                        <th>Título</th>
                        <th>Qtd Respostas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adivinhacoesAtivas as $adivinhacao)
                    <tr>
                        <td>{{ $adivinhacao->uuid }}</td>
                        <td>{{ $adivinhacao->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $adivinhacao->titulo }}</td>
                        <td>{{ $adivinhacao->respostas->count() }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#removeAdivinhacao-{{ $adivinhacao->id }}">
                                Remover
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="removeAdivinhacao-{{ $adivinhacao->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('adivinhacoes.delete', ['adivinhacao' => $adivinhacao->uuid]) }}" method="POST">
                                    @method('DELETE')
                                    @csrf
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Excluir Adivinhação</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            Tem certeza que deseja excluir a adivinhação?
                                            <strong>{{ $adivinhacao->titulo }}</strong>?
                                        </p>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Respostas Adivinhações Ativas</h5>
        </div>
        <div class="card-body">
            <table id="respostasTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Título</th>
                        <th>Resposta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($respostasAdivinhacoesAtivas as $resposta)
                    <tr>
                        <td>{{ $resposta->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $resposta->username }}</td>
                        <td>{{ $resposta->titulo }}</td>
                        <td>{{ $resposta->resposta }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Tabela de usuários -->
    <!-- Tabela de usuários -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários</h5>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Whatsapp</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->whatsapp }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#banModal-{{ $user->id }}">
                                Banir
                            </button>
                        </td>
                    </tr>

                    <!-- Modal de Banimento -->
                    <div class="modal fade" id="banModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('user.ban', ['user' => $user->username]) }}" method="POST">
                                    @csrf
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Banir Usuário</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            Tem certeza que deseja banir o usuário
                                            <strong>{{ $user->name }} ({{ $user->username }})</strong>?
                                        </p>
                                        <div class="mb-3">
                                            <label for="motivo-{{ $user->id }}" class="form-label">Motivo (opcional)</label>
                                            <textarea name="motivo" id="motivo-{{ $user->id }}" class="form-control" rows="3" placeholder="Escreva o motivo do banimento..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Confirmar Banimento</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>



</div>

<!-- DataTables e Bootstrap via CDN -->
@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#comentariosTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            order: [
                [0, 'desc']
            ]
        });
        $('#premiacoesTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            order: [
                [0, 'desc']
            ]
        });
        $('#usersTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            order: [
                [0, 'desc']
            ]
        });

        $('#respostasTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            order: [
                [0, 'desc']
            ]
        });
    });
</script>
@endpush
@endsection