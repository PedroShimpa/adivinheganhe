@extends('layouts.app', ['enable_adsense' => true])

@section('content')
<div class="container  ">
    <h2 class="mb-4">Meus Prêmios</h2>

    @if($meusPremios->isEmpty())
    <div class="alert alert-info">
        Você ainda não ganhou nenhum prêmio.
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Título</th>
                    <th>Resposta Correta</th>
                    <th>Prêmio</th>
                    <th>Enviado?</th>
                    <th>Previsão de Envio</th>
                    <th>Notificado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meusPremios as $premio)
                <tr>
                    <td>
                        <a href="{{ route('adivinhacoes.index', $premio->uuid) }}" target="_blank">
                            {{ $premio->titulo }}
                        </a>
                    </td>
                    <td>{{ $premio->resposta }}</td>
                    <td>{{ $premio->premio }}</td>
                    <td>
                        @if($premio->premio_enviado)
                        <span class="badge bg-success">Sim</span>
                        @else
                        <span class="badge bg-secondary">Não</span>
                        @endif
                    </td>
                    <td>
                        {{ $premio->previsao_envio_premio ? \Carbon\Carbon::parse($premio->previsao_envio_premio)->format('d/m/Y') : '-' }}
                    </td>
                    <td>
                        @if($premio->vencedor_notificado)
                        <span class="badge bg-primary">Sim</span>
                        @else
                        <span class="badge bg-warning text-dark">Não</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
