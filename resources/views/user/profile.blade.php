@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container py-4" style="max-width: 900px;">

    {{-- Header do perfil --}}
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
            <p class="text-muted mb-1">{{ '@'.$user->username }} ({{ $user->followers()->count()}} Seguidores)</p>

            @auth
            @if(auth()->user()->id === $user->id)
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Editar Perfil
            </a>
            @endif

            @if(auth()->id() !== $user->id)
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
            @endauth

        </div>
    </div>

    {{-- Sobre --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Sobre</h5>
            <p class="mb-3">
                {{ $user->bio ?: 'Ainda não escreveu nada sobre si mesmo.' }}
            </p>
        </div>
    </div>

    {{-- Criar novo post (apenas dono do perfil) --}}
    @auth
    @if(auth()->id() === $user->id)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror"
                        rows="3" placeholder="O que você está pensando?">{{ old('content') }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror">
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endauth

    {{-- Timeline de posts --}}
    <h5 class="fw-bold mb-3">Publicações</h5>

    @forelse($user->posts as $post)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ $user->image ? $user->image : 'https://ui-avatars.com/api/?name='.urlencode($user->username).'&background=random' }}"
                    class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                <div>
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-white">{{ $post->created_at->diffForHumans() }}</small>
                </div>
            </div>

            {{-- Conteúdo do post --}}
            @if($post->content)
            <p class="mb-2">{{ $post->content }}</p>
            @endif

            {{-- Arquivo/imagem do post --}}
            @if($post->file)
            <div class="mb-2">
                <img src="{{$post->file }}" class="img-fluid rounded">
            </div>
            @endif

            <div class="mt-3">
                <button class="btn btn-secondary btn-sm rounded-pill verComentarios"
                    data-id="{{ $post->id }}"
                    data-route="{{ route('posts.comments', $post->id) }}">
                    💬 Comentários
                </button>

                <div id="comentarios-post-{{ $post->id }}" class="comentarios-box d-none mt-3 p-3 rounded-4 bg-light shadow-sm animate__animated">
                    <div class="comentarios-list small mb-3 text-dark">
                        <p class="text-muted">Carregando comentários...</p>
                    </div>

                    @auth
                    <div class="input-group">
                        <input type="text" id="comentario-input-{{ $post->id }}" class="form-control rounded-start-pill" placeholder="💬 Escreva um comentário...">
                        <button class="btn btn-primary rounded-end-pill sendComment"
                            data-id="{{ $post->id }}"
                            data-route="{{ route('posts.comment', $post->id) }}">
                            Enviar
                        </button>
                    </div>
                    @else
                    <div class="alert alert-warning small rounded-3 mt-2">
                        Você precisa <a href="{{ route('login') }}" class="fw-semibold text-decoration-underline">entrar</a> para comentar.
                    </div>
                    @endauth
                </div>
            </div>
        </div>

    </div>
    @empty
    <p class="text-white">Nenhuma publicação ainda.</p>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
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
                    $list.html('<p class="text-muted">Nenhum comentário ainda. Seja o primeiro!</p>');
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
</script>
@endpush