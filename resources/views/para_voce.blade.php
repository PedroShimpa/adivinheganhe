@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container">

    @include('partials.post_store')

    <div class="timeline position-relative" id="timeline">
        <div class="timeline-line position-absolute top-0 start-50 translate-middle-x bg-secondary" style="width:4px; height:100%;"></div>

        @forelse($posts as $post)
        @include('partials.post')
        @empty
        <p class="text-white text-center">Nenhuma publicação ainda.</p>
        @endforelse
    </div>

    @if($posts->hasMorePages())
    <div class="text-center my-4">
        <button id="loadMorePosts" class="btn btn-outline-primary px-4" data-next-page="{{ $posts->nextPageUrl() }}">
            Ver mais
        </button>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    $('#loadMorePosts').on('click', async function() {
        const $btn = $(this);
        const nextPageUrl = $btn.data('next-page');

        if (!nextPageUrl) return;

        $btn.attr('disabled', true).text('Carregando...');

        try {
            const res = await fetch(nextPageUrl, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();

            if (data.html) {
                $('#timeline').append(data.html);

                if (data.next_page_url) {
                    $btn.data('next-page', data.next_page_url).attr('disabled', false).text('Ver mais');
                } else {
                    $btn.remove(); 
                }
            }
        } catch (e) {
            console.error(e);
            $btn.attr('disabled', false).text('Ver mais');
        }
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