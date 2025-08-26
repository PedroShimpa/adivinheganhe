@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container " style="max-width: 900px;">

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
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">
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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Sobre</h5>
            <p class="mb-3">
                {{ $user->bio ?: 'Ainda não escreveu nada sobre si mesmo.' }}
            </p>
        </div>
    </div>

    @auth
    @if(auth()->id() === $user->id)
    @include('partials.post_store')
    @endif
    @endauth

    {{-- Timeline de posts --}}
    <h5 class="fw-bold mb-3">Publicações</h5>

    @forelse($user->posts as $post)
    @include('partials.post')
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