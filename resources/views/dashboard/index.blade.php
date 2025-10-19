@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">

    <div class="mb-4">
        <h1 class="h3">Dashboard</h1>
        <p class="text-white">Visão geral do sistema</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Usuários Online</h5>
                    <p class="card-text display-6" id="online-users-count">{{ $countUsersOnline }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Usuários</h5>
                    <p class="card-text display-6" id="total-users-count">{{ $countUsers }}</p>
                    <small>Hoje: {{ $countUsersToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Respostas Clássico</h5>
                    <p class="card-text display-6" id="total-responses-count">{{ $countRespostasClassico }}</p>
                    <small>Hoje: {{ $countRespostasClassicoToday }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Adivinhações</h5>
                    <p class="card-text display-6">{{ $countAdivinhacoes }}</p>
                    <small>Ativas: {{ is_numeric($countAdivinhacoesAtivas) ? $countAdivinhacoesAtivas : (is_array($countAdivinhacoesAtivas) ? count($countAdivinhacoesAtivas) : 0) }}</small>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Jogos Adivinhe o Milhão</h5>
                    <p class="card-text display-6">{{ $countJogosAdivinheOmilhao }}</p>
                    <small>Hoje: {{ $countJogosAdivinheOmilhaoToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-secondary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Partidas Competitivo</h5>
                    <p class="card-text display-6">{{ $countPartidasCompetitivo }}</p>
                    <small>Hoje: {{ $countPartidasCompetitivoToday }}</small>
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


    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Horários com mais respostas</h5>
                </div>
                <div class="card-body">
                    <canvas id="horariosChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Dias da semana com mais respostas</h5>
                </div>
                <div class="card-body">
                    <canvas id="diasChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuários Online -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários Online</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush" id="online-users-list">
                @if(isset($onlineUsers['users']) && is_array($onlineUsers['users']) && count($onlineUsers['users']) > 0)
                @foreach($onlineUsers['users'] as $user)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $user['name'] ?? $user->name ?? 'Usuário Desconhecido' }}
                    <span class="badge bg-success rounded-pill">Online</span>
                </li>
                @endforeach
                @else
                <li class="list-group-item text-muted">Nenhum usuário online no momento.</li>
                @endif
            </ul>
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
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários</h5>
        </div>
        <div class="card-body">
            {!! $usersTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <!-- Ganhos do AdSense -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Ganhos do AdSense</h5>
                    <p class="card-text display-6">R$ {{ $adsenseEarnings['thisMonth'] }}</p>
                    <small>Este Mês</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Ganhos do AdSense</h5>
                    <p class="card-text display-6">R$ {{ $adsenseEarnings['today'] }}</p>
                    <small>Hoje</small>
                </div>
            </div>
        </div>
    </div>
    @if($adsenseEarnings['error'])
    <div class="alert alert-warning" role="alert">
        {{ $adsenseEarnings['error'] }}
    </div>
    @endif

</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Laravel Echo for real-time updates -->
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
<script>
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("REVERB_APP_KEY") }}',
        wsHost: '{{ env("VITE_REVERB_HOST", "adivinheganhe.com.br") }}',
        wsPort: '{{ env("VITE_REVERB_PORT", 443) }}',
        forceTLS: false,
        disableStats: true,
        authEndpoint: '/broadcasting/auth-mixed',
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        }
    });

    // Listen for dashboard updates
    Echo.channel('dashboard-updates')
        .listen('user.registered', (e) => {
            try {
                const countElement = document.getElementById('total-users-count');
                if (countElement) {
                    countElement.textContent = e.totalUsers;
                    countElement.classList.add('count-update');
                    setTimeout(() => countElement.classList.remove('count-update'), 500);
                }
            } catch (error) {
                console.error('Error updating user count:', error);
            }
        })
        .listen('response.added', (e) => {
            try {
                const countElement = document.getElementById('total-responses-count');
                if (countElement) {
                    countElement.textContent = e.totalResponses;
                    countElement.classList.add('count-update');
                    setTimeout(() => countElement.classList.remove('count-update'), 500);
                }
            } catch (error) {
                console.error('Error updating response count:', error);
            }
        })
        .listen('online.users.updated', (e) => {
            try {
                const onlineCountElement = document.getElementById('online-users-count');
                if (onlineCountElement) {
                    onlineCountElement.textContent = e.count;
                    onlineCountElement.classList.add('count-update');
                    setTimeout(() => onlineCountElement.classList.remove('count-update'), 500);
                }

                // Update online users list
                const onlineUsersList = document.getElementById('online-users-list');
                if (onlineUsersList && e.users) {
                    onlineUsersList.innerHTML = '';
                    if (e.users.length > 0) {
                        e.users.forEach(user => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center';
                            li.innerHTML = `
                                ${user.name || user['name'] || 'Usuário Desconhecido'}
                                <span class="badge bg-success rounded-pill">Online</span>
                            `;
                            onlineUsersList.appendChild(li);
                        });
                    } else {
                        onlineUsersList.innerHTML = '<li class="list-group-item text-muted">Nenhum usuário online no momento.</li>';
                    }
                }
            } catch (error) {
                console.error('Error updating online users:', error);
            }
        })
        .error((error) => {
            console.error('Echo connection error:', error);
        });
</script>

<style>
    .count-update {
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }
</style>

{!! $premiacoesTable->scripts() !!}
{!! $comentariosTable->scripts() !!}
{!! $adivinhacoesAtivasTable->scripts() !!}
{!! $respostasTable->scripts() !!}
{!! $usersTable->scripts() !!}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Horários Chart
        const ctxHorarios = document.getElementById('horariosChart').getContext('2d');
        new Chart(ctxHorarios, {
            type: 'line',
            data: {
                labels: Array.from({
                    length: 24
                }, (_, i) => i.toString()),
                datasets: [{
                    label: 'Respostas',
                    data: @json($horariosRespostas),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Dias da Semana Chart
        const ctxDias = document.getElementById('diasChart').getContext('2d');
        new Chart(ctxDias, {
            type: 'line',
            data: {
                labels: Object.keys(@json($diasSemanaRespostas)),
                datasets: [{
                    label: 'Respostas',
                    data: Object.values(@json($diasSemanaRespostas)),
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection