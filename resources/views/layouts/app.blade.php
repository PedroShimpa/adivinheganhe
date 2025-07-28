<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="google-adsense-account" content="{{ env('GOOGLE_ANALYTICS_TAG')}}" />

    <title>{{ isset($title) ? $title : config('app.name', 'Laravel') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

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
            @include('layouts.navigation')
        @else
            <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
                <div class="container">
                    <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    <div class="d-flex ms-auto">
                        <a class="btn btn-outline-primary me-2" href="{{ route('login') }}">Entrar</a>
                        <a class="btn btn-primary" href="{{ route('register') }}">Registre-se</a>
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
        function gtag(){ dataLayer.push(arguments); }
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
