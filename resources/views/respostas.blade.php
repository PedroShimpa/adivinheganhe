<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-adsense-account" content="{{ env('GOOGLE_ANALYTICS_TAG')}}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/bootstrap.min.css')}}" rel="stylesheet">
    <script src="{{asset('js/bootstrap.bundle.min.js')}}"></script>
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
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ env('GOOGLE_ADSENSE_TAG')}}"
        crossorigin="anonymous"></script>
</head>

<body>

    <div class="container py-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary">Respostas da Adivinhação: {{ $adivinhacao->titulo }}</h1>
            <p class="text-muted">Confira abaixo quem respondeu e quando</p>
            <hr class="w-25 mx-auto">
        </div>

        <div class="card shadow rounded-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-primary ">
                            <tr>
                                <th class="text-center">Código</th>
                                <th>Usuário</th>
                                <th>Resposta</th>
                                <th class="text-center">Hora</th>
                            </tr>
                        </thead>
                        <tbody id="respostas-container">
                            @include('partials.respostas_table_rows', ['respostas' => $respostas])
                        </tbody>

                    </table>
                </div>
                <div class="mt-3">
                    {{ $respostas->links() }}
                </div>
            </div>

        </div>
    </div>
</body>