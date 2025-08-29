@auth
@php
$friends = auth()->user()->friends();
@endphp

{{-- Sidebar de amigos --}}
<div id="friendsSidebar" class="friends-sidebar shadow-lg rounded-start position-fixed d-flex flex-column">
    <div id="friendsHeader" class="d-flex justify-content-between align-items-center p-3">
        <h6 class="m-0">Amigos (<span id="friendsCount">{{$friends->count()}}</span>)</h6>
        <button id="friendsToggleBtn" class="btn btn-sm btn-primary d-none d-md-block">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <div id="friendsBody" class="flex-grow-1 overflow-auto px-2 pb-3">
        @if($friends->count())
            <ul class="list-unstyled m-0 p-0">
                @foreach($friends as $friend)
                    <li class="d-flex align-items-center gap-2 py-2 px-2 friend-item rounded hover-glow"
                        style="cursor: pointer;"
                        data-id="{{ $friend->id }}"
                        data-username="{{ $friend->username }}"
                        data-name="{{ $friend->name }}">
                        
                        <img src="{{ $friend->image ?? 'https://ui-avatars.com/api/?name='.urlencode($friend->username).'&background=random' }}"
                             alt="{{ $friend->username }}"
                             class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                        <span class="flex-grow-1">{{ $friend->username }}</span>
                        <span class="badge bg-danger unread-badge d-none" id="mensagem-recebida-{{ $friend->id }}"></span>
                        <span class="badge bg-success rounded-circle" title="Online" style="width:10px;height:10px;"></span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="alert alert-warning small rounded-3 mt-2 px-3">
                Nenhum amigo ainda...
            </div>
        @endif
    </div>
</div>

{{-- Botão flutuante mobile --}}
<button id="friendsMobileBtn" class="btn btn-primary d-md-none friends-mobile-btn">
    <i class="bi bi-people-fill"></i>
    <span class="badge bg-light text-dark" id="mobileFriendsCount">{{ $friends->count() }}</span>
</button>

{{-- Balão de ações --}}
<div id="friendBalloon" class="friend-balloon card shadow-lg d-none">
    <div class="card-body p-2">
        <button class="btn btn-sm w-100 mb-1 btn-outline-primary open-profile">Ver perfil</button>
        <button class="btn btn-sm w-100 btn-primary open-chat">Abrir chat</button>
    </div>
</div>

{{-- CSS --}}
<style>
/* Sidebar */
.friends-sidebar {
    top: 1rem;
    right: 0;
    width: 250px;
    max-height: 90vh;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(255, 255, 255, 0.1);
}

/* Scroll suave mobile */
.friends-sidebar .overflow-auto {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

/* Botão flutuante */
.friends-mobile-btn {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    z-index: 1100;
    border-radius: 50px;
    padding: 0.5rem 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-weight: bold;
}

/* Balão */
.friend-balloon {
    position: absolute;
    z-index: 1200;
    min-width: 140px;
    border-radius: 12px;
    background: #fff;
}

/* Mobile */
@media (max-width: 768px) {
    .friends-sidebar {
        transform: translateX(100%);
        position: fixed;
        top: 0;
        right: 0;
        width: 80vw;
        height: 100vh; /* ocupa toda a tela */
        max-height: none;
    }

    .friends-sidebar.open {
        transform: translateX(0);
    }

    body.no-scroll {
        overflow: hidden;
    }
}
</style>

{{-- JS --}}
<script>
$(function() {
    const $sidebar = $('#friendsSidebar');
    const $toggleBtn = $('#friendsToggleBtn');
    const $mobileBtn = $('#friendsMobileBtn');
    const $friendsBody = $('#friendsBody');
    const $balloon = $('#friendBalloon');

    // Restaurar estado desktop
    const savedState = localStorage.getItem('friendsSidebarState');
    if (savedState === 'closed') {
        $friendsBody.hide();
    } else {
        $friendsBody.show();
    }

    // Toggle desktop
    $toggleBtn.on('click', function() {
        $friendsBody.toggle();
        localStorage.setItem(
            'friendsSidebarState',
            $friendsBody.is(':visible') ? 'open' : 'closed'
        );
    });

    // Toggle mobile
    $mobileBtn.on('click', function() {
        $sidebar.toggleClass('open');
        if ($sidebar.hasClass('open')) {
            $('body').addClass('no-scroll');
        } else {
            $('body').removeClass('no-scroll');
        }
    });

    // Clique em amigo => abre balão
    $(document).on('click', '.friend-item', function(e) {
        e.stopPropagation();
        const friendId = $(this).data('id');
        const friendUsername = $(this).data('username');

        let offset = $(this).offset();
        let balloonTop = offset.top;
        let balloonLeft = offset.left - $balloon.outerWidth() - 10;

        // Se estiver mobile, centraliza na tela
        if ($(window).width() <= 768) {
            balloonTop = $(window).scrollTop() + 100;
            balloonLeft = ($(window).width() - $balloon.outerWidth()) / 2;
        }

        $balloon.css({
            top: balloonTop + 'px',
            left: balloonLeft + 'px'
        }).removeClass('d-none')
          .data('username', friendUsername)
          .data('id', friendId);
    });

    // Ações do balão
    $balloon.find('.open-profile').on('click', function() {
        const username = $balloon.data('username');
        window.location.href = `/jogadores/${username}`;
    });
    $balloon.find('.open-chat').on('click', function() {
        const username = $balloon.data('username');
        window.location.href = `/chat/${username}`;
    });

    // Fecha balão ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.friend-item, #friendBalloon, #friendsMobileBtn').length) {
            $balloon.addClass('d-none');
            $sidebar.removeClass('open');
            $('body').removeClass('no-scroll');
        }
    });
});
</script>
@endauth
