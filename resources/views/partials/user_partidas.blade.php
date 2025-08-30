@foreach($userPartidas as $partida)
@php
    $oponente = $partida->partida->jogadores()
        ->where('user_id', '!=', $user->id)
        ->with('user')
        ->first();
@endphp

<a href="{{ route('competitivo.partida.finalizada', $partida->partida->uuid) }}"
    class="text-decoration-none text-reset partida-item">
    <div class="card border-0 shadow-sm mb-2 rounded-3 p-3 d-flex flex-row align-items-center justify-content-between hover-shadow-sm">
        <div class="d-flex align-items-center gap-3">
            @if($oponente && $oponente->user)
            <img src="{{ $oponente->user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($oponente->user->username).'&background=random' }}"
                alt="oponente"
                class="rounded-circle shadow-sm"
                width="48" height="48"
                style="object-fit: cover;">
            <div>
                <div class="fw-bold">{{ $oponente->user->username }}</div>
                <small class="text-muted">
                    {{ $partida->partida->created_at->format('d/m/Y H:i') }}
                </small>
            </div>
            @endif
        </div>

        <div class="text-center">
            <span class="badge bg-dark px-3 py-2">
                {{ $partida->partida->round_atual }} Rounds
            </span>
        </div>

        <div>
            @if($partida->vencedor == $user->id)
            <span class="badge bg-success px-3 py-2"><i class="bi bi-check2"></i> Vitória</span>
            @else
            <span class="badge bg-danger px-3 py-2"><i class="bi bi-x"></i> Derrota</span>
            @endif
        </div>
    </div>
</a>
@endforeach
