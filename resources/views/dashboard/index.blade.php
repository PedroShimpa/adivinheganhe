@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">

    <div class="mb-4">
        <h1 class="h3">Dashboard</h1>
        <p class="text-white">Visão geral do sistema</p>
    </div>

    <!-- Date Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div id="cards" class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Usuários Online</h5>
                    <p class="card-text display-6 mb-0" id="online-users-count">{{ $countUsersOnline }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Usuários</h5>
                    <p class="card-text display-6 mb-0" id="total-users-count">{{ $countUsers }}</p>
                    <small>Hoje: {{ $countUsersToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Respostas Clássico</h5>
                    <p class="card-text display-6 mb-0" id="total-responses-count">{{ $countRespostasClassico }}</p>
                    <small>Hoje: {{ $countRespostasClassicoToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Adivinhações</h5>
                    <p class="card-text display-6 mb-0">{{ $countAdivinhacoes }}</p>
                    <small>Ativas: {{ is_numeric($countAdivinhacoesAtivas) ? $countAdivinhacoesAtivas : (is_array($countAdivinhacoesAtivas) ? count($countAdivinhacoesAtivas) : 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Jogos Adivinhe o Milhão</h5>
                    <p class="card-text display-6 mb-0">{{ $countJogosAdivinheOmilhao }}</p>
                    <small>Hoje: {{ $countJogosAdivinheOmilhaoToday }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Usuários VIP</h5>
                    <p class="card-text display-6 mb-0">{{ $countVipUsers }}</p>
                    <small>Ativos</small>
                    <a href="{{ route('dashboard.vip_users') }}" class="btn btn-sm btn-outline-light mt-2">
                        <i class="bi bi-list"></i> Ver Lista
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Partidas Competitivo</h5>
                    <p class="card-text display-6 mb-0">{{ $countPartidasCompetitivo }}</p>
                    <small>Hoje: {{ $countPartidasCompetitivoToday }}</small>
                    <small>Na fila: {{ $jogadoresNaFilaAgoraCompetitivo }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm" style="height: 180px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title">Chamados em Aberto</h5>
                    <p class="card-text display-6 mb-0">{{ $countChamadosAguardando }}</p>
                    <small>Aguardando atendimento</small>
                </div>
            </div>
        </div>
    </div>

    <div id="charts" class="row g-3 mb-4">
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
        <div class="col-md-12" id="indicadores-chart">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Usuários que mais indicaram</h5>
                </div>
                <div class="card-body">
                    <canvas id="indicadoresChart" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuários Online -->
    <div id="online-users" class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários Online</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush" id="online-users-list">
                @if(isset($onlineUsers['users']) && is_array($onlineUsers['users']) && count($onlineUsers['users']) > 0)
                @foreach($onlineUsers['users'] as $user)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $user['name'] ?? $user->name ?? 'Usuário Desconhecido' }} (ID: {{ $user['id'] ?? $user->id ?? 'N/A' }})</span>
                    <span class="badge bg-success rounded-pill">Online</span>
                </li>
                @endforeach
                @else
                <li class="list-group-item text-muted">Nenhum usuário online no momento.</li>
                @endif
            </ul>
        </div>
    </div>

    <div id="premiacoes" class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Premiações</h5>
            <a href="{{ route('dashboard.export_premiacoes') }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Exportar XLSX
            </a>
        </div>
        <div class="card-body">
            {!! $premiacoesTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>
    <div id="comentarios" class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Comentarios em Adivinhações</h5>
            <a href="{{ route('dashboard.export_comentarios') }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Exportar XLSX
            </a>
        </div>
        <div class="card-body">
            {!! $comentariosTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <div id="adivinhacoes-ativas" class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Adivinhações Ativas</h5>
            <a href="{{ route('dashboard.export_adivinhacoes_ativas') }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Exportar XLSX
            </a>
        </div>
        <div class="card-body">
            {!! $adivinhacoesAtivasTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <div id="respostas" class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Respostas Adivinhações Ativas</h5>
            <a href="{{ route('dashboard.export_respostas') }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Exportar XLSX
            </a>
        </div>
        <div class="card-body">
            {!! $respostasTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <!-- Tabela de usuários -->
    <div id="usuarios" class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Usuários</h5>
            <a href="{{ route('dashboard.export_users') }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Exportar XLSX
            </a>
        </div>
        <div class="card-body">
            {!! $usersTable->table(['class' => 'table table-striped table-hover'], true) !!}
        </div>
    </div>

    <!-- Floating Navigation Button -->
    <div class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1050;">
        <div class="dropdown">
            <button class="btn btn-primary btn-lg rounded-circle shadow" type="button" id="floatingNavButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-chevron-up"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="floatingNavButton">
                <li><a class="dropdown-item" href="#cards">Cards</a></li>
                <li><a class="dropdown-item" href="#charts">Gráficos</a></li>
                <li><a class="dropdown-item" href="#indicadores-chart">Indicadores</a></li>
                <li><a class="dropdown-item" href="#online-users">Usuários Online</a></li>
                <li><a class="dropdown-item" href="#premiacoes">Premiações</a></li>
                <li><a class="dropdown-item" href="#comentarios">Comentários</a></li>
                <li><a class="dropdown-item" href="#adivinhacoes-ativas">Adivinhações Ativas</a></li>
                <li><a class="dropdown-item" href="#respostas">Respostas</a></li>
                <li><a class="dropdown-item" href="#usuarios">Usuários</a></li>
            </ul>
        </div>
    </div>

</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
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
                                <span>${user.name || user['name'] || 'Usuário Desconhecido'} (ID: ${user.id || user['id'] || 'N/A'})</span>
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
$(document).on('click', '.marcar-pago-btn', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#marcarPago-' + id).modal('show');
});

$(document).on('click', '.deletar-btn', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#removePremiacao-' + id).modal('show');
});

$(document).on('submit', 'form[action*="/premiacoes/marcar-pago/"]', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(form[0]);
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#premiacoesTable').DataTable().ajax.reload();
            form.closest('.modal').modal('hide');
        },
        error: function(xhr) {
            alert('Erro ao marcar como pago: ' + xhr.responseText);
        }
    });
});

$(document).on('submit', 'form[action*="/premiacoes/deletar/"]', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
            $('#premiacoesTable').DataTable().ajax.reload();
        },
        error: function(xhr) {
            alert('Erro ao excluir: ' + xhr.responseText);
        }
    });
});
</script>

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

        // Indicadores Chart
        const ctxIndicadores = document.getElementById('indicadoresChart').getContext('2d');
        const indicadoresData = @json($topIndicadores);
        new Chart(ctxIndicadores, {
            type: 'bar',
            data: {
                labels: indicadoresData.map(item => item.name + ' (' + item.username + ')'),
                datasets: [{
                    label: 'Total Indicados',
                    data: indicadoresData.map(item => item.total_indicados),
                    backgroundColor: 'rgba(54, 162, 235, 1)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection