        <div class="timeline-item position-relative mb-5" style="z-index:1;">
            <div class="card shadow-lg border-0 timeline-card mx-auto" style="max-width:700px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($user->username).'&background=random' }}"
                            class="rounded-circle me-3 border border-white shadow-sm" width="50" height="50" style="object-fit: cover;">
                        <div>
                            <strong>{{ $user->name }}</strong><br>
                            <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if($post->content)
                    <p class="mb-3">{{ $post->content }}</p>
                    @endif

                    @if($post->file)
                    <div class="mb-3">
                        <img src="{{$post->file }}" class="img-fluid rounded shadow-sm">
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-secondary btn-sm rounded-pill verComentarios"
                            data-id="{{ $post->id }}"
                            data-route="{{ route('posts.comments', $post->id) }}">
                            💬 Comentários
                        </button>
                        <!-- <div>
                            <button class="btn btn-danger btn-sm rounded-pill me-1">❤️ Curtir</button>
                            <button class="btn btn-primary btn-sm rounded-pill">🔗 Compartilhar</button>
                        </div> -->
                    </div>

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