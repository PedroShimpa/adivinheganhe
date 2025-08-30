@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<style>
    .custom-tabs .nav-link {
        background-color: #0d6efd;
        /* azul intermediário (inativa) */
        color: #fff;
        border-radius: 0.5rem 0.5rem 0 0;
        margin-right: 4px;
        transition: background-color 0.2s ease-in-out;
        border: none;
        /* remove borda padrão do bootstrap */
    }

    .custom-tabs .nav-link:hover {
        background-color: #0b5ed7;
        /* hover */
        color: #fff;
    }

    .custom-tabs .nav-link.active {
        background-color: black;
        /* ativa */
        color: #fff;
        font-weight: bold;
        border: none !important;
    }
</style>
<div class="container mb-5 mt-2" style="max-width: 900px;">

    <div class="card shadow-sm border-0 overflow-hidden mb-4">
        <div class="position-relative">
            <div class="bg-primary" style="height: 200px;"></div>

            <div class="position-absolute top-100 start-50 translate-middle" style="margin-top: -80px;">
                <img src="{{ $user->image ? $user->image : 'https://ui-avatars.com/api/?name='.urlencode($user->username).'&background=random' }}"
                    alt="Foto de perfil"
                    class="rounded-circle border border-3 border-white shadow"
                    width="160" height="160" style="object-fit: cover;">
            </div>
        </div>
        <div class="card-body text-center mt-5">
            <h2 class="fw-bold mb-0">{{ $user->name }}</h2>
            <h6 class="fw-bold mb-0">Rating Competitivo: {{ $user->getOrCreateRank()->elo ?? ''}}</h6>
            <p class="text-muted mb-1">{{ '@'.$user->username }} ({{ $user->followers()->count()}} Seguidores)</p>

            @auth
            @if(auth()->user()->id === $user->id)
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil-square"></i> Editar Perfil
            </a>
            @endif

            @if(auth()->id() !== $user->id)
            @if($user->perfil_privado == 'N')
            @if($user->followers()->where('user_id', auth()->user()->id)->exists())
            <a href="{{ route('users.unfollow', $user->username) }}" class="btn btn-sm btn-danger">
                <i class="bi bi-person-dash"></i> Deixar de seguir
            </a>
            @else
            <a href="{{ route('users.follow', $user->username) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus"></i> Seguir
            </a>
            @endif
            @endif
            @endif
            @endauth

            @auth
            @if(auth()->id() !== $user->id)
            @php
            $isFriend = auth()->user()->friends()->contains(fn($f) => $f->id === $user->id);

            // Verifica se existe pedido pendente enviado pelo usuário logado
            $pendingRequest = auth()->user()->sentFriendships()
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->exists();
            @endphp

            @if($isFriend)
            <button class="btn btn-sm btn-success">
                <i class="bi bi-check2"></i> Amigo
            </button>
            @elseif($pendingRequest)
            <button class="btn btn-sm btn-warning" disabled>
                <i class="bi bi-clock"></i> Pedido enviado
            </button>
            @else
            <button
                class="btn btn-sm btn-primary sendFriendRequest"
                data-route="{{ route('users.friend_request', $user->username) }}">
                <i class="bi bi-person-plus"></i> Adicionar amigo
            </button>
            @endif
            @endif
            @endauth

            @auth
            @if(auth()->id() === $user->id)
            @php
            $pendingRequestsCount = $user->receivedFriendships()
            ->where('status', 'pending')
            ->count();
            @endphp

            <a href="{{ route('users.friend_requests') }}" class="btn btn-sm btn-warning position-relative">
                <i class="bi bi-person-plus"></i> Pedidos de amizade
                @if($pendingRequestsCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $pendingRequestsCount }}
                </span>
                @endif
            </a>
            @endif
            @endauth

        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Sobre</h5>
            @if($user->perfil_privado == 'S' && (!auth()->check() || (auth()->id() != $user->id)))
            <p class="mb-3">Este perfil é privado.</p>
            @else
            <p class="mb-3">
                {{ $user->bio ?: 'Ainda não escreveu nada sobre si mesmo.' }}
            </p>
            @endif
        </div>
    </div>

    @auth
    @if(auth()->id() === $user->id)
    @include('partials.post_store')
    @endif
    @endauth

    @if($user->perfil_privado == 'S' && (!auth()->check() || (auth()->id() != $user->id)))
    <div class="card shadow-lg border-0 timeline-card " style="min-width: 100%; max-width:100%;">
        <div class="card-body">
            <p class="m-3">Este perfil é privado.</p>
        </div>
    </div>
    @else
    <h5 class="fw-bold mb-3">Atividades</h5>

    <ul class="nav nav-tabs mb-3 custom-tabs" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts"
                type="button" role="tab" aria-controls="posts" aria-selected="true">
                Publicações
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history"
                type="button" role="tab" aria-controls="history" aria-selected="false">
                Histórico Competitivo
            </button>
        </li>
    </ul>
    <div class="tab-content" id="profileTabsContent">

        {{-- Aba de Publicações --}}
        <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
            @forelse($user->posts as $post)
            @include('partials.post')
            @empty
            <div class="card shadow-lg border-0 timeline-card " style="min-width: 100%; max-width:100%;">
                <div class="card-body">
                    <p class="m-3">Nenhuma publicação ainda.</p>
                </div>
            </div>
            @endforelse
        </div>
        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
            @forelse($user->partidas as $partida)
            @php
            $oponente = $partida->partida->jogadores()
            ->where('user_id', '!=', $user->id)
            ->with('user')
            ->first();
            @endphp

            <a href="{{ route('competitivo.partida.finalizada', $partida->partida->uuid) }}"
                class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm mb-2 rounded-3 p-3 d-flex flex-row align-items-center justify-content-between hover-shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        {{-- Avatar oponente --}}
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
            @empty
            <div class="card shadow-lg border-0 timeline-card " style="min-width: 100%; max-width:100%;">
                <div class="card-body text-center text-muted">
                    Nenhuma partida encontrada.
                </div>
            </div>
            @endforelse
        </div>


    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.sendFriendRequest').on('click', async function() {
            const $btn = $(this);
            const route = $btn.data('route');

            $btn.prop('disabled', true);

            try {
                const res = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                if (res.ok) {
                    // Alterar botão para "Pedido enviado" em amarelo
                    $btn.removeClass('btn-primary')
                        .addClass('btn-warning')
                        .html('<i class="bi bi-clock"></i> Pedido enviado');
                } else {
                    alert('Erro ao enviar pedido de amizade.');
                    $btn.prop('disabled', false);
                }
            } catch (e) {
                console.error(e);
                alert('Erro ao enviar pedido de amizade.');
                $btn.prop('disabled', false);
            }
        });
    });

    $('.verComentarios').on('click', async function() {
        const postId = $(this).data('id');
        const route = $(this).data('route');
        const $box = $(`#comentarios-post-${postId}`);
        const $list = $box.find('.comentarios-list');

        if ($box.hasClass('d-none')) {
            $box.removeClass('d-none animate__fadeOut').addClass('animate__fadeIn');

            try {
                const res = await fetch(route, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (data.length > 0) {
                    let html = '';
                    data.forEach(c => {
                        html += adicionarComentario(c, true);
                    });
                    $list.html(html);
                } else {
                    // $list.html('<p class="text-muted">Nenhum comentário ainda. Seja o primeiro!</p>');
                }
            } catch (e) {
                $list.html('<p class="text-danger">Erro ao carregar comentários.</p>');
            }

        } else {
            $box.removeClass('animate__fadeIn').addClass('animate__fadeOut');
            setTimeout(() => $box.addClass('d-none'), 300);
        }
    });

    $('.sendComment').on('click', async function() {
        const postId = $(this).data('id');
        const route = $(this).data('route');
        const $input = $(`#comentario-input-${postId}`);
        const body = $input.val().trim();

        if (!body) return;

        $(this).attr('disabled', true)

        try {
            const res = await fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    body
                })
            });

            $input.val('');
        } catch (e) {
            alert('Erro ao enviar comentário');
        } finally {
            $(this).attr('disabled', false)
        }
    });


    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', function() {
            const adivinhacaoId = this.dataset.id;
            const btn = this;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/posts/${adivinhacaoId}/toggle-like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({})
                })
                .then(res => res.json())
                .then(data => {
                    // Atualiza count
                    btn.querySelector('.likes-count').textContent = data.likes_count;

                    // Alterna ícone e cor do botão
                    const icon = btn.querySelector('i');
                    if (data.liked) {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-danger');
                        icon.classList.remove('bi-hand-thumbs-up');
                        icon.classList.add('bi-hand-thumbs-up-fill');
                    } else {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-outline-primary');
                        icon.classList.remove('bi-hand-thumbs-up-fill');
                        icon.classList.add('bi-hand-thumbs-up');
                    }
                })
                .catch(err => console.error(err));
        });
    });
</script>
@endpush