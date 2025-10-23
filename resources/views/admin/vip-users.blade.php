@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">

    <div class="mb-4">
        <h1 class="h3">Usuários VIP</h1>
        <p class="text-white">Lista de usuários com assinatura VIP ativa</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Usuários VIP Ativos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Data de Expiração</th>
                            <th>Dias Restantes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vipUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ '@'.$user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->membership_expires_at ? $user->membership_expires_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                @if($user->membership_expires_at)
                                    @php
                                        $daysLeft = round(now()->diffInDays($user->membership_expires_at, false));
                                    @endphp
                                    @if($daysLeft > 0)
                                        <span class="badge bg-success rounded-pill">{{ $daysLeft }} dias</span>
                                    @elseif($daysLeft == 0)
                                        <span class="badge bg-warning rounded-pill">Expira hoje</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill">Expirado</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary rounded-pill">N/A</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('profile.view', $user->username) }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="bi bi-eye"></i> Ver Perfil
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Nenhum usuário VIP ativo encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
