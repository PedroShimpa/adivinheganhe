<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-adsense-account" content="{{ env('GOOGLE_ANALYTICS_TAG')}}">

    <title>{{ isset($title) ? $title : config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

 <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"></noscript>

<link rel="preload" as="style" href="{{ asset('css/bootstrap.icons.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/bootstrap.icons.min.css') }}"></noscript>

<link rel="preload" as="style" href="{{ asset('css/bootstrap.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}"></noscript>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}" defer></script>

    @stack('head-scripts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
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
    <script src="{{ asset('js/jquery.min.js')}}"></script>
    <script src="{{ asset('js/sweetalert.min.js')}}"></script>

    @if(env('GOOGLE_ANALYTICS_TAG'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_TAG')}}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', "{{ env('GOOGLE_ANALYTICS_TAG')}}");
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
                <a href="https://github.com/PedroShimpa/adivinheganhe" class="text-decoration-none text-warning" target="_blank">
                    github.com/PedroShimpa/adivinheganhe
                </a>
            </p>
        </div>
    </footer>



</body>

</html>