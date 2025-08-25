@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container py-4" style="max-width: 1000px;">

    <h2 class="fw-bold mb-4 text-center">
        <i class="bi bi-people"></i> Jogadores
    </h2>

    @if($players->count())
        <div class="row g-4">
            @foreach($players as $player)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden player-card hover-shadow">
                        <div class="card-body text-center p-4">
                            <a href="{{ route('profile.view', $player->username) }}" class="text-decoration-none">
                                <img src="{{ $player->image ? $player->image : 'https://ui-avatars.com/api/?name='.urlencode($player->username).'&background=random' }}"
                                    alt="{{ $player->username }}"
                                    class="rounded-circle shadow mb-3"
                                    width="100" height="100"
                                    style="object-fit: cover;">

                                <h5 class="fw-bold text-dark mb-1">{{ '@'.$player->username }}</h5>
                            </a>
                            <p class="text-dark small mb-3" style="min-height: 40px;">
                                {{ $player->bio ? Str::limit($player->bio, 80) : 'Ainda não escreveu nada...' }}
                            </p>

                            <a href="{{ route('profile.view', $player->username) }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                Ver Perfil
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-4 text-white">
            {{ $players->links() }}
        </div>
    @else
        <p class="text-center text-white">Nenhum jogador disponível no momento.</p>
    @endif

</div>

@push('styles')
<style>
    .player-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .player-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush
@endsection
