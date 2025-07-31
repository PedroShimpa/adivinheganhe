@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">🏆 Hall da Fama - Jogadores mais premiados</h2>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>Total de Prêmios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuariosComMaisPremios as $index => $usuario)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $usuario->username }}</td>
                        <td>{{ $usuario->count_premiacoes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
