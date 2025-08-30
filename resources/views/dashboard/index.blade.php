@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">

    <!-- Título -->
    <div class="mb-4">
        <h1 class="h3">Dashboard</h1>
        <p class="text-white">Visão geral do sistema</p>
    </div>

    <!-- Cards de estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Usuários</h5>
                    <p class="card-text display-6">{{ $countUsers }}</p>
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
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Posts</h5>
                    <p class="card-text display-6">{{ $countPosts }}</p>
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
    </div>

    <!-- Segundo bloco de cards -->
    <div class="row g-3 mb-4">
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
                    <h5 class="card-title">Respostas Competitivo</h5>
                    <p class="card-text display-6">{{ $countRespostasCompetitivo }}</p>
                    <small>Hoje: {{ $countRespostasCompetitivoToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Jogadores na Fila Competitivo</h5>
                    <p class="card-text display-6">{{ $jogadoresNaFilaAgoraCompetitivo }}</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabela de respostas ativas -->
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
                        <th>Resposta Correta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($respostasAdivinhacoesAtivas as $resposta)
                    <tr>
                        <td>{{ $resposta->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $resposta->name }}</td>
                        <td>{{ $resposta->titulo }}</td>
                        <td>{{ $resposta->resposta }}</td>
                        <td>{{ $resposta->resposta_correta }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Tabela de usuários -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários </h5>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Whatsapp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->whatsapp }}</td>
                    </tr>
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