<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="google-adsense-account" content="{{ env('GOOGLE_ANALYTICS_TAG')}}" />

    <title>{{ $title ?? env('APP_NAME', 'Adivinhe e Ganhe') }}</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
        rel="stylesheet" />
    <!-- MDB -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.min.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        media="print" onload="this.media='all'">

    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </noscript>

    <style>
        body {
            -webkit-font-smoothing: antialiased;
            font-smooth: always;
            text-rendering: optimizeLegibility;
        }
    </style>

    @stack('head-scripts')
</head>

<body class="font-sans bg-light text-dark">
    <div class="min-h-screen">
        @if(Auth::check())
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center me-3" href="{{ route('home') }}">
                    {{ env('APP_NAME', 'Adivinhe e Ganhe'); }}
                </a>

                <div class="navbar-nav ms-auto d-flex align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('suporte.index') }}">
                                    {{ __('Suporte') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('sobre') }}">
                                    {{ __('Sobre') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('hall_da_fama') }}">
                                    {{ __('Hall da Fama') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('meus_premios') }}">
                                    {{ __('Meus Prêmios') }}
                                </a>
                            </li>
                            @if(auth()->user()->isAdmin())
                            <li>
                                <a class="dropdown-item" href="{{ route('adivinhacoes.create') }}">
                                    {{ __('Nova adivinhação') }}
                                </a>
                            </li>
                            @endif
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </div>
            </div>
        </nav>
        @else

        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center me-3" href="{{ route('home') }}">
                    {{ env('APP_NAME', 'Adivinhe e Ganhe'); }}
                </a>

                <div class="navbar-nav ms-auto d-flex align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                          +
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('suporte.index') }}">
                                    {{ __('Suporte') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('sobre') }}">
                                    {{ __('Sobre') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('hall_da_fama') }}">
                                    {{ __('Hall da Fama') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('login') }}">
                                    {{ __('Entrar') }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>

                        </ul>
                    </li>
                </div>
            </div>
        </nav>
        @endif

        <main>
            @yield('content')
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>

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

    @stack('scripts')

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <h5 class="fw-bold mb-2">Adivinhe e Ganhe</h5>
            <p class="mb-1">
                Um projeto de código aberto criado por
                <span class="fw-bold text-info">Pedro "Shimpa" Falconi</span>
            </p>
            <p>
                Acesse no GitHub:
                <a href="https://github.com/PedroShimpa/adivinheganhe" class="text-decoration-none text-warning" target="_blank" rel="noopener noreferrer">
                    github.com/PedroShimpa/adivinheganhe
                </a>
            </p>
        </div>
    </footer>
</body>

</html>