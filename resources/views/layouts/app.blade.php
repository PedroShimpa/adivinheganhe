<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @isset($enable_adsense)
    <meta name="google-adsense-account" content="{{ env('GOOGLE_ADSENSE_TAG', 'ca-pub-2128338486173774') }}" />
    @endisset

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6a1b9a">

    <!-- iOS support -->
    <link rel="apple-touch-icon" href="/icons/icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    @stack('head-content')
    <title>{{ $title ?? env('APP_NAME', 'Adivinhe e Ganhe') }}</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">


    <link rel="preload" href="{{ asset('vendor/mdb/mdb.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('vendor/mdb/mdb.min.css') }}">

    <!-- <link rel="preload" href="{{ asset('vendor/animate/animate.min.css') }}" as="style"> -->
    <link rel="stylesheet" href="{{ asset('vendor/animate/animate.min.css') }}" media="print" onload="this.media='all'">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" media="print" onload="this.media='all'">

    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </noscript>
    <style>
        .nav-link.active {
            background-color: rgba(13, 110, 253, 0.15);
            /* azul clarinho */
            border-radius: 8px;
        }

        img {
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
            pointer-events: none;
            /* impede clique */
        }

        html,
        body {
            height: 100%;
            min-height: 100vh;
            /* Garante altura mínima da tela */
            margin: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(145deg, #0f0c29, #302b63, #24243e);
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-size: cover;
            color: #fff;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1 0 auto;
            padding-top: 4.5rem;
            /* desktop */
        }



        .alert-partida {
            margin-top: 4.5rem;
            /* altura aproximada da navbar mobile */
        }

        @media (min-width: 992px) {
            .alert-partida {
                margin-top: 1rem;
                /* menos no desktop, já que navbar está escondida */
            }
        }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-glow {
            transition: 0.3s ease;
            box-shadow: 0 0 10px #00f7ff;
        }

        .btn-glow:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px #00f7ff, 0 0 30px #00f7ff;
        }

        .nav-link {
            color: #fff !important;
        }

        .nav-link:hover {
            color: #00f7ff !important;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.7);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
    </style>

    @stack('head-scripts')
</head>

<body class="bg-dark text-white d-flex flex-column min-vh-100">
    <div class="offcanvas offcanvas-start bg-dark text-white sidebar-nav" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold">Adivinhe e Ganhe</h5>
            <div class="d-flex align-items-center gap-3">

                @auth
                <div class="dropdown">
                    <button id="notificationButton" class="btn btn-light ml-2" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-4"></i>
                        <span id="notificationCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ auth()->user()->unreadNotificationsCount()}}
                        </span>
                    </button>
                    <ul id="notificationList" class="dropdown-menu dropdown-menu-end p-2" style="min-width: 300px;">
                        <li class="text-center text-muted">Carregando...</li>
                    </ul>
                </div>
                @endauth


            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-2">

            {{-- ================== Jogar ================== --}}
            <a href="#collapsePlay"
                class="nav-link text-white"
                type="button"
                data-bs-toggle="collapse"
                aria-expanded="{{ request()->routeIs('home','competitivo.index','adivinhe_o_milhao.index') ? 'true' : 'false' }}">
                <i class="bi bi-controller me-2"></i> Jogar
            </a>
            <div class="accordion" id="playAccordion">
                <div class="accordion-item bg-dark border-0">
                    <div id="collapsePlay"
                        class="accordion-collapse collapse {{ request()->routeIs('home','competitivo.index','adivinhe_o_milhao.index') ? 'show' : '' }}"
                        data-bs-parent="#playAccordion">
                        <a href="{{ route('home') }}"
                            class="nav-link text-white {{ request()->routeIs('home') ? 'active fw-bold text-primary' : '' }}">
                            Clássico
                        </a>
                        {{-- <a href="{{ route('regioes.index') }}" class="nav-link text-white">Clássico por região</a> --}}
                        @if(config('app.competitivo_mode_enabled'))
                        <a href="{{ route('competitivo.index') }}"
                            class="nav-link text-white {{ request()->routeIs('competitivo.index') ? 'active fw-bold text-primary' : '' }}">
                            Competitivo
                        </a>
                        @endif
                        <a href="{{ route('adivinhe_o_milhao.index') }}"
                            class="nav-link text-white {{ request()->routeIs('adivinhe_o_milhao.index') ? 'active fw-bold text-primary' : '' }}">
                            Adivinhe o Milhão
                        </a>
                        <a href="{{ route('adivinhacoes.vip') }}"
                            class="nav-link text-white {{ request()->routeIs('adivinhacoes.vip') ? 'active fw-bold text-primary' : '' }}">
                            Adivinhações VIP
                        </a>
                    </div>
                </div>
            </div>

            {{-- ================== Comunidade ================== --}}
            <a href="#collapseComunidade"
                class="nav-link text-white"
                type="button"
                data-bs-toggle="collapse"
                aria-expanded="{{ request()->routeIs('para_voce','jogadores','premiacoes','ranking_classico') ? 'true' : 'false' }}">
                <i class="bi bi-people"></i> Comunidade
            </a>
            <div class="accordion" id="playersAccordion">
                <div class="accordion-item bg-dark border-0">
                    <div id="collapseComunidade"
                        class="accordion-collapse collapse {{ request()->routeIs('para_voce','jogadores','premiacoes','ranking_classico') ? 'show' : '' }}"
                        data-bs-parent="#playersAccordion">

                        @auth
                        <a href="{{ route('para_voce') }}"
                            class="nav-link text-white {{ request()->routeIs('para_voce') ? 'active fw-bold text-primary' : '' }}">
                            Para Você
                        </a>
                        @endauth

                        <a href="{{ route('jogadores') }}"
                            class="nav-link text-white {{ request()->routeIs('jogadores') ? 'active fw-bold text-primary' : '' }}">
                            Jogadores
                        </a>
                        <a href="{{ route('premiacoes') }}"
                            class="nav-link text-white {{ request()->routeIs('premiacoes') ? 'active fw-bold text-primary' : '' }}">
                            Prêmios
                        </a>
                        <a href="{{ route('ranking_classico') }}"
                            class="nav-link text-white {{ request()->routeIs('ranking_classico') ? 'active fw-bold text-primary' : '' }}">
                            Ranking - Modo Clássico
                        </a>
                    </div>
                </div>
            </div>

            {{-- ================== Links soltos ================== --}}
            <a href="{{ route('membership.index') }}"
                class="nav-link text-white {{ request()->routeIs('membership.index') ? 'active fw-bold text-primary' : '' }}">
                <i class="bi bi-star"></i> Seja VIP
            </a>
            <a href="{{ route('sobre') }}"
                class="nav-link text-white {{ request()->routeIs('sobre') ? 'active fw-bold text-primary' : '' }}">
                <i class="bi bi-info-circle"></i> Sobre
            </a>

            <a href="{{ route('suporte.index') }}"
                class="nav-link text-white {{ request()->routeIs('suporte.index') ? 'active fw-bold text-primary' : '' }}">
                <i class="bi bi-life-preserver"></i> Suporte
            </a>

            <hr class="border-secondary">

            {{-- ================== Usuário autenticado ================== --}}
            @auth
            <a href="{{ route('profile.view', auth()->user()->username) }}"
                class="nav-link text-white {{ request()->routeIs('profile.view') ? 'active fw-bold text-primary' : '' }}">
                <i class="bi bi-person-circle"></i> Meu Perfil
            </a>
            <a href="{{ route('meus_premios') }}"
                class="nav-link text-white {{ request()->routeIs('meus_premios') ? 'active fw-bold text-primary' : '' }}">
                <i class="bi bi-gift"></i> Meus Prêmios
            </a>
            <a href="{{ route('suporte.user.index') }}"
                class="nav-link text-white {{ request()->routeIs('suporte.user.index') ? 'active fw-bold text-primary' : '' }}">
                <i class="bi bi-ticket"></i> Meus Chamados
            </a>

            @php
            $indicatedCount = \App\Models\User::where('indicated_by', auth()->user()->uuid)->count();
            @endphp
            @if($indicatedCount > 0)
            <p class="text-white small mt-2 mb-0">
                <i class="bi bi-people-fill"></i> Você indicou {{ $indicatedCount }} jogadores
            </p>
            @endif

            {{-- ================== Admin ================== --}}
            @if(auth()->user()->isAdmin())
            <a href="#collapseAdmin"
                class="nav-link text-white"
                type="button"
                data-bs-toggle="collapse"
                aria-expanded="{{ request()->routeIs('dashboard','adivinhacoes.expiradas','adivinhacoes.new','adivinhe_o_milhao.create_pergunta','competitivo.store_pergunta') ? 'true' : 'false' }}">
                <i class="bi bi-gear"></i> Admin
            </a>
            <div class="accordion" id="adminAccordion">
                <div class="accordion-item bg-dark border-0">
                    <div id="collapseAdmin"
                        class="accordion-collapse collapse {{ request()->routeIs('dashboard','adivinhacoes.expiradas','adivinhacoes.new','adivinhe_o_milhao.create_pergunta','competitivo.store_pergunta') ? 'show' : '' }}"
                        data-bs-parent="#adminAccordion">

                        <a href="{{ route('dashboard') }}"
                            class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active fw-bold text-primary' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('adivinhacoes.expiradas') }}"
                            class="nav-link text-white {{ request()->routeIs('adivinhacoes.expiradas') ? 'active fw-bold text-primary' : '' }}">
                            Expiradas
                        </a>
                        <a href="{{ route('adivinhacoes.new') }}"
                            class="nav-link text-white {{ request()->routeIs('adivinhacoes.new') ? 'active fw-bold text-primary' : '' }}">
                            Nova Adivinhação
                        </a>
                        <a href="{{ route('adivinhe_o_milhao.create_pergunta') }}"
                            class="nav-link text-white {{ request()->routeIs('adivinhe_o_milhao.create_pergunta') ? 'active fw-bold text-primary' : '' }}">
                            Nova Pergunta AOM
                        </a>
                        <a href="{{ route('competitivo.store_pergunta') }}"
                            class="nav-link text-white {{ request()->routeIs('competitivo.store_pergunta') ? 'active fw-bold text-primary' : '' }}">
                            Nova Pergunta Comp
                        </a>
                        <a href="{{ route('suporte.admin.index') }}"
                            class="nav-link text-white {{ request()->routeIs('suporte.admin.index') ? 'active fw-bold text-primary' : '' }}">
                           Atender Chamados
                        </a>
                        <a href="{{ route('conectar.whatsapp') }}"
                            class="nav-link text-white {{ request()->routeIs('conectar.whatsapp') ? 'active fw-bold text-primary' : '' }}">
                            Conectar WhatsApp
                        </a>
                     
                    </div>
                </div>
            </div>
            @endif

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
            @else
            {{-- Login --}}
            <a href="{{ route('login') }}" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Entrar
            </a>
            @endauth
        </div>

    </div>

    <nav class="navbar navbar-dark bg-dark fixed-top d-lg-none px-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold text-white" href="{{ route('home') }}">
                🎮 {{ env('APP_NAME', 'Adivinhe e Ganhe') }}
            </a>
            <button class="btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <i class="bi bi-list fs-3"></i>
            </button>
        </div>
    </nav>


    <main class="flex-grow-1 pt-5 mt-3">
        @auth
        @php
        $partidaEmAndamento = auth()->user()->partidaEmAndamento;
        $rotaAtual = Route::currentRouteName();
        @endphp

        @if($partidaEmAndamento && $rotaAtual !== 'competitivo.partida')
        <div class="alert alert-warning text-center shadow-lg rounded-4 py-4 mb-4" role="alert" style="font-size: 1.5rem;">
            ⚠️ Você tem uma partida em andamento!
            <a href="{{ route('competitivo.partida', $partidaEmAndamento->partida->uuid) }}"
                class="btn btn-primary btn-lg ms-3">
                Voltar para a partida
            </a>
        </div>
        @endif
        @endauth
        @yield('content')
    </main>

    <footer class="text-center  mt-auto bg-dark ">
        <h5 class="fw-bold mt-2">Adivinhe e Ganhe</h5>
        <p class="text-light mb-1">Projeto de código aberto criado e mantido por <span class="text-info">Pedro "Shimpa" Falconi</span></p>
        <a href="https://github.com/PedroShimpa/adivinheganhe" class="text-warning text-decoration-none" target="_blank">
            github.com/PedroShimpa/adivinheganhe
        </a>
    </footer>

    <style>
        .sidebar-nav .nav-link {
            padding: 0.7rem 1rem;
            border-radius: .5rem;
            transition: background 0.2s;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #00f7ff !important;
        }

        @media (min-width: 992px) {
            .offcanvas-header .btn-close {
                display: none !important;
            }
        }

        @media (min-width: 992px) {
            .offcanvas-start {
                position: fixed;
                top: 0;
                bottom: 0;
                transform: none !important;
                visibility: visible !important;
                border-right: 1px solid rgba(255, 255, 255, 0.1);
                width: 250px !important;
            }

            main {
                margin-left: 250px;
            }

            .navbar {
                display: none;
            }
        }
    </style>

    {{-- Modais --}}
    @include('partials.modals')
    @stack('extra_modais')

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>

    @include('partials.socket')
    @include('partials.friends_bar')

    @if(env('GOOGLE_ANALYTICS_TAG'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_TAG') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', "{{ env('GOOGLE_ANALYTICS_TAG') }}");
    </script>
    @endif
    @auth
    <script>
        $(document).ready(function() {
            const $notifButton = $('#notificationButton');
            const $notifList = $('#notificationList');
            const $notifCount = $('#notificationCount');

            $notifButton.on('click', function() {
                $.ajax({
                    url: "{{ route('user.notificacoes') }}",
                    method: 'GET',
                    success: function(res) {
                        $notifList.empty();

                        if (res.length === 0) {
                            $notifList.append('<li class="text-center text-muted">Sem notificações</li>');
                        } else {
                            res.forEach(n => {
                                let content = '';

                                if (n.data.url) {
                                    content = `
                        <a href="${n.data.url}" class="dropdown-item d-block">
                            ${n.data.message}
                            <br><small class="text-muted">${n.created_at_br}</small>
                        </a>
                    `;
                                } else {
                                    content = `
                        <li class="dropdown-item">
                            ${n.data.message}
                            <br><small class="text-muted">${n.created_at_br}</small>
                        </li>
                    `;
                                }

                                $notifList.append(content);
                            });
                        }

                        $notifCount.text(0);
                    },
                    error: function() {
                        $notifList.html('<li class="text-center text-danger">Erro ao carregar</li>');
                    }
                });

            });

            $('.apagarPost').on('click', function(e) {
                e.preventDefault();

                let rota = $(this).data('route');
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Essa ação não pode ser desfeita!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: rota,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Excluído!',
                                    text: 'O post foi deletado com sucesso.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                // remove o elemento da tela
                                $(`#${id}`).fadeOut(200, function() {
                                    $(this).remove();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro!',
                                    text: 'Não foi possível excluir. Tente novamente.'
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
    <script>
        $(document).on('click', '.abrir-perfil', function() {
            let username = $(this).data('username');
            window.open(`/jogadores/${username}`, '_blank');
        });
    </script>

    @endauth

    @auth
        @if(!auth()->user()->isVip())
            @isset($enable_adsense)
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{  env('GOOGLE_ADSENSE_TAG', 'ca-pub-2128338486173774')}}"
                crossorigin="anonymous"></script>
            @endisset
        @endif
    @else
        @isset($enable_adsense)
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{  env('GOOGLE_ADSENSE_TAG', 'ca-pub-2128338486173774')}}"
            crossorigin="anonymous"></script>
        @endisset
    @endauth

    @stack('scripts')
</body>


</html>