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
            {!! $premiacoesTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Comentarios em Adivinhações</h5>
        </div>
        <div class="card-body">
            {!! $comentariosTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Adivinhações Ativas</h5>
        </div>
        <div class="card-body">
            {!! $adivinhacoesAtivasTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Respostas Adivinhações Ativas</h5>
        </div>
        <div class="card-body">
            {!! $respostasTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>
    <!-- Tabela de usuários -->
    <!-- Tabela de usuários -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários</h5>
        </div>
        <div class="card-body">
            {!! $usersTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>



</div>

@push('scripts')
{!! $premiacoesTable->scripts() !!}
{!! $comentariosTable->scripts() !!}
{!! $adivinhacoesAtivasTable->scripts() !!}
{!! $respostasTable->scripts() !!}
{!! $usersTable->scripts() !!}
@endpush
@endsection