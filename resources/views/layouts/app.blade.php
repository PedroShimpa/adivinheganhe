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

    <link rel="preload" href="{{ asset('vendor/animate/animate.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('vendor/animate/animate.min.css') }}" media="print" onload="this.media='all'">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" media="print" onload="this.media='all'">

    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </noscript>

    <style>
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

    {{-- Botão para abrir a barra lateral --}}
    <nav class="navbar fixed-top bg-dark shadow-sm px-3 d-flex justify-content-between align-items-center">
        <a class="navbar-brand text-white fw-bold" href="{{ route('home') }}">
            🎮 {{ env('APP_NAME', 'Adivinhe e Ganhe') }}
        </a>

        <div class="d-flex align-items-center gap-3">

            @auth
            <div class="dropdown">
                <button id="notificationButton" class="btn btn-light position-relative" data-bs-toggle="dropdown">
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

            {{-- Mobile toggle --}}
            <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start bg-dark text-white sidebar-nav" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold">Adivinhe e Ganhe</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-2">

            <div class="accordion" id="playAccordion">
                <div class="accordion-item bg-dark border-0">
                    <h2 class="accordion-header" id="headingPlay">
                        <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlay">
                            <i class="bi bi-controller me-2"></i> Jogar
                        </button>
                    </h2>
                    <div id="collapsePlay" class="accordion-collapse collapse" data-bs-parent="#playAccordion">
                        <div class="accordion-body d-flex flex-column gap-1 ps-4">
                            <a href="{{ route('home') }}" class="nav-link text-white">Clássico</a>
                            <a href="{{ route('regioes.index') }}" class="nav-link text-white">Clássico por região</a>
                            <a href="{{ route('adivinhe_o_milhao.index') }}" class="nav-link text-white">Adivinhe o Milhão</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion" id="playersAccordion">
                <div class="accordion-item bg-dark border-0">
                    <h2 class="accordion-header" id="headingComunidade">
                        <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComunidade">
                           <i class="bi bi-people"></i> Comunidade
                        </button>
                    </h2>
                    <div id="collapseComunidade" class="accordion-collapse collapse" data-bs-parent="#playersAccordion">
                        <div class="accordion-body d-flex flex-column gap-1 ps-4">
                            @auth
                            <a href="{{ route('para_voce') }}" class="nav-link text-white">Para Você</a>
                            @endauth
                            <a href="{{ route('jogadores') }}" class="nav-link text-white">Jogadores</a>
                            <a href="{{ route('premiacoes') }}" class="nav-link text-white">Prêmios</a>
                            <a href="{{ route('hall_da_fama') }}" class="nav-link text-white">Ranking</a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('sobre') }}" class="nav-link text-white"><i class="bi bi-info-circle"></i> Sobre</a>

            <a href="{{ route('suporte.index') }}" class="nav-link text-white"><i class="bi bi-life-preserver"></i> Suporte</a>

            <hr class="border-secondary">

            @auth
            <a href="{{ route('profile.view', auth()->user()->username) }}" class="nav-link text-white"><i class="bi bi-person-circle"></i> Perfil</a>
            <a href="{{ route('meus_premios') }}" class="nav-link text-white"><i class="bi bi-gift"></i> Meus Prêmios</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Sair</button>
            </form>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Entrar</a>
            @endauth
        </div>
    </div>
    <main class="flex-grow-1 pt-5 mt-3">
        @yield('content')
    </main>

    <footer class="text-center  mt-auto bg-dark animate__animated animate__fadeInUp">
        <h5 class="fw-bold">Adivinhe e Ganhe</h5>
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

        /* No desktop a sidebar fica fixa à esquerda */
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
                /* desloca conteúdo pra direita */
            }

            .navbar {
                display: none;
                /* some o topo em desktop */
            }
        }
    </style>

    {{-- Modais --}}
    @include('partials.modals')

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>

    @include('partials.socket')
    @if(env('ENABLE_CHAT', true))
    @include('partials.chat-floating')
    @endif

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
                                $notifList.append(`
                            <li class="dropdown-item">
                                ${n.data.message}
                                <br><small class="text-muted">${n.created_at}</small>
                            </li>
                        `);
                            });
                        }
                        $notifCount.text(0); // marca visualmente como lida
                    },
                    error: function() {
                        $notifList.html('<li class="text-center text-danger">Erro ao carregar</li>');
                    }
                });
            });
        });
    </script>
    @endauth

    @isset($enable_adsense)
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{  env('GOOGLE_ADSENSE_TAG', 'ca-pub-2128338486173774')}}"
        crossorigin="anonymous"></script>
    @endisset

    @stack('scripts')
</body>


</html>