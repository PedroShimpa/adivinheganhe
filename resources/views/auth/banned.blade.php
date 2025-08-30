@extends('layouts.app')

@section('content')

<head>
    <meta charset="UTF-8">
    <title>Conta Banida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card shadow-lg border-0 text-center" style="max-width: 500px;">
        <div class="card-header bg-danger text-white">
            <h3 class="mb-0">Conta Banida</h3>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Sua conta foi <strong>banida</strong> por violar as regras da plataforma.
            </p>
            <p class="text-muted">
                Caso acredite que isso foi um engano, entre em contato com o suporte.
            </p>
        </div>
    </div>
</body>
@endsection