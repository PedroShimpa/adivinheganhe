@auth
{{-- Sidebar --}}
<div id="friendsSidebar" class="friends-sidebar shadow-lg rounded-start position-fixed d-flex flex-column">
    <div id="friendsHeader" class="d-flex justify-content-between align-items-center p-3">
        <h6 class="m-0">Amigos Online (<span id="friendsCount">{{ auth()->user()->onlineFriends()->count() }}</span>)</h6>
        <button id="friendsToggleBtn" class="btn btn-sm btn-primary d-none d-md-block">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <div id="friendsBody" class="flex-grow-1 overflow-auto px-2 pb-3">
        @if(auth()->user()->onlineFriends()->count())
        <ul class="list-unstyled m-0 p-0">
            @foreach(auth()->user()->onlineFriends() as $friend)
            <li class="d-flex align-items-center gap-2 py-2 px-2 friend-item rounded hover-glow"
                style="cursor: pointer;"
                data-id="{{ $friend->id }}"
                data-name="{{ $friend->name }}">
                <img src="{{ $friend->image ?? 'https://ui-avatars.com/api/?name='.urlencode($friend->username).'&background=random' }}"
                     alt="{{ $friend->name }}"
                     class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                <span class="flex-grow-1">{{ $friend->name }}</span>
                <span class="badge bg-success rounded-circle" title="Online" style="width:10px;height:10px;"></span>
            </li>
            @endforeach
        </ul>
        @else
        <div class="alert alert-warning small rounded-3 mt-2 px-3">
            Nenhum amigo online no momento
        </div>
        @endif
    </div>
</div>

{{-- Botão flutuante para celular --}}
<button id="friendsMobileBtn" class="btn btn-primary d-md-none friends-mobile-btn">
    <i class="bi bi-people-fill"></i>
    <span class="badge bg-light text-dark" id="mobileFriendsCount">{{ auth()->user()->onlineFriends()->count() }}</span>
</button>

<style>
    /* Sidebar Geral */
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
        border-left: 1px solid rgba(255,255,255,0.1);
    }

    /* Header */
    #friendsHeader {
        cursor: pointer;
        user-select: none;
    }

    /* Amigos */
    .friend-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    .hover-glow:hover {
        box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
    }

    /* Botão flutuante celular */
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
    .friends-mobile-btn .badge {
        font-size: 0.8rem;
    }

    /* Mobile */
    @media (max-width: 768px) {
        .friends-sidebar {
            transform: translateX(100%);
            position: fixed;
            width: 80vw;
        }

        .friends-sidebar.open {
            transform: translateX(0);
        }

        #friendsBody {
            padding: 0.5rem 1rem 1rem 1rem !important;
        }

        .friend-item img {
            width: 35px;
            height: 35px;
        }
    }
</style>

<script>
    $(function() {
        const $sidebar = $('#friendsSidebar');
        const $toggleBtn = $('#friendsToggleBtn');
        const $mobileBtn = $('#friendsMobileBtn');
        const $friendsBody = $('#friendsBody');

        // Inicializa estado desktop
        if(window.innerWidth >= 768){
            const savedState = localStorage.getItem('friendsSidebarState');
            if(savedState === 'open'){
                $friendsBody.show();
            } else {
                $friendsBody.hide();
            }
        }

        // Toggle desktop
        $toggleBtn.on('click', function() {
            $friendsBody.toggle();
            localStorage.setItem('friendsSidebarState', $friendsBody.is(':visible') ? 'open' : 'closed');
        });

        // Toggle mobile
        $mobileBtn.on('click', function() {
            $sidebar.toggleClass('open');
        });
    });
</script>
@endauth
