        <div class="timeline-item position-relative mb-5" style="z-index:1;" id="post-{{ $post->id}}">
            <div class="card shadow-lg border-0 timeline-card " style="min-width: 100%; max-width:100%;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $post->user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->username).'&background=random' }}"
                            class="rounded-circle me-3 border border-white shadow-sm" width="50" height="50" style="object-fit: cover;">
                        <div>
                            <strong class="abrir-perfil" data-username="{{ $post->user->username }}">{{ $post->user->username }}</strong><br>
                            <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if($post->content)
                    <p class="mb-3">{{ $post->content }}</p>
                    @endif

                    @if($post->file)
                    <div class="mb-3">
                        <img src="{{ $post->file }}" class="rounded shadow-sm" style="width: 300px; height: 200px; object-fit: cover;">
                    </div>
                    @endif

                    <div class="d-flex align-items-center gap-2 mt-1 mb-2">
                        @php
                        $userLiked = auth()->check() && $post->likes()->where('user_id', auth()->id())->exists();
                        $likesCount = $post->likes()->count();
                        @endphp

                        @auth
                        <button
                            class="btn btn-sm rounded-pill btn-like {{ $userLiked ? 'btn-danger' : 'btn-outline-primary' }}"
                            data-id="{{ $post->id }}">
                            <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                            <span class="likes-count">{{ $likesCount }}</span>
                        </button>
                        @else
                        <div class="btn btn-sm btn-outline-secondary rounded-pill disabled">
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span class="likes-count">{{ $likesCount }}</span>
                        </div>
                        @endauth
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-secondary btn-sm rounded-pill verComentarios"
                            data-id="{{ $post->id }}"
                            data-route="{{ route('posts.comments', $post->id) }}">
                            💬 Comentários
                        </button>
                        @if(auth()->user()->id == $post->user_id || auth()->user()->isAdmin())
                        <button class="btn btn-danger btn-sm rounded-pill apagarPost"
                            data-id="post-{{ $post->id }}"
                            data-route="{{ route('posts.delete', $post->id) }}">
                            Apagar
                        </button>
                        @endif
                        <!-- <div>
                            <button class="btn btn-danger btn-sm rounded-pill me-1">❤️ Curtir</button>
                            <button class="btn btn-primary btn-sm rounded-pill">🔗 Compartilhar</button>
                        </div> -->
                    </div>

                    <div id="comentarios-post-{{ $post->id }}" class="comentarios-box d-none mt-3 p-3 rounded-4 bg-light shadow-sm ">
                        <div class="comentarios-list small mb-3 text-dark">
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