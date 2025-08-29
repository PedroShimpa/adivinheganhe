@extends('layouts.app', ['enable_adsense' => true])

@section('content')
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
            @if($user->perfil_privado == 'S' && (!auth()->check()  || (auth()->id() != $user->id)))
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

    @if($user->perfil_privado == 'S' && (!auth()->check()  || (auth()->id() != $user->id)))
    <p class="card text-dark">Este perfil é privado.</p>
    @else
    <h5 class="fw-bold mb-3">Publicações</h5>

    @forelse($user->posts as $post)
    @include('partials.post')
    @empty
    <p class="card text-dark">Nenhuma publicação ainda.</p>
    @endforelse
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