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

    <title>{{ $title ?? env('APP_NAME', 'Adivinhe e Ganhe') }}</title>

    @stack('head-content')
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </noscript>

    <style>
        html, body {
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(145deg, #0f0c29, #302b63, #24243e);
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

    <nav class="navbar navbar-expand-sm fixed-top bg-dark shadow-lg py-3 px-4">
        <div class="container">
            <a class="navbar-brand text-white fs-4" href="{{ route('home') }}">
                🎮 {{ env('APP_NAME', 'Adivinhe e Ganhe') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon text-white"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-flex align-items-center gap-3">
                    <li class="nav-item"><a class="nav-link" href="{{ route('regioes.index') }}">Regiões</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('suporte.index') }}">Suporte</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('sobre') }}">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('premiacoes') }}">Premiações</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('hall_da_fama') }}">Ranking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('adivinhe_o_milhao.index') }}">Adivinhe o Milhão</a></li>

                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                            <li><a class="dropdown-item" href="{{ route('meus_premios') }}">Meus Prêmios</a></li>
                            @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('adivinhacoes.create') }}">Nova Adivinhação</a></li>
                            <li><a class="dropdown-item" href="{{ route('adivinhe_o_milhao.create_pergunta') }}">Nova Pergunta Adivinhe o Milhão</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Sair</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Entrar</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="text-center py-4 mt-auto bg-dark animate__animated animate__fadeInUp">
        <h5 class="fw-bold">Adivinhe e Ganhe</h5>
        <p class="text-light mb-1">Projeto de código aberto criado e mantido por <span class="text-info">Pedro "Shimpa" Falconi</span></p>
        <a href="https://github.com/PedroShimpa/adivinheganhe" class="text-warning text-decoration-none" target="_blank">
            github.com/PedroShimpa/adivinheganhe
        </a>
    </footer>

    {{-- Modais --}}
    @include('partials.modals')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>

    @include('partials.socket')
    @if(env('ENABLE_CHAT', true))
    @include('partials.chat-floating')
    @endif

    @if(env('GOOGLE_ANALYTICS_TAG'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_TAG') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', "{{ env('GOOGLE_ANALYTICS_TAG') }}");
    </script>
    @endif

    @isset($enable_adsense)
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{  env('GOOGLE_ADSENSE_TAG', 'ca-pub-2128338486173774')}}"
        crossorigin="anonymous"></script>
    @endisset

    @stack('scripts')
</body>

</html>
